<?php

namespace Drupal\uc_cdr_client\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\Core\TypedData\ComplexDataInterface;

/**
 * Defines a data type for event data values.
 *
 * This data type is used only for search api compatibility; however, it
 * could be used for all conveyance of event related data.
 *
 * @DataType(
 *   id = "cdr_event_data",
 *   label = @Translation("CDR Event Data"),
 *   constraints = {},
 *   description = @Translation("Event data from the central data repository."),
 *   definition_class = "\Drupal\uc_cdr_client\TypedData\CdrEventDataDefinition"
 * )
 */
class CdrEventData extends Map implements \IteratorAggregate, ComplexDataInterface {
}
