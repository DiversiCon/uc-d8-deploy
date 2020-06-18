<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'uc_cdr_client_external_reference_widget' widget.
 *
 * @FieldWidget(
 *   id = "uc_cdr_client_external_reference_widget",
 *   label = @Translation("External reference text"),
 *   field_types = {
 *     "uc_cdr_client_external_reference"
 *   }
 * )
 */
class ExternalReferenceDefaultWidget extends WidgetBase {

  /**
   *
   * @inheritdoc
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#description' => t('External Reference ID'),
    ];
    return $element;
  }

}
