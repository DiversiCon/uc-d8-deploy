<?php

namespace Drupal\it_showcase\Plugin\Showcase;

use Drupal\Core\Plugin\PluginBase;
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
   * @var array
   */
  protected $itemDefinitions;

  /**
   * @var \Drupal\it_showcase\ShowcaseItemInterface
   */
  protected $showcaseItem;

  /**
   * YamlDefinitions constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Component\Serialization\Yaml $yaml_serializer
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
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
   * Helper function to find showcase item definitions in YAML files.
   * YAML files may exist in default theme directory or modules/custom
   * directory.  The theme directory will have precedence for duplicates. Thus,
   * you may override a module define showcase item in the theme directory.
   *
   * @return array
   */
  private function findDefinitions() {
    $definitions = [];
    $directories = [
      $this->getModulesPath(),
      $this->getThemePath(),
    ];

    foreach ($directories as $delta => $path) {
      $files = file_scan_directory($path, '/^showcase.yml$/');
      foreach ($files as $uri => $file) {
        $definitions += $this->loadYaml($uri);
      }
    }

    return $definitions;
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
   *
   * @return mixed
   */
  protected function loadYaml($file_uri) {
    $definitions = [];

    // Get data from YAML file.
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

  /**
   * Helper function to get default theme path.
   *
   * @return string
   */
  private function getThemePath() {
    $default_theme = $this->themeHandler->getDefault();
    return $this->themeHandler->getTheme($default_theme)->getPath();
  }

  /**
   * Helper function to get default modules path from settings or based on
   * wherever showcase module is located if setting unavailable.
   *
   * @return string
   */
  private function getModulesPath() {
    $modules_path = trim($this->config->get("paths.modules"), '/') . '/';
    if (!$modules_path) {
      $modules_path = $this->moduleHandler->getModule('it_showcase')->getPath() . '/../';
    }

    return $modules_path;
  }

}
