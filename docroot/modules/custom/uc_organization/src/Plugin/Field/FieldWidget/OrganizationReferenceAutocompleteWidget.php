<?php

namespace Drupal\uc_organization\Plugin\Field\FieldWidget;

/* @codingStandardsIgnoreLine */
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'uc_organization_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "uc_organization_autocomplete",
 *   label = @Translation("Organization Autocomplete"),
 *   description = @Translation("A special autocomplete text field for organizations."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class OrganizationReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Override field type from entity_autocomplete to
    // uc_organization_autocomplete.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#type'] = 'uc_organization_autocomplete';

    return $element;
  }

}
