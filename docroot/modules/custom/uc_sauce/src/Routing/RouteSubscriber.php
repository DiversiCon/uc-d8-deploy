<?php

namespace Drupal\uc_sauce\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /* @var \Symfony\Component\Routing\Route $route */
    $route = $collection->get('entity.reusable.canonical');
    if ($route) {
      $route->setDefault('_title_callback', '\Drupal\Core\Entity\Controller\EntityController::title');
    }
  }

}
