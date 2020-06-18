<?php

namespace Drupal\uc_cdr_client\TypedData;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines a data type for event data values.
 *
 * This data type is used only for search api compatibility; however, it
 * could be used for all conveyance of event related data.
 *
 * Since it is currently only used by search, some of the field values are
 * tailored to be optimized for search indexing (vs. comparison or rendering).
 */
class CdrEventDataDefinition extends ComplexDataDefinitionBase implements DataDefinitionInterface {
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
      $this->propertyDefinitions['event_uuid'] = DataDefinition::create('string')
        ->setLabel(t('Event ID'))
        ->setRequired(TRUE);

      $this->propertyDefinitions['event_title'] = DataDefinition::create('string')
        ->setLabel(t('Event title'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_location'] = DataDefinition::create('string')
        ->setLabel(t('Event location'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_speaker'] = DataDefinition::create('string')
        ->setLabel(t('Event speaker'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_speaker_affiliation'] = DataDefinition::create('string')
        ->setLabel(t('Event speaker affiliation'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_description'] = DataDefinition::create('string')
        ->setLabel(t('Event description'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_rendered_item'] = DataDefinition::create('string')
        ->setLabel('Rendered item')
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_alias'] = DataDefinition::create('string')
        ->setLabel(t('URL alias'))
        ->setRequired(FALSE);

      $this->propertyDefinitions['event_status'] = DataDefinition::create('boolean')
        ->setLabel(t('Status'))
        ->setRequired(FALSE);
    }
    return $this->propertyDefinitions;
  }

}
