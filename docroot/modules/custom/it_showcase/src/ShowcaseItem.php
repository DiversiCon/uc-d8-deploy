<?php

namespace Drupal\it_showcase;

/**
 * Class ShowcaseItem.
 *
 * @package Drupal\it_showcase
 */
class ShowcaseItem implements ShowcaseItemInterface {

  /**
   * The identifier for this item.
   *
   * @var string
   */
  protected $itemId;

  /**
   * An array containing showcase item values.
   *
   * The structure will match that of the expected YAML input.
   *
   * @var array
   */
  protected $itemDefinition;

  /**
   * An array containing showcase item status info.
   *
   * @var array
   */
  protected $itemStatus;

  /**
   * An array containing showcase item source info.
   *
   * @var array
   */
  protected $itemSource;

  /**
   * {@inheritdoc}
   */
  public function __construct($id) {
    $this->itemId = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getArray() {
    return $this->itemDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyClass() {
    return (isset($this->itemDefinition['attributes']['body_class'])) ? $this->itemDefinition['attributes']['body_class'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getBodyClasses() {
    $classes = [];

    if (isset($this->itemDefinition['attributes']['body_class']) && $this->itemDefinition['attributes']['body_class']) {
      $singles = explode(' ', $this->itemDefinition['attributes']['body_class']);
      foreach ($singles as $class) {
        $classes[$class] = $class;
      }
    }

    return $classes;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories() {
    $categories = [];
    if (isset($this->itemDefinition['attributes']['category'])) {
      $categories = explode(',', $this->itemDefinition['attributes']['category']);
    }
    return $categories;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategory() {
    return (isset($this->itemDefinition['attributes']['category'])) ? $this->itemDefinition['attributes']['category'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return (isset($this->itemDefinition['description'])) ? $this->itemDefinition['description'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFileAttribute() {
    return (isset($this->itemDefinition['attributes']['file'])) ? $this->itemDefinition['attributes']['file'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLinks() {
    return (isset($this->itemDefinition['links'])) ? $this->itemDefinition['links'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return (isset($this->itemDefinition['attributes']['path'])) ? $this->itemDefinition['attributes']['path'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedShowcaseId() {
    return (isset($this->itemDefinition['attributes']['related_id'])) ? $this->itemDefinition['attributes']['related_id'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getShortTitle() {
    return (isset($this->itemDefinition['attributes']['short_title'])) ? $this->itemDefinition['attributes']['short_title'] : $this->getTitle();
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePlugin() {
    return (isset($this->itemSource['plugin'])) ? $this->itemSource['plugin'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFile() {
    return (isset($this->itemSource['file'])) ? $this->itemSource['file'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateId() {

    // Get rid of dashes in item ID.
    return ($this->idBase()) ? str_replace('-', '_', $this->idBase()) : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateSuggestion() {
    return 'showcase--' . $this->getTemplateId() . '.html.twig';
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateSuggestionLegacy() {
    return 'showcase--' . $this->getType() . '--' . $this->getTemplateId() . '.html.twig';
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnail() {
    return (isset($this->itemDefinition['attributes']['thumbnail'])) ? $this->itemDefinition['attributes']['thumbnail'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return (isset($this->itemDefinition['title'])) ? $this->itemDefinition['title'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return (isset($this->itemDefinition['type'])) ? $this->itemDefinition['type'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariant($delta = 0) {
    return (isset($this->itemDefinition['variants'][$delta])) ? $this->itemDefinition['variants'][$delta] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantCaption($delta = 0) {
    return (isset($this->itemDefinition['variants'][$delta]['caption'])) ? $this->itemDefinition['variants'][$delta]['caption'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantContent($delta = 0) {
    return (isset($this->itemDefinition['variants'][$delta]['content'])) ? $this->itemDefinition['variants'][$delta]['content'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getVariants() {
    return (isset($this->itemDefinition['variants'])) ? $this->itemDefinition['variants'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantTitle($delta = 0) {
    return (isset($this->itemDefinition['variants'][$delta]['title'])) ? $this->itemDefinition['variants'][$delta]['title'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->itemId;
  }

  /**
   * {@inheritdoc}
   */
  public function idBase() {
    return isset($this->itemDefinition['base_id']) ? $this->itemDefinition['base_id'] : $this->itemId;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (isset($this->itemDefinition['enabled'])) ? $this->itemDefinition['enabled'] : TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFullPage() {
    return (isset($this->itemDefinition['attributes']['full_page'])) ? $this->itemDefinition['attributes']['full_page'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isHiddenOnIndex() {
    if ($this->getRelatedShowcaseId()) {
      return (isset($this->itemDefinition['attributes']['index_hide'])) ? $this->itemDefinition['attributes']['index_hide'] : FALSE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isSidebar() {
    return (isset($this->itemDefinition['attributes']['sidebar'])) ? $this->itemDefinition['attributes']['sidebar'] : FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * @TODO: I thought I could enforce some rules here but not without feedback.
   *
   * It would be nice to provide some sort of simple validation but we can't
   * just skip items without providing some sort of feedback as to why.  The
   * original code that was yanked:
   *
   *  // Must have type and attributes.
   *  if (!isset($definition['type']) || !isset($definition['attributes'])) {
   *    $status = FALSE;
   *  }
   *  else {
   *    // Must have variants on non-readme showcase items.
   *    if ($definition['type'] !== 'readme' && !isset($defition['variants'])) {
   *      $status = FALSE;
   *    }
   * }
   */
  public function setArray($definition = []) {
    $status = TRUE;
    $this->itemDefinition = [];

    // If we're good then save the definition.
    if ($status) {
      $this->itemDefinition = $definition;
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyClass($class) {
    $this->itemDefinition['attributes']['body'] = $class;
  }

  /**
   * {@inheritdoc}
   */
  public function setCategory($category) {
    $this->itemDefinition['attributes']['category'] = $category;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->itemDefinition['attributes']['description'] = $description;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($value) {
    $this->itemDefinition['enabled'] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFullPage($value) {
    $this->itemDefinition['attributes']['full_page'] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setHiddenOnIndex($value) {
    $this->itemDefinition['attributes']['index_hide'] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLink($link) {
    if (!isset($this->itemDefinition['links'])) {
      $this->itemDefinition['links'] = [];
    }
    $this->itemDefinition['links'][] = $link;
  }

  /**
   * {@inheritdoc}
   */
  public function setLinks($links) {
    $this->itemDefinition['links'] = $links;
  }

  /**
   * {@inheritdoc}
   */
  public function setPath($path) {
    $this->itemDefinition['attributes']['path'] = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function setShortTitle($title) {
    $this->itemDefinition['attributes']['short_title'] = $title;
  }

  /**
   * {@inheritdoc}
   */
  public function setSidebar($value) {
    $this->itemDefinition['attributes']['sidebar'] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourcePlugin($plugin) {
    $this->itemSource['plugin'] = $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceFile($file) {
    $this->itemSource['file'] = $file;
  }

  /**
   * {@inheritdoc}
   */
  public function setThumbnail($thumbnail) {
    $this->itemDefinition['attributes']['thumbnail'] = $thumbnail;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->itemDefinition['title'] = $title;
  }

  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    $status = FALSE;

    // We shouldn't allow invalid types.
    if ($type == 'component' || $type == 'page' || $type == 'endpoint') {
      $this->itemDefinition['type'] = $type;
      $status = TRUE;
    }

    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariants($variants = []) {
    $this->itemDefinition['variants'] = $variants;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($theme_path) {

    // Get a saved version if we have it.
    if (isset($this->itemStatus) && $this->itemStatus) {
      $status = $this->itemStatus;
    }

    // Generate status information.
    else {
      $status = [
        'id' => $this->id(),
        'title' => $this->getTitle(),
      ];

      // Check template file.
      $status['template'] = $this->checkTemplateFile($theme_path);
      if ($status['template']['message'] !== 'Ok') {
        $status['messages'][] = 'template: Could not find template file';
      }

      // Check id.
      if (!$this->id()) {
        $status['messages'][] = 'id: No item id, which is impossible';
      }

      // Check type.
      $types = ['component', 'endpoint', 'page'];
      if (!in_array($this->getType(), $types)) {
        $status['messages'][] = $this->getType() . ': Invalid showcase item type';
      }

      // Check item title.
      if (!$this->getTitle()) {
        $status['messages'][] = 'title: Title value is not set';
      }

      // Check item description.
      if (!$this->getDescription()) {
        $status['messages'][] = 'description: Description value is not set';
      }

      // Save status information.
      $this->itemStatus = $status;
    }

    return $status;
  }

  /**
   * Helper function to check for template file.
   *
   * @param string $theme_path
   *   Theme path.
   *
   * @return array
   *   Template array.
   */
  private function checkTemplateFile($theme_path) {
    $template = [
      'file' => $this->getTemplateSuggestion(),
      'message' => 'Ok',
    ];

    // Check theme directory.
    $files = file_scan_directory($theme_path, '/^' . $this->getTemplateSuggestionLegacy() . '$/');
    if (!$files) {
      $files = file_scan_directory($theme_path, '/^' . $this->getTemplateSuggestion() . '$/');
      if (!$files) {
        $template['message'] = 'Template file not found in default theme directory';
      }
      else {
        reset($files);
        $template['file'] = key($files);
      }
    }
    else {
      reset($files);
      $template['file'] = key($files);
    }

    return $template;
  }

}
