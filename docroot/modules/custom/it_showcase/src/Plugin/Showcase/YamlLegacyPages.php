<?php

// @codingStandardsIgnoreFile

namespace Drupal\it_showcase\Plugin\Showcase;

use Drupal;
use Drupal\Core\Plugin\PluginBase;
use Drupal\it_showcase\PluginManager\ItemPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\it_showcase\ShowcaseItem;

/**
 * Provides access to Showcase items that are defined by YAML files that use
 * a previous iteration of format and structure.
 *
 * By design this plugin might dump a ton of items all at once instead
 * providing a single showcase item.
 *
 * @ShowcaseItemPlugin(
 *  id = "it_showcase_yaml_legacy_pagess",
 *  label = @Translation("YAML Legacy Pages"),
 * )
 */
class YamlLegacyPages extends PluginBase implements ItemPluginInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var \Drupal\Component\Serialization\Yaml
   */
  protected $yaml;

  /**
   * Theme Handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var array
   */
  protected $itemDefinitions;

  /**
   * YamlDefinitions constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Component\Serialization\Yaml $yaml_serializer
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CacheBackendInterface $cache_backend,
    Yaml $yaml_serializer,
    ThemeHandlerInterface $theme_handler) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cacheBackend = $cache_backend;
    $this->yaml = $yaml_serializer;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('it_showcase.cache.showcase'),
      $container->get('serialization.yaml'),
      $container->get('theme_handler')
    );
  }

  /**
   * @inheritdoc
   */
  public function getDefinitions() {
    $cid = 'item.definitions.yaml.legacy.pages.all';

    // If we don't already have item definitions.
    if (!isset($this->itemDefinitions) && !$this->itemDefinitions) {

      // Check cache bin for a match and act accordingly.
      $cache = $this->cacheBackend->get($cid);
      if ($cache) {
        $this->itemDefinitions = $cache->data;
      }
      else {
        $this->itemDefinitions = $this->findDefinitions();
        $this->cacheBackend->set($cid, $this->itemDefinitions);
      }
    }
    return $this->itemDefinitions;
  }

  /**
   * Helper function to find showcase item definitions in YAML files.
   *
   * @return array
   */
  private function findDefinitions() {
    /* @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = Drupal::service('file_system');

    $definitions = [];
    $files = [];

    foreach ($this->getThemePaths() as $theme_directory) {
      $files = array_merge($files, $file_system->scanDirectory($theme_directory, '/^pages.yml$/'));
    }

    foreach ($files as $uri => $file) {
      $definitions += $this->loadYaml($uri);
    }

    return $definitions;
  }

  /**
   * Helper function to get showcase definitions from a YAML file.
   *
   * @param string $file_uri
   *
   * @return mixed
   */
  protected function loadYaml($file_uri) {
    $definitions = [];

    // Get data from YAML file.
    $data = $this->yaml->decode(file_get_contents($file_uri));

    // Let's now go through the data, validate and normalize.
    foreach ($data as $id => $row) {

      // Normalize row from old to new format.
      $row = $this->normalize($row);

      // Namespace the id with type and save the original (base id).
      $row['base_id'] = $id;
      $id = $row['base_id'] . '.' . $row['type'];

      // Save this definition locally and, while we are here, in cache bin.
      $item = new ShowcaseItem($id);
      if ($item->setArray($row)) {
        $item->setSourcePlugin($this->pluginId);
        $item->setSourceFile($file_uri);
        $definitions[$id] = $item;
      }
    }

    return $definitions;
  }

  /**
   * Help function to normalize older versions of YAML data. A little messy.
   *
   * @param $row
   *
   * @return array
   */
  private function normalize($row) {
    $normalized = [];

    // Set data/attributes.
    $normalized['title'] = isset($row['title']) ? $row['title'] : '';
    $normalized['description'] = isset($row['description']) ? $row['description'] : '';
    $normalized['type'] = 'page';
    $normalized['enabled'] = isset($row['enabled']) ? $row['enabled'] : '';
    $normalized['attributes'] = [
      'full_page' => isset($row['full_page']) ? $row['full_page'] : FALSE,
      'body_class' => isset($row['body_class']) ? $row['body_class'] : FALSE,
    ];

    // Variants are stored under the content key and there is only one for pages.
    $variants = [];
    if (isset($row['content']) && $row['content']) {
      $variants[] = [
        'content' => $row['content'],
      ];
    }
    $normalized['variants'] = $variants;

    return $normalized;
  }

  /**
   * Helper function to get default theme paths.
   *
   * @return array
   */
  private function getThemePaths() {
    $theme_paths = [];

    // Get the default theme.
    $default_theme = $this->themeHandler->getTheme($this->themeHandler->getDefault());

    // Add the default theme path to the list.
    $theme_paths[] = $default_theme->getPath();

    // If there is a base theme for our default theme, get the base theme path as well.
//    if ($default_theme->base_theme) {
//      $theme_paths[] = $this->themeHandler->getTheme($default_theme->base_theme)->getPath();
//    }

    return $theme_paths;
  }

}
