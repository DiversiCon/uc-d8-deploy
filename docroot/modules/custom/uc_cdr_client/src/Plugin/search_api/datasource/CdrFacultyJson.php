<?php

namespace Drupal\uc_cdr_client\Plugin\search_api\datasource;

use Drupal\search_api\Datasource\DatasourcePluginBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\uc_cdr_client\TypedData\CdrFacultyDataDefinition;
use Drupal\Core\Url;

/**
 * Defines a the CDR faculty datasource class.
 *
 * @SearchApiDatasource(
 *   id = "cdr_faculty",
 *   label = @Translation("CDR Faculty"),
 *   description = @Translation("Exposes CDR faculty data as a datasource."),
 * )
 */
class CdrFacultyJson extends DatasourcePluginBase implements DatasourceInterface {

  /**
   * CDR Client.
   *
   * @var \Drupal\uc_cdr_client\Service\CdrFacultyService
   */
  protected $cdrFaculty;

  /**
   * Typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $datasource */
    $datasource = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $datasource->cdrFaculty = $container->get('uc_cdr_client.cdr_faculty_service');
    $datasource->typedDataManager = $container->get('typed_data_manager');
    $datasource->renderer = $container->get('renderer');

    return $datasource;
  }

  /**
   * Retrieves the properties exposed by the underlying faculty data.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   An associative array of property data types, keyed by the property name.
   */
  public function getPropertyDefinitions() {
    $definition = CdrFacultyDataDefinition::create('cdr_faculty_data');

    return $definition->getPropertyDefinitions();
  }

  /**
   * Loads an item.
   *
   * @param mixed $id
   *   The datasource-specific ID of the item.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface|null
   *   The loaded item if it could be found, NULL otherwise.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function load($id) {
    $faculty_data = [];

    // Get faculty render array, which includes the raw data.
    $item = NULL;
    $faculty_render = $this->cdrFaculty->renderFacultyMember($id);
    if ($faculty_render) {
      $faculty_data = $faculty_render['#faculty_data'];
    }
    if ($faculty_data) {

      // Build academic titles text.
      $academic_titles = '';
      $departments = '';
      if ($faculty_data['academic_titles']) {
        foreach ($faculty_data['academic_titles'] as $delta => $title) {
          $academic_titles .= $title['title'];
          if ($title['department']) {
            $academic_titles .= ' of ' . $title['department'];
            $departments .= $title['department'] . ' <br />';
          }
          $academic_titles .= ' <br />';
        }
      }

      // Build clinical interests text.
      $clinical_interests = '';
      if ($faculty_data['clinical_interests']) {
        foreach ($faculty_data['clinical_interests'] as $delta => $value) {
          $clinical_interests .= $value . ' <br />';
        }
      }

      // Build scholarly interests text.
      $scholarly_interests = '';
      if ($faculty_data['scholar_interests']) {
        foreach ($faculty_data['scholar_interests'] as $delta => $value) {
          $scholarly_interests .= $value . ' <br />';
        }
      }

      // Grab the necessary data.
      if ($faculty_data['faculty_id'] == $id) {
        $item_data = [
          'faculty_uuid' => $id,
          'faculty_name' => $faculty_data['faculty_title'],
          'faculty_overview' => $faculty_data['description'],
          'faculty_title' => $academic_titles,
          'faculty_rendered_item' => $this->renderer->renderPlain($faculty_render),
          'faculty_departments' => $departments,
          'faculty_section' => $faculty_data['section'],
          'faculty_clinical_interests' => $clinical_interests,
          'faculty_scholarly_interests' => $scholarly_interests,
          'faculty_alias' => $faculty_data['pathAlias'],
          'faculty_status' => $faculty_data['status'],
        ];

        // And deliver as faculty data object.
        $item_definition = CdrFacultyDataDefinition::create('cdr_faculty_data');
        $item = $this->typedDataManager->create($item_definition);
        $item->setValue($item_data);
      }
    }

    return $item;
  }

  /**
   * Loads multiple items.
   *
   * @param array $ids
   *   An array of datasource-specific item IDs.
   *
   * @return array|\Drupal\Core\TypedData\ComplexDataInterface[]
   *   An associative array of loaded items, keyed by their
   *   (datasource-specific) IDs.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function loadMultiple(array $ids) {

    $items = [];
    foreach ($ids as $delta => $id) {
      $item = $this->load($id);
      if ($item) {
        $items[$id] = $item;
      }
    }

    return $items;

  }

  /**
   * Retrieves the unique ID of an object from this datasource.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An object from this datasource.
   *
   * @return string|null
   *   The datasource-internal, unique ID of the item. Or NULL if the given item
   *   is no valid item of this datasource.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getItemId(ComplexDataInterface $item) {
    // Return $item['faculty_uuid'];.
    return $item->get('faculty_uuid')->getString();
  }

  /**
   * Retrieves a human-readable label for an item.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this controller's type.
   *
   * @return string|null
   *   Either a human-readable label for the item, or NULL if none is available.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getItemLabel(ComplexDataInterface $item) {
    return $item->get('faculty_name')->getString();
  }

  /**
   * Retrieves the item's bundle.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this datasource's type.
   *
   * @return string
   *   The bundle identifier of the item. Might be just the datasource
   *   identifier or a similar pseudo-bundle if the datasource does not contain
   *   any bundles.
   *
   * @see getBundles()
   */
  public function getItemBundle(ComplexDataInterface $item) {
    return 'cdr_faculty';
  }

  /**
   * Retrieves the item's language.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this datasource's type.
   *
   * @return string
   *   The language code of this item.
   */
  public function getItemLanguage(ComplexDataInterface $item) {
    return 'en';
  }

  /**
   * Retrieves a URL at which the item can be viewed on the web.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this datasource's type.
   *
   * @return \Drupal\Core\Url|null
   *   Either an object representing the URL of the given item, or NULL if the
   *   item has no URL of its own.
   */
  public function getItemUrl(ComplexDataInterface $item) {
    return Url::fromUri('internal:/search');
  }

  /**
   * Checks whether a user has permission to view the given item.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   An item of this datasource's type.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user.
   *
   * @return bool
   *   TRUE if access is granted, FALSE otherwise.
   */
  public function checkItemAccess(ComplexDataInterface $item, AccountInterface $account = NULL) {
    return TRUE;
  }

  /**
   * Returns the available view modes for this datasource.
   *
   * @param string|null $bundle
   *   (optional) The bundle for which to return the available view modes. Or
   *   NULL to return all view modes for this datasource, across all bundles.
   *
   * @return string[]
   *   An associative array of view mode labels, keyed by the view mode ID. Can
   *   be empty if it isn't possible to view items of this datasource.
   */
  public function getViewModes($bundle = NULL) {
    return NULL;
  }

  /**
   * Retrieves the bundles associated to this datasource.
   *
   * @return string[]
   *   An associative array mapping the datasource's bundles' IDs to their
   *   labels. If the datasource doesn't contain any bundles, a single
   *   pseudo-bundle should be returned, usually equal to the datasource
   *   identifier (and label).
   */
  public function getBundles() {
    return ['cdr_faculty' => 'CDR Faculty'];
  }

  /**
   * Returns the render array for the provided item and view mode.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   The item to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the item.
   * @param string|null $langcode
   *   (optional) For which language the item should be rendered. Defaults to
   *   the language the item has been loaded in.
   *
   * @return array
   *   A render array for displaying the item.
   */
  public function viewItem(ComplexDataInterface $item, $view_mode, $langcode = NULL) {
    return [];
  }

  /**
   * Returns the render array for the provided items and view mode.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface[] $items
   *   The items to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the items.
   * @param string|null $langcode
   *   (optional) For which language the items should be rendered. Defaults to
   *   the language each item has been loaded in.
   *
   * @return array
   *   A render array for displaying the items.
   */
  public function viewMultipleItems(array $items, $view_mode, $langcode = NULL) {
    return [];
  }

  /**
   * Retrieves the entity type ID of items from this datasource, if any.
   *
   * @return string|null
   *   If items from this datasource are all entities of a single entity type,
   *   that type's ID; NULL otherwise.
   */
  public function getEntityTypeId() {
    return NULL;
  }

  /**
   * Returns a list of IDs of items from this datasource.
   *
   * Returns all items IDs by default. However, to avoid issues for large data
   * sets, plugins should also implement a paging mechanism (the details of
   * which are up to the datasource to decide) which guarantees that all item
   * IDs can be retrieved by repeatedly calling this method with increasing
   * values for $page (starting with 0) until NULL is returned.
   *
   * @param int|null $page
   *   The zero-based page of IDs to retrieve, for the paging mechanism
   *   implemented by this datasource; or NULL to retrieve all items at once.
   *
   * @return string[]|null
   *   An array with datasource-specific item IDs (that is, raw item IDs not
   *   prefixed with the datasource ID); or NULL if there are no more items for
   *   this and all following pages.
   */
  public function getItemIds($page = NULL) {
    $ids = $this->cdrFaculty->getFacultyIds($page);
    return $ids;
  }

  /**
   * Retrieves any dependencies of the given fields.
   *
   * @param string[] $fields
   *   An array of property paths on this datasource, keyed by field IDs.
   *
   * @return string[][][]
   *   An associative array containing the dependencies of the given fields. The
   *   array is keyed by field ID and dependency type, the values are arrays
   *   with dependency names.
   */
  public function getFieldDependencies(array $fields) {
    return [];
  }

}
