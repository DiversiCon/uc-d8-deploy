<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link_attributes\Plugin\Field\FieldWidget\LinkWithAttributesWidget;

/**
 * Plugin implementation of the 'link' widget with external references.
 *
 * @FieldWidget(
 *   id = "link_external_reference_with_attributes",
 *   label = @Translation("Link External Reference With Attributes"),
 *   field_types = {
 *     "link"
 *   }
 * )
 *
 * @see \Drupal\uc_cdr_client\Element\LinkExternalReferenceAutocomplete
 * @see \Drupal\uc_cdr_client\Controller\LinkExternalReferenceAutocompleteController
 */
class LinkExternalReferenceWithAttributesWidget extends LinkWithAttributesWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Build the original form element.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    if ($this->supportsInternalLinks()) {
      // Override the form element type.
      $element['uri']['#type'] = 'link_external_reference_autocomplete';
    }

    return $element;
  }

}
