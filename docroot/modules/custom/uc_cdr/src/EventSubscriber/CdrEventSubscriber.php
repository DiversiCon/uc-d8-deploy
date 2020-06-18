<?php

namespace Drupal\uc_cdr\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 */
class CdrEventSubscriber implements EventSubscriberInterface {

  /**
   * Code that should be triggered on event specified .
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();

    if (strpos($request->getRequestUri(), '/api/node/event') === FALSE) {
      return;
    }

    if ($request->headers->get('bsdInstanceId')) {
      $instance_id = $request->headers->get('bsdInstanceId');
    }

    if ($request->query->get('_bsdInstanceId')) {
      $instance_id = $request->query->get('_bsdInstanceId');
      $request->query->remove('_bsdInstanceId');
    }

    if (!empty($instance_id)) {
      $config = \Drupal::config('uc_cdr.settings');
      $departments = $config->get("department_mapping.$instance_id");

      if (is_array($departments) && count($departments)) {
        $filter_val['dept_map']['condition'] = [
          'path' => 'field_department.name',
          'operator' => 'IN',
          'value' => [],
        ];

        foreach ($departments as $dept) {
          $filter_val['dept_map']['condition']['value'][] = $dept;
        }
        $existing_filters = $request->query->get('filter');
        if (is_array($existing_filters)) {
          $filters = array_merge($existing_filters, $filter_val);
        }
        else {
          $filters = $filter_val;
        }
        $request->query->set('filter', $filters);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

}
