<?php

namespace Drupal\uc_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * If the source evaluates to a configured value, skip processing or whole row.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_less"
 * )
 *
 * Available configuration keys:
 * - value: A single value against which the source value should be compared.
 * - method: What to do if the input value is empty. Possible values:
 *   - row: Skips the entire row when an empty value is encountered.
 *   - process: Prevents further processing of the input property when the value
 *     is empty.
 *
 * Examples:
 *
 * Example usage with minimal configuration:
 * @code
 *   type:
 *     plugin: skip_on_less
 *     source: created
 *     method: row
 *     value: 1550706423
 * @endcode
 */
class SkipOnLess extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function row($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (empty($this->configuration['value']) && !array_key_exists('value', $this->configuration)) {
      throw new MigrateException('Skip on less plugin is missing value configuration.');
    }

    if ($this->compareValue($value, $this->configuration['value'])) {
      throw new MigrateSkipRowException('Obsolete ' . $value);
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (empty($this->configuration['value']) && !array_key_exists('value', $this->configuration)) {
      throw new MigrateException('Skip on less plugin is missing value configuration.');
    }

    if ($this->compareValue($value, $this->configuration['value'])) {
      throw new MigrateSkipProcessException();
    }

    return $value;
  }

  /**
   * Compare values to see if value is less than skip value.
   *
   * @param mixed $value
   *   Actual value.
   * @param mixed $skipValue
   *   Value to compare against.
   *
   * @return bool
   *   True if the compare successfully, FALSE otherwise.
   */
  protected function compareValue($value, $skipValue) {
    return (integer) $value <= (integer) $skipValue;
  }

}
