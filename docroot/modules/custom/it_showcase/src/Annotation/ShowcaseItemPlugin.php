<?php

namespace Drupal\it_showcase\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Showcase Item plugin annotation object.
 *
 * @see \Drupal\it_showcase\PluginManager\ItemPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class ShowcaseItemPlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
