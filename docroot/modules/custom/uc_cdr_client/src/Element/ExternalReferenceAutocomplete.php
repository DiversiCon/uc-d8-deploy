<?php

namespace Drupal\uc_cdr_client\Element;

use Drupal\Core\Render\Element\Textfield;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an entity autocomplete form element.
 *
 * The #default_value accepted by this element is either an entity object or an
 * array of entity objects.
 *
 * @FormElement("uc_cdr_client_external_reference_autocomplete")
 */
class ExternalReferenceAutocomplete extends Textfield {

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE && $input !== NULL) {
      // This should be a string, but allow other scalars since they might be
      // valid input in programmatic form submissions.
      if (!is_scalar($input)) {
        $input = '';
      }

      // Get reference hash from input value.
      $matches = [];
      $hash_found = preg_match('/\(([^)]*)\)[^(]*$/', $input, $matches);
      if ($hash_found) {
        $hash = $matches[1];
        if ($hash) {
          return $hash;
        }
      }
    }
    return NULL;
  }

}
