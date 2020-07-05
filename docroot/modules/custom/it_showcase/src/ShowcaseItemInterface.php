<?php

namespace Drupal\it_showcase;

/**
 * Defines an interface for Showcase Items.
 */
interface ShowcaseItemInterface {

  /**
   * Constructs a new ShowcaseController object.
   *
   * @param string $id
   *   A showcase item ID.
   */
  public function __construct($id);

  /**
   * Method to return component information as an array.
   *
   * The output here should be the same as the expected input YAML structure.
   *
   * @return array
   *   Array of showcase item information.
   */
  public function getArray();

  /**
   * Get body class attribute.
   *
   * @return string
   *   A body class attribute.
   */
  public function getBodyClass();

  /**
   * Get array of body class attribute values.
   *
   * @return array
   *   Array of body class attributes.
   */
  public function getBodyClasses();

  /**
   * Get item categories array.
   *
   * @return array
   *   Array of showcase item categories.
   */
  public function getCategories();

  /**
   * Get item category.
   *
   * @return string
   *   A showcase item category.
   */
  public function getCategory();

  /**
   * Get item description.
   *
   * @return string
   *   A showcase item description.
   */
  public function getDescription();

  /**
   * Get file attribute.
   *
   * @return string
   *   A showcase item file attribute.
   */
  public function getFileAttribute();

  /**
   * Get item links.
   *
   * @return array
   *   Array of showcase item links.
   */
  public function getLinks();

  /**
   * Get showcase path for item.
   *
   * @return string
   *   A showcase item path.
   */
  public function getPath();

  /**
   * Get related showcase id.
   *
   * @return string
   *   A showcase item related showcase item ID.
   */
  public function getRelatedShowcaseId();

  /**
   * Get related showcase readme ID's.
   *
   * @return array
   *   A list of related showcase readme items.
   */
  public function getRelatedReadmeShowcaseIds();

  /**
   * Get short title.
   *
   * @return string
   *   A showcase item short title.
   */
  public function getShortTitle();

  /**
   * Get showcase source plugin for item.
   *
   * @return string
   *   A showcase item source plugin ID.
   */
  public function getSourcePlugin();

  /**
   * Get showcase source file for item.
   *
   * @return string
   *   A showcase item source file path.
   */
  public function getSourceFile();

  /**
   * Get item template id.
   *
   * @return string
   *   A showcase item template ID.
   */
  public function getTemplateId();

  /**
   * Get item template suggestion.
   *
   * @return string
   *   A showcase item template suggestion.
   */
  public function getTemplateSuggestion();

  /**
   * Get legacy item template suggestion.
   *
   * @return string
   *   A showcase item legacy template suggestion.
   */
  public function getTemplateSuggestionLegacy();

  /**
   * Get item thumbnail.
   *
   * @return string
   *   A showcase item thumbnail.
   */
  public function getThumbnail();

  /**
   * Get item title.
   *
   * @return string
   *   A showcase item title.
   */
  public function getTitle();

  /**
   * Get item type.  (component, page, endpoint)
   *
   * @return string
   *   A showcase item type.
   */
  public function getType();

  /**
   * Get variant array by delta.
   *
   * @param int $delta
   *   A showcase item variant delta.
   *
   * @return array
   *   Array containing a showcase item variant.
   */
  public function getVariant($delta = 0);

  /**
   * Get variant caption by delta.
   *
   * @param int $delta
   *   A showcase item variant delta.
   *
   * @return string
   *   A showcase item variant caption.
   */
  public function getVariantCaption($delta = 0);

  /**
   * Get variant content array by delta.
   *
   * @param int $delta
   *   A showcase item variant delta.
   *
   * @return array
   *   Array containing a showcase item variant.
   */
  public function getVariantContent($delta = 0);

  /**
   * Get all variants.
   */
  public function getVariants();

  /**
   * Get variant title by delta.
   *
   * @param int $delta
   *   A showcase item variant delta.
   *
   * @return string
   *   A showcase item variant title.
   */
  public function getVariantTitle($delta = 0);

  /**
   * Get item id (namespaced).
   *
   * @return string
   *   A namespaced showcase item ID.
   */
  public function id();

  /**
   * Get base item id.
   *
   * @return string
   *   A showcase item base ID.
   */
  public function idBase();

  /**
   * Is full page item?
   *
   * Optional for pages only.
   *
   * @return bool
   *   Boolean indicating whether or not a showcase item is a full page item.
   */
  public function isFullPage();

  /**
   * Is item hidden on index?
   *
   * Optional for readme only.
   *
   * @return bool
   *   Boolean indicating whether or not a showcase item is hidden in the index.
   */
  public function isHiddenOnIndex();

  /**
   * Is sidebar item?
   *
   * Optional for components only.
   *
   * @return bool
   *   Boolean indicating whether or not a showcase item is a sidebar element.
   */
  public function isSidebar();

  /**
   * Is item enabled?
   *
   * @return bool
   *   Boolean indicating whether or not a showcase item is enabled.
   */
  public function isEnabled();

  /**
   * Set showcase item data by array.
   *
   * The input here should be the same as the expected input YAML structure.
   *
   * @param array $definition
   *   An array with fully formed showcase item array.
   *
   * @return bool
   *   Boolean indicating success/failure.
   */
  public function setArray(array $definition = []);

  /**
   * Set body class.
   *
   * @param string $class
   *   A showcase item body class.
   */
  public function setBodyClass($class);

  /**
   * Set item category.
   *
   * @param string $category
   *   A showcase item category.
   */
  public function setCategory($category);

  /**
   * Set item description.
   *
   * @param string $description
   *   A showcase item description.
   */
  public function setDescription($description);

  /**
   * Set item enabled value.
   *
   * @param bool $value
   *   Boolean indicating whether or not a showcase item is enabled.
   */
  public function setEnabled($value);

  /**
   * Set full page item value.
   *
   * @param bool $value
   *   Boolean indicating whether or not a showcase item is a full page item.
   */
  public function setFullPage($value);

  /**
   * Set hidden on index value.
   *
   * @param bool $value
   *   Boolean indicating whether or not to hide a showcase item in the index.
   */
  public function setHiddenOnIndex($value);

  /**
   * Set single item link.
   *
   * @param array $link
   *   A showcase item link array.
   */
  public function setLink(array $link);

  /**
   * Set item links.
   *
   * @param array $links
   *   Array of showcase item links.
   */
  public function setLinks(array $links);

  /**
   * Set showcase path for item.
   *
   * @param string $path
   *   A showcase item path.
   */
  public function setPath($path);

  /**
   * Set short title attribute.
   *
   * @param bool $value
   *   A showcase item short title.
   */
  public function setShortTitle($value);

  /**
   * Set sidebar item value.
   *
   * @param bool $value
   *   Boolean indicating whether a showcase item is a sidebar element.
   */
  public function setSidebar($value);

  /**
   * Set item source plugin value.
   *
   * @param string $plugin
   *   A showcase item source plugin name.
   */
  public function setSourcePlugin($plugin);

  /**
   * Set item source file value.
   *
   * @param string $file
   *   A showcase item source file path.
   */
  public function setSourceFile($file);

  /**
   * Set item thumbnail.
   *
   * @param string $thumbnail
   *   A showcase item thumbnail.
   */
  public function setThumbnail($thumbnail);

  /**
   * Set item title.
   *
   * @param string $title
   *   A showcase item title.
   */
  public function setTitle($title);

  /**
   * Set item type.
   *
   * @param string $type
   *   A showcase item type.
   *
   * @return bool
   *   Boolean indicating success/failure.
   */
  public function setType($type);

  /**
   * Set all variants via array.
   *
   * @param array $variants
   *   Array of showcase item variants.
   */
  public function setVariants(array $variants = []);

  /**
   * Validate item.
   *
   * @param array $theme_paths
   *   Array of theme paths.
   */
  public function validate(array $theme_paths);

}
