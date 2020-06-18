<?php

namespace Drupal\uc_cdr_client\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\Core\TypedData\ComplexDataInterface;

/**
 * Defines a data type for faculty data values.
 *
 * This data type is used only for search api compatibility; however, it
 * could be used for all conveyance of faculty related data.
 *
 * @DataType(
 *   id = "cdr_faculty_data",
 *   label = @Translation("CDR Faculty Data"),
 *   constraints = {},
 *   description = @Translation("Faculty data from the central data repository."),
 *   definition_class = "\Drupal\uc_cdr_client\TypedData\CdrFacultyDataDefinition"
 * )
 */
class CdrFacultyData extends Map implements \IteratorAggregate, ComplexDataInterface {
}
