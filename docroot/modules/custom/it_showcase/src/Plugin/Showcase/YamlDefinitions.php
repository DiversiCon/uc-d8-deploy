<?php

namespace Drupal\it_showcase\Plugin\Showcase;

use Drupal;
use Drupal\Core\Plugin\PluginBase;
// @codingStandardsIgnoreLine
use Drupal\it_showcase\Annotation\ShowcaseItemPlugin;
use Drupal\it_showcase\PluginManager\ItemPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\it_showcase\ShowcaseItem;

/**
 * Provides access to Showcase items that are defined by YAML files.
 *
 * This plugin searches all sub-directories of the default theme directory for
 * files named `showcase.yml`.
 *
 * By design this plugin is going to dump a ton of items all at once instead
 * providing a single showcase item.
 *
 * The proper format for `showcase.yml` files is defined in the showcase README.
 *
 * @see: docroot/modules/custom/it_showcase/README.md
 *
 * @ShowcaseItemPlugin(
 *  id = "it_showcase_yaml_definitions",
 *  label = @Translation("YAML Definitions"),
 * )
 */
class YamlDefinitions extends PluginBase implements ItemPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * YAML serializer.
   *
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
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Showcase configuration.
   *
   * @var array
   */
  private $config;

  /**
   * Item definitions.
   *
   * @var array
   */
  protected $itemDefinitions;

  /**
   * YamlDefinitions constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Cache\CacheBackendInterface|object $cache_backend
   *   Cache backend service.
   * @param \Drupal\Component\Serialization\Yaml|object $yaml_serializer
   *   Serialization service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface|object $theme_handler
   *   Theme handler service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|object $module_handler
   *   Module handler service.
   * @param \Drupal\Core\Config\ConfigFactory|object $config_factory
   *   Configuration factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CacheBackendInterface $cache_backend,
    Yaml $yaml_serializer,
    ThemeHandlerInterface $theme_handler,
    ModuleHandlerInterface $module_handler,
    ConfigFactory $config_factory) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cacheBackend = $cache_backend;
    $this->yaml = $yaml_serializer;
    $this->themeHandler = $theme_handler;
    $this->moduleHandler = $module_handler;

    $this->config = $config_factory->getEditable('it_showcase.settings');
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
      $container->get('theme_handler'),
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * Helper function to find showcase item definitions in YAML files.
   *
   * YAML files may exist in default theme directory or modules/custom
   * directory.  The theme directory will have precedence for duplicates. Thus,
   * you may override a module define showcase item in the theme directory.
   *
   * @return array
   *   Array of showcase item definitions.
   */
  private function findDefinitions() {
    /* @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = Drupal::service('file_system');

    $definitions = [];
    $directories = [
      'docs',
      $this->getModulesPath(),
    ];

    $directories = array_merge($directories, $this->getThemePaths());
    foreach ($directories as $path) {
      if (file_exists($path)) {
        $files = $file_system->scanDirectory($path, '/^showcase.yml$/');
        foreach ($files as $uri => $file) {
          $definitions += $this->loadYaml($uri);
        }
      }
    }

    return $definitions;
  }

  /**
   * Get showcase item definitions.
   *
   * @inheritdoc
   */
  public function getDefinitions() {
    $cid = 'item.definitions.yaml.all';

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
   * Helper function to get default modules path.
   *
   * Either from settings or based on wherever showcase module is located
   * if setting unavailable.
   *
   * @return string
   *   Path to modules directory.
   */
  private function getModulesPath() {
    $modules_path = trim($this->config->get("paths.modules"), '/') . '/';
    if (!$modules_path) {
      $modules_path = $this->moduleHandler->getModule('it_showcase')->getPath() . '/../';
    }

    return $modules_path;
  }

  /**
   * Helper function to get default theme paths.
   *
   * @return array
   *   Array of theme paths.
   */
  private function getThemePaths() {
    $theme_paths = [];

    // Get the default theme.
    $default_theme = $this->themeHandler->getTheme($this->themeHandler->getDefault());

    // Add the default theme path to the list.
    $theme_paths[] = $default_theme->getPath();

    // If a base theme exists for default theme, get base theme path as well.
    // if ($default_theme->base_theme) {
    // $theme_paths[] =
    // $this->themeHandler->getTheme($default_theme->base_theme)->getPath();
    // }
    return $theme_paths;
  }

  /**
   * Helper function to get showcase definitions from a YAML file.
   *
   * This is a terrible burden to put on a plugin.  Perhaps we can have
   * a plugin base class that handles some of this common stuff.  Specifically,
   * namespacing id with type, getThemePath, etc.
   *
   * In order to a prevent duplicates across types we namespace all ids with
   * the items type (i.e. <showcase_id>.<type>).  This will allow for related
   * items of different types to be named alike.  For example, a component,
   * endpoint an readme can all have the same base id.
   *
   * @param string $file_uri
   *   Path to a YAML file with showcase item definitions.
   *
   * @return array
   *   Array of showcase item definitions.
   */
  protected function loadYaml($file_uri) {
    $definitions = [];

    // Get data from YAML file.
    // @codingStandardsIgnoreLine
    $data = $this->yaml->decode(file_get_contents($file_uri));

    // Let's now go through the data, validate and normalize.
    foreach ($data as $id => $row) {

      // Reject anything without a type.
      if (isset($row['type'])) {

        // We have a convoluted situation where we need to make unique the
        // showcase ids within type.  Two showcase items of a different type
        // may be included in a single showcase.yml file with the same name.
        // But, YAML files do not like duplicate id in the same file.  We had
        // to do some funky stuff to get everything to work.
        //
        // End result is id may be specified as <base_id> or <base_id>.<type>.
        // Namespace the id with type and save the base id.
        if (strpos($id, '.')) {
          $chunks = explode('.', $id);
          $row['base_id'] = $chunks[0];
        }
        else {
          $row['base_id'] = $id;
          $id = $row['base_id'] . '.' . $row['type'];
        }

        // Save this definition locally and, while we are here, in cache bin.
        $item = new ShowcaseItem($id);
        if ($item->setArray($row)) {
          $item->setSourcePlugin($this->pluginId);
          $item->setSourceFile($file_uri);
          $definitions[$id] = $item;
        }
      }
    }

    return $definitions;
  }

}
