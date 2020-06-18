<?php

namespace Drupal\it_showcase\PluginManager;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Showcase Item plugins.
 */
interface ItemPluginInterface extends PluginInspectionInterface {

  /**
   * Returns an array of showcase item definitions.
   *
   * @return array
   *   Array of showcase item definitions.
   */
  public function getDefinitions();

}
