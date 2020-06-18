<?php

namespace Drupal\uc_sauce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a home page based on content.
 */
class HomeController extends ControllerBase {

  /**
   * Returns the home page.
   *
   * @return array
   *   A simple renderable array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function showHome() {

    $entity_type = 'node';
    $view_mode = 'default';

    // Get the home page node and generate build array.
    $nid = $this->findHome();
    if ($nid) {
      $node = \Drupal::entityTypeManager()
        ->getStorage($entity_type)
        ->load($nid);
      $build = \Drupal::entityTypeManager()
        ->getViewBuilder($entity_type)
        ->view($node, $view_mode);
    }

    // Tweak caching for this page.
    $build['#cache']['max-age'] = Cache::PERMANENT;
    $build['#cache']['tags'][] = 'home_page';
    $build['#cache']['keys'][] = 'home';

    return $build;
  }

  /**
   * Helper method to find the current home page.
   *
   * @return int
   *   A node id.
   */
  private function findHome() {
    return uc_sauce_find_home_nid();
  }

}
