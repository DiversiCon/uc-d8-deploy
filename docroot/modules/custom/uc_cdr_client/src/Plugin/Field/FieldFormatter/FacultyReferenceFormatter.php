<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field formatter for displaying Faculty references.
 *
 * @FieldFormatter(
 *   id = "faculty_reference_formatter",
 *   label = @Translation("Faculty Reference"),
 *   field_types = {
 *     "uc_cdr_client_external_reference",
 *   },
 * )
 */
class FacultyReferenceFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $faculty_ids = [];

    // Retrieve the faculty ids.
    foreach ($items as $item) {
      $faculty_ids[] = $item->getValue()['value'];
    }

    $faculty_names = [];

    // If there are faculty id values, retrieve data from the CDR and
    // parse it for the names.
    if (count($faculty_ids) > 0) {
      // Request the faculty data.
      /* @var \Drupal\uc_cdr_client\Service\CdrFacultyService $cdr_faculty_service */
      $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');

      $data = $cdr_faculty_service->getFacultyMembers($faculty_ids);

      // Loop through the faculty members returned and parse for names.
      foreach ($data as $faculty_data) {
        $faculty_names[] = $faculty_data['first_name'] . ' ' . $faculty_data['last_name'];
      }
    }

    // Construct the render array.
    $render = [
      '#plain_text' => implode(', ', $faculty_names),
    ];

    return $render;
  }

}
