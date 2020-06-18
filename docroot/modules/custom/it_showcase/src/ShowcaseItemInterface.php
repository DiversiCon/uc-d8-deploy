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
   */
  public function __construct($id);

  /**
   * Method to return component information as an array.
   *
   * The output here should be the same as the expected input YAML structure.
   *
   * @return array
   */
  public function getArray();

  /**
   * Get body class attribute.
   *
   * @return string
   */
  public function getBodyClass();

  /**
   * Get array of body class attribute values.
   *
   * @return array
   */
  public function getBodyClasses();

  /**
   * Get item categories array.
   *
   * @return array
   */
  public function getCategories();

  /**
   * Get item category.
   *
   * @return string
   */
  public function getCategory();

  /**
   * Get item description.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Get file attribute.
   *
   * @return string
   */
  public function getFileAttribute();

  /**
   * Get item links.
   *
   * @return array
   */
  public function getLinks();

  /**
   * Get showcase path for item.
   *
   * @return string
   */
  public function getPath();

  /**
   * Get related showcase id.
   *
   * @return string
   */
  public function getRelatedShowcaseId();

  /**
   * Get short title.
   *
   * @return string
   */
  public function getShortTitle();

  /**
   * Get showcase source plugin for item.
   *
   * @return string
   */
  public function getSourcePlugin();

  /**
   * Get showcase source file for item.
   *
   * @return string
   */
  public function getSourceFile();

  /**
   * Get item template id.
   *
   * @return string
   */
  public function getTemplateId();

  /**
   * Get item template suggestion.
   *
   * @return string
   */
  public function getTemplateSuggestion();

  /**
   * Get legacy item template suggestion.
   *
   * @return string
   */
  public function getTemplateSuggestionLegacy();

  /**
   * Get item thumbnail.
   *
   * @return string
   */
  public function getThumbnail();

  /**
   * Get item title.
   *
   * @return string
   */
  public function getTitle();

  /**
   * Get item type.  (component, page, endpoint)
   *
   * @return string
   */
  public function getType();

  /**
   * Get variant array by delta.
   *
   * @param int $delta
   *
   * @return array
   */
  public function getVariant($delta = 0);

  /**
   * Get variant caption by delta.
   *
   * @param int $delta
   *
   * @return string
   */
  public function getVariantCaption($delta = 0);

  /**
   * Get variant content array by delta.
   *
   * @param int $delta
   *
   * @return array
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
   *
   * @return string
   */
  public function getVariantTitle($delta = 0);

  /**
   * Get item id (namespaced).
   *
   * @return string
   */
  public function id();

  /**
   * Get base item id.
   *
   * @return string
   */
  public function idBase();

  /**
   * Is full page item?
   *
   * Optional for pages only.
   *
   * @return bool
   */
  public function isFullPage();

  /**
   * Is item hidden on index?
   *
   * Optional for readme only.
   *
   * @return bool
   */
  public function isHiddenOnIndex();

  /**
   * Is sidebar item?
   *
   * Optional for components only.
   *
   * @return bool
   */
  public function isSidebar();

  /**
   * Is item enabled?
   *
   * @return bool
   */
  public function isEnabled();

  /**
   * Set showcase item data by array.
   *
   * The input here should be the same as the expected input YAML structure.
   *
   * @param array $definition
   *
   * @return bool
   */
  public function setArray($definition = []);

  /**
   * Set body class.
   *
   * @param string $class
   */
  public function setBodyClass($class);

  /**
   * Set item category.
   *
   * @param string $category
   */
  public function setCategory($category);

  /**
   * Set item description.
   *
   * @param string $description
   */
  public function setDescription($description);

  /**
   * Set item enabled value.
   *
   * @param bool $value
   */
  public function setEnabled($value);

  /**
   * Set full page item value.
   *
   * @param bool $value
   */
  public function setFullPage($value);

  /**
   * Set hidden on index value.
   *
   * @param bool $value
   */
  public function setHiddenOnIndex($value);

  /**
   * Set single item link.
   *
   * @param array $link
   */
  public function setLink($link);

  /**
   * Set item links.
   *
   * @param array $links
   */
  public function setLinks($links);

  /**
   * Set showcase path for item.
   *
   * @param string $path
   */
  public function setPath($path);

  /**
   * Set short title attribute.
   *
   * @param bool $value
   */
  public function setShortTitle($value);

  /**
   * Set sidebar item value.
   *
   * @param bool $value
   */
  public function setSidebar($value);

  /**
   * Set item source plugin value.
   *
   * @param string $plugin
   */
  public function setSourcePlugin($plugin);

  /**
   * Set item source file value.
   *
   * @param string $file
   */
  public function setSourceFile($file);

  /**
   * Set item thumbnail.
   *
   * @param string $thumbnail
   */
  public function setThumbnail($thumbnail);

  /**
   * Set item title.
   *
   * @param string $title
   */
  public function setTitle($title);

  /**
   * Set item type.
   *
   * @param string $type
   *
   * @return bool
   */
  public function setType($type);

  /**
   * Set all variants via array.
   *
   * @param array $variants
   */
  public function setVariants($variants = []);

  /**
   * Validate item.
   *
   * @param string $theme_path
   */
  public function validate($theme_path);

}
