<?php

namespace Drupal\uc_cdr_client\Plugin\search_api\datasource;

use Drupal\search_api\Datasource\DatasourcePluginBase;
use Drupal\search_api\Datasource\DatasourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\uc_cdr_client\TypedData\CdrEventDataDefinition;
use Drupal\Core\Url;

/**
 * Defines a the CDR event datasource class.
 *
 * @SearchApiDatasource(
 *   id = "cdr_event",
 *   label = @Translation("CDR Event"),
 *   description = @Translation("Exposes CDR event data as a datasource."),
 * )
 */
class CdrEventJson extends DatasourcePluginBase implements DatasourceInterface {

  /**
   * CDR Client.
   *
   * @var \Drupal\uc_cdr_client\CdrClient
   */
  protected $cdrClient;

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

    $datasource->cdrClient = $container->get('uc_cdr_client.cdr_client');
    $datasource->typedDataManager = $container->get('typed_data_manager');
    $datasource->renderer = $container->get('renderer');

    return $datasource;
  }

  /**
   * Retrieves the properties exposed by the underlying event data.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   An associative array of property data types, keyed by the property name.
   */
  public function getPropertyDefinitions() {
    $definition = CdrEventDataDefinition::create('cdr_event_data');

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

    // Get event data.
    $item = NULL;
    $event_data = $this->cdrClient->getEventData($id, TRUE);
    if ($event_data) {

      // Figure out status, we assume NULL is active (which is kinda weird).
      $status = FALSE;
      if (is_null($event_data['data']['attributes']['field_event_status']) || $event_data['data']['attributes']['field_event_status']) {
        $status = TRUE;
      }

      // Grab the necessary data.
      if ($event_data['data']['id'] == $id) {
        $item_data = [
          'event_uuid' => $id,
          'event_title' => $event_data['data']['attributes']['title'],
          'event_location' => $event_data['data']['attributes']['field_location'],
          'event_speaker' => $event_data['data']['attributes']['field_speaker_name'],
          'event_speaker_affiliation' => $event_data['data']['attributes']['field_speaker_affiliation'],
          'event_description' => $event_data['data']['attributes']['field_description'],
          'event_rendered_item' => '',
          'event_alias' => $event_data['data']['attributes']['path']['alias'],
          'event_status' => $status,
        ];

        // And deliver as event data object.
        $item_definition = cdrEventDataDefinition::create('cdr_event_data');
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
    return $item->get('event_uuid')->getString();
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
    return $item->get('event_title')->getString();
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
    return 'cdr_event';
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
    return ['cdr_event' => 'CDR Event'];
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
    $ids = $this->cdrClient->getEventIdsPaged($page);
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
