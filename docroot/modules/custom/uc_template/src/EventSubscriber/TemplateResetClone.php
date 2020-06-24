<?php

namespace Drupal\uc_template\EventSubscriber;

use Drupal\entity_clone\Event\EntityCloneEvent;
use Drupal\entity_clone\Event\EntityCloneEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Event subscriber class.
 */
class TemplateResetClone implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[EntityCloneEvents::PRE_CLONE][] = ['onEntityCloned'];
    return $events;
  }

  /**
   * Clones entity reference revisions fields.
   *
   * @param \Drupal\entity_clone\Event\EntityCloneEvent $event
   *   Event object.
   */
  public function onEntityCloned(EntityCloneEvent $event) {
    $entity = $event->getClonedEntity();

    // Make sure we have a content entity.
    if ($entity instanceof ContentEntityInterface) {

      // Make sure we have a field called field_bool_template.
      if ($entity->hasField('field_bool_template')) {

        // Make sure template value is TRUE.
        $check = $entity->get('field_bool_template')->getValue();
        if ($check && isset($check[0]['value']) && $check[0]['value']) {

          // Reset the template value for the cloned entity.
          $entity->set('field_bool_template', FALSE);
        }
      }
    }
  }

}
