<?php

namespace Drupal\uc_section\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'uc_section_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "uc_section_autocomplete",
 *   label = @Translation("Section Autocomplete"),
 *   description = @Translation("A special autocomplete text field for sections."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class SectionReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Override field type from entity_autocomplete to uc_section_autocomplete.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#type'] = 'uc_section_autocomplete';

    return $element;
  }

}
