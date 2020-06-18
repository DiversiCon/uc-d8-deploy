<?php

namespace Drupal\uc_sauce\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link_attributes\Plugin\Field\FieldWidget\LinkWithAttributesWidget;

/**
 * Plugin implementation of the 'link' widget with custom functionality.
 *
 * @FieldWidget(
 *   id = "custom_link_with_attributes",
 *   label = @Translation("Custom Link With Attributes"),
 *   field_types = {
 *     "link"
 *   }
 * )
 *
 * @see \Drupal\uc_sauce\Element\CustomLinkAutocomplete
 * @see \Drupal\uc_sauce\Controller\CustomLinkAutocompleteController
 */
class CustomLinkWidgetWithAttributes extends LinkWithAttributesWidget {

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
