<?php

namespace Drupal\uc_sauce\Element;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;

/**
 * Custom Link auto-complete element.
 *
 * @FormElement("custom_link_autocomplete")
 *
 * @see \Drupal\uc_sauce\Controller\CustomLinkAutocompleteController
 * @see \Drupal\uc_sauce\Plugin\Field\FieldWidget\CustomLinkWidget
 */
class CustomLinkAutocomplete extends EntityAutocomplete {

  /**
   * {@inheritdoc}
   */
  public static function processEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    // Build the original element.
    $element = parent::processEntityAutocomplete($element, $form_state, $complete_form);

    // Override the autocomplete route.
    $element['#autocomplete_route_name'] = 'uc_sauce.custom_link_autocomplete';

    return $element;
  }

}
