<?php

namespace Drupal\Tests\eck\Functional;

use Drupal\Core\Url;

/**
 * Tests if eck entity types are correctly created and updated.
 *
 * @group eck
 */
class EntityTypeCRUDTest extends FunctionalTestBase {

  /**
   * Test if creation of an entity does not result in mismatched definitions.
   */
  public function testEntityCreationDoesNotResultInMismatchedEntityDefinitions() {
    $this->createEntityType([], 'TestType');

    $this->assertNoMismatchedFieldDefinitions();
  }

  /**
   * Test if updating an entity type does not result in mismatched definitions.
   */
  public function testIfEntityUpdateDoesNotResultInMismatchedEntityDefinitions() {
    $this->createEntityType([], 'TestType');

    $routeArguments = ['eck_entity_type' => 'testtype'];
    $route = 'entity.eck_entity_type.edit_form';
    $edit = ['created' => FALSE];
    $submitButton = t('Update @type', ['@type' => 'TestType']);
    $this->drupalPostForm(Url::fromRoute($route, $routeArguments), $edit, $submitButton);

    $this->assertNoMismatchedFieldDefinitions();
  }

  /**
   * Asserts that there are no mismatched definitions.
   */
  private function assertNoMismatchedFieldDefinitions() {
    $this->drupalGet(Url::fromRoute('system.status'));
    $this->assertSession()->responseNotContains('Mismatched entity and/or field definitions');
  }

}
