<?php

namespace Drupal\uc_sauce\Plugin\Linkit\Matcher;

use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;

/**
 * Custom Linkit Matcher class.
 *
 * @Matcher(
 *   id = "entity:media",
 *   target_entity = "media",
 *   label = @Translation("Media"),
 *   provider = "media"
 * )
 */
class MediaMatcher extends EntityMatcher {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + [
      'module' => ['media'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPath($entity) {
    // Provide a default method to supply a URL for any media entity.
    $url = $entity->toUrl()->toString();

    // Now, provide bundle specific url methods.
    switch ($entity->bundle()) {
      case 'document':
        $url = file_url_transform_relative(file_create_url($entity->get('field_media_file')->entity->getFileUri()));

        break;
    }

    return $url;
  }

}
