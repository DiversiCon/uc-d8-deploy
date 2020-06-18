<?php

namespace Drupal\uc_sauce\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link' widget with custom functionality.
 *
 * @FieldWidget(
 *   id = "custom_link",
 *   label = @Translation("Custom Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 *
 * @see \Drupal\uc_sauce\Element\CustomLinkAutocomplete
 * @see \Drupal\uc_sauce\Controller\CustomLinkAutocompleteController
 */
class CustomLinkWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Build the original form element.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    if ($this->supportsInternalLinks()) {
      // Override the form element type.
      $element['uri']['#type'] = 'custom_link_autocomplete';
    }

    return $element;
  }

}
