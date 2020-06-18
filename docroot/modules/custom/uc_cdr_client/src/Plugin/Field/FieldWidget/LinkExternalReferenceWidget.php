<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link' widget with external references.
 *
 * @FieldWidget(
 *   id = "link_external_reference",
 *   label = @Translation("Link External Reference"),
 *   field_types = {
 *     "link"
 *   }
 * )
 *
 * @see \Drupal\uc_cdr_client\Element\LinkExternalReferenceAutocomplete
 * @see \Drupal\uc_cdr_client\Controller\LinkExternalReferenceAutocompleteController
 */
class LinkExternalReferenceWidget extends LinkWidget {

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
