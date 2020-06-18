<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'uc_cdr_client_faculty_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "uc_cdr_client_faculty_autocomplete",
 *   label = @Translation("Faculty Autocomplete"),
 *   description = @Translation("A special autocomplete text field for faculty selection from CDR."),
 *   field_types = {
 *     "uc_cdr_client_external_reference"
 *   }
 * )
 */
class FacultyReferenceAutocompleteWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Get default input value based on faculty ID and related title as needed.
    $default_value = NULL;
    if (isset($items[$delta]->value)) {
      $default_value = $this->getFacultyInputValue($items[$delta]->value);
    }

    // Define attributes of form element.
    $element['value'] = $element + [
      '#type' => 'uc_cdr_client_external_reference_autocomplete',
      '#default_value' => (!empty($default_value) && $default_value) ? $default_value : NULL,
      '#description' => t('Faculty Reference Autocomplete'),
      '#autocomplete_route_name' => 'uc_cdr_client.faculty_autocomplete',
      '#autocomplete_route_parameters' => [],
    ];

    return $element;
  }

  /**
   *
   */
  private function getFacultyInputValue($faculty_id) {

    // Use CdrClient.
    $client = \Drupal::service('uc_cdr_client.cdr_client');

    // Get Event title.
    $title = $client->getFacultyTitle($faculty_id, 'field_external_id');

    // Use title and faculty ID to construct proper input value.
    if ($title) {
      $input = $title . ' (' . $faculty_id . ')';
    }
    else {
      $input = $faculty_id;
    }

    return $input;
  }

}
