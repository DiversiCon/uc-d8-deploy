<?php

namespace Drupal\uc_cdr_client\TypedData;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines a data type for faculty data values.
 *
 * This data type is used only for search api compatibility; however, it
 * could be used for all conveyance of faculty related data.
 *
 * Since it is currently only used by search, some of the field values are
 * tailored to be optimized for search indexing (vs. comparison or rendering).
 */
class CdrFacultyDataDefinition extends ComplexDataDefinitionBase implements DataDefinitionInterface {
  use StringTranslationTrait;

  /**
   *
   * @var array
   */

  protected $properties;

  /**
   *
   */
  public function getPropertyDefinitions() {
    if (!isset($this->propertyDefinitions)) {
      $this->propertyDefinitions['faculty_uuid'] = DataDefinition::create('string')
        ->setLabel('Faculty ID')
        ->setRequired(TRUE);

      $this->propertyDefinitions['faculty_name'] = DataDefinition::create('string')
        ->setLabel('Faculty name')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_overview'] = DataDefinition::create('string')
        ->setLabel('Faculty overview')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_title'] = DataDefinition::create('string')
        ->setLabel('Faculty title')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_rendered_item'] = DataDefinition::create('string')
        ->setLabel('Rendered item')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_departments'] = DataDefinition::create('string')
        ->setLabel('Faculty departments')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_section'] = DataDefinition::create('string')
        ->setLabel('Faculty section')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_clinical_interests'] = DataDefinition::create('string')
        ->setLabel('Faculty clinical interests')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_scholarly_interests'] = DataDefinition::create('string')
        ->setLabel('Faculty scholarly interests')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_alias'] = DataDefinition::create('string')
        ->setLabel('URL alias')
        ->setRequired(FALSE);

      $this->propertyDefinitions['faculty_status'] = DataDefinition::create('boolean')
        ->setLabel('Status')
        ->setRequired(FALSE);
    }
    return $this->propertyDefinitions;
  }

}
