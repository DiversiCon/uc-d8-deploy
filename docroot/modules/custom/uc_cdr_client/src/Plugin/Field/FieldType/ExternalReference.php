<?php

namespace Drupal\uc_cdr_client\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'ExternalReference' field type.
 *
 * @FieldType(
 *   id = "uc_cdr_client_external_reference",
 *   label = @Translation("External Reference"),
 *   description = @Translation("Stores an external reference to data in the central data repository."),
 *   category = @Translation("Custom"),
 *   default_widget = "uc_cdr_client_external_reference_widget",
 *   default_formatter = "uc_cdr_client_external_reference_formatter"
 * )
 */
class ExternalReference extends FieldItemBase {

  const EXTERNAL_REFERENCE_MAXLENGTH = 255;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('External Reference'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'char',
          'length' => static::EXTERNAL_REFERENCE_MAXLENGTH,
          'not null' => FALSE,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $constraint_manager->create('ComplexData', [
      'value' => [
        'Length' => [
          'max' => static::EXTERNAL_REFERENCE_MAXLENGTH,
          'maxMessage' => t('%name: the external reference may not be longer than @max characters.', ['%name' => $this->getFieldDefinition()->getLabel(), '@max' => static::EXTERNAL_REFERENCE_MAXLENGTH]),
        ],
      ],
    ]);
    return $constraints;
  }

}
