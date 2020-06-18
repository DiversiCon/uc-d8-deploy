<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'Media Contact' migrate process plugin.
 *
 * This is necessary due to the way each user/role combination is treated
 * as a separate $row.  Essentially, we just need to make sure we return
 * a consistent result, regardless of how many times it is called during
 * migrate process.
 *
 * Conditioning specific to this use case only.  We simply look for the
 * passed $value in the values of the roles array.  If found then TRUE.
 *
 * @MigrateProcessPlugin(
 *  id = "media_contact"
 * )
 */
class MediaContact extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $map = [
      '7' => 'profile',
      '8' => 'story',
    ];
    $return = FALSE;
    $source = $row->getSource();
    if (isset($source['roles']) && !empty($source['roles'])) {
      $roles = array_values($source['roles']);
      foreach ($map as $key => $type) {
        if (in_array($key, $roles)) {
          $return[] = $type;
        }
      }
    }
    return $return;
  }

}
