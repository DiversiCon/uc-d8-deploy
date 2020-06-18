<?php

namespace Drupal\uc_cdr_client\Element;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FormElement("link_external_reference_autocomplete")
 *
 * @see \Drupal\uc_cdr_client\Plugin\Field\FieldWidget\LinkExternalReferenceWidget
 * @see \Drupal\uc_cdr_client\Controller\LinkExternalReferenceAutocompleteController
 */
class LinkExternalReferenceAutocomplete extends EntityAutocomplete {

  /**
   * {@inheritdoc}
   */
  public static function processEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    // Build the original element.
    $element = parent::processEntityAutocomplete($element, $form_state, $complete_form);

    // Override the autocomplete route.
    $element['#autocomplete_route_name'] = 'uc_cdr_client.link_external_reference_autocomplete';

    return $element;
  }

}
