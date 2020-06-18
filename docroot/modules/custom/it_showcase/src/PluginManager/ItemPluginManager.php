<?php

namespace Drupal\it_showcase\PluginManager;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Showcase Item plugin plugin manager.
 */
class ItemPluginManager extends DefaultPluginManager implements PluginManagerInterface {

  /**
   * Constructor for ItemPluginManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Showcase',
      $namespaces,
      $module_handler,
      'Drupal\it_showcase\PluginManager\ItemPluginInterface',
      'Drupal\it_showcase\Annotation\ShowcaseItemPlugin');

    $this->alterInfo('it_showcase_item_info');
    $this->setCacheBackend($cache_backend, 'it_showcase_item_plugins');
  }

}
