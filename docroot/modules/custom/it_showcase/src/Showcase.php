<?php

namespace Drupal\it_showcase;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactory;

/**
 *
 */
class Showcase {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * Theme Interface Handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  private $themeHandler;

  /**
   * Component Plugin Manager.
   *
   * @var \Drupal\it_showcase\PluginManager\ItemPluginManager
   */
  private $pluginManager;

  /**
   * Showcase configuration.
   *
   * @var array
   */
  private $config;

  /**
   * Showcase Items.
   *
   * @var array
   */
  private $itemDefinitions;

  /**
   * Item index.
   *
   * Use to index by item type, category and potentially other criteria.
   *
   * @var array
   */
  private $itemIndex;

  /**
   * Constructs a new ShowcaseController object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Plugin manager object.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend object.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, PluginManagerInterface $plugin_manager, ConfigFactory $config_factory, CacheBackendInterface $cache_backend) {
    $this->themeHandler = $theme_handler;
    $this->pluginManager = $plugin_manager;
    $this->cacheBackend = $cache_backend;

    $this->config = $config_factory->getEditable('it_showcase.settings');
  }

  /**
   * Get showcase item indexes to learn how items are classified.
   *
   * @return array
   *   Indexes array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getIndexes() {
    $indexes = [];

    // Make sure index is loaded.
    if (!isset($this->itemIndex) || !$this->itemIndex) {
      $this->loadItems();
    }

    // If the requested index exists just return it.
    if (isset($this->itemIndex)) {
      $indexes = $this->itemIndex;
    }

    return $indexes;
  }

  /**
   * Get item Index by class/sub-class.
   *
   * @param $class
   *   Type, category.
   * @param $subclass
   *   Component, page, endpoint OR Main, Sidebar, Search.
   *
   * @return array
   *   Index value.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getIndex($class, $subclass) {
    $index = [];

    // Make sure index is loaded.
    if (!isset($this->itemIndex) || !$this->itemIndex) {
      $this->loadItems();
    }

    // If the requested index exists just return it.
    if (isset($this->itemIndex[$class][$subclass]) && $this->itemIndex[$class][$subclass]) {
      $index = $this->itemIndex[$class][$subclass];
    }

    return $index;
  }

  /**
   * Get content for item Index by class/sub-class.
   *
   * @param string $class
   *   Type, category.
   * @param string $subclass
   *   Component, page, endpoint OR Main, Sidebar, Search.
   *
   * @return array
   *   Content array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getIndexContent($class, $subclass) {

    // Loop through all items in the index and prepare content.
    $content = [];
    foreach ($this->getIndex($class, $subclass) as $key => $id) {
      if (isset($this->itemDefinitions[$id]) && $this->itemDefinitions[$id]) {
        if (!$this->itemDefinitions[$id]->isHiddenOnIndex() && $this->itemDefinitions[$id]->isEnabled()) {
          $content[$id] = [
            'title' => $this->itemDefinitions[$id]->getTitle(),
            'description' => $this->itemDefinitions[$id]->getDescription(),
            'sidebar' => $this->itemDefinitions[$id]->isSidebar(),
            'path' => $this->itemDefinitions[$id]->getPath(),
          ];
        }
      }
    }

    return $content;
  }

  /**
   * Get summary content.
   *
   * @return array
   *   Summary array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getSummaryContent() {
    $summary = [
      'header' => [
        'ID',
        'Type',
        'Category',
        'Source',
        'Enabled',
      ],
      'rows' => [],
    ];

    // Loop through all items and generate an array for summary table.
    /* @var \Drupal\it_showcase\ShowcaseItem $item */
    foreach ($this->loadItems() as $id => $item) {
      $summary['rows'][] = [
        $item->id(),
        $item->getType(),
        $item->getCategory(),
        $item->getSourceFile(),
        $item->isEnabled() ? '' : 'Disabled',
      ];
    }

    return $summary;
  }

  /**
   * Generate a showcase page render array.
   *
   * @param string $page_id
   *   Page ID.
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function renderPage($page_id) {

    // Get page configuration.
    $page = $this->getItem($page_id);
    if ($page) {

      // Choose type of page based on full_page setting.
      $type = 'container';
      if ($page->isFullPage()) {
        $type = 'page';
      }

      // Only expecting one variant.
      $content = $page->getVariantContent();

      // Render page content.
      $build = [
        '#show_messages' => TRUE,
        '#type' => $type,
        '#theme' => 'showcase',
        '#content' => $content,
        '#cache' => [
          'tags' => ['showcase'],
        ],
        '#showcase' => [
          'id' => $page_id,
          'type' => 'page',
          'template' => $page->getTemplateId(),
          'template_suggestion' => $page->getTemplateSuggestion(),
        ],
      ];

      // Add any associated body classes.
      if ($page->getbodyClass()) {
        $this::it_showcase_body_classes($page->getbodyClass());
      }
    }

    // Undefined page.
    else {
      $build = [
        '#show_messages' => TRUE,
        '#type' => 'markup',
        '#markup' => '<div>Invalid page ID provided: ' . $page_id . '</div>',
        '#cache' => [
          'tags' => ['showcase'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Generate a showcase component render array.
   *
   * @param string $component_id
   *   Component ID.
   * @param mixed $delta
   *   Variant ID.
   *
   * @return array
   *   Return render array for the component.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function renderComponent($component_id, $delta = FALSE) {
    $build = [];
    $show_titles = FALSE;

    // Get component configuration.
    $component = $this->getItem($component_id);
    if ($component) {

      // Define set of components to render, on or many.
      $items = [];
      if ($delta !== FALSE && is_numeric($delta)) {
        if ($component->getVariant($delta)) {
          $items[$delta] = $component->getVariant($delta);
        }
      }
      else {
        $show_titles = TRUE;
        if ($component->getVariants()) {
          $items = $component->getVariants();
        }
      }

      // Render components.
      if ($items) {
        foreach ($items as $delta => $content) {

          // Render title section as needed.
          $content['title'] = (isset($content['title'])) ? $content['title'] : '';
          $content['caption'] = (isset($content['caption'])) ? $content['caption'] : FALSE;
          if ($show_titles && $content['title']) {
            $build[$component_id . '-' . $delta . '-title'] = $this->renderTitleSection($content['title'], $content['caption']);
          }

          // Render this component item.
          $build[$component_id . '-' . $delta] = [
            '#theme' => 'showcase',
            '#content' => $content['content'],
            '#cache' => [
              'tags' => ['showcase'],
            ],
            '#showcase' => [
              'id' => $component_id,
              'type' => $component->getType(),
              'template' => $component->getTemplateId(),
              'template_suggestion' => $component->getTemplateSuggestion(),
            ],
          ];
        }

        // Add any associated body classes.
        if ($component->getBodyClass()) {
          $this::it_showcase_body_classes($component->getBodyClass());
        }
      }

      // No items to render.
      else {
        $build = [
          '#show_messages' => TRUE,
          '#type' => 'markup',
          '#markup' => '<div>Based on the settings, I do not know how to render the component ID provided: ' . $component_id . '</div>',
          '#cache' => [
            'tags' => ['showcase'],
          ],
        ];
      }
    }

    // Undefined component.
    else {
      $build = [
        '#show_messages' => TRUE,
        '#type' => 'markup',
        '#markup' => '<div>Invalid komponent ID provided: ' . $component_id . '</div>',
        '#cache' => [
          'tags' => ['showcase'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Render the title section as needed.
   *
   * @param $title
   *   Title.
   * @param $caption
   *   Caption.
   *
   * @return array
   *   Render array.
   */
  private function renderTitleSection($title = '', $caption = FALSE) {
    return [
      'wrapper' => [
        '#type' => 'html_tag',
        '#tag' => 'section',
        '#attributes' => ['class' => ['showcase__section']],
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $title,
        ],
        'caption' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $caption ? $caption : '',
        ],
      ],
    ];
  }

  /**
   * Method to get a showcase item definition.
   *
   * @var string $showcase_id
   *
   * @return \Drupal\it_showcase\ShowcaseItemInterface
   *   Showcase item object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getItem($showcase_id) {
    $item = FALSE;
    $cid = 'item.definitions.item.' . $showcase_id;

    // If items are not already loaded keep looking.
    if (!isset($this->itemDefinitions) && !$this->itemDefinitions) {

      // We have a chance to get this one item from cache without loading all.
      $cache = $this->cacheBackend->get($cid);
      if ($cache) {
        $item = $cache->data;
      }
      else {
        $this->loadItems();
      }
    }

    // If we don't have it yet, extract the requested item.
    if (!$item) {
      if (isset($this->itemDefinitions[$showcase_id])) {
        $item = $this->itemDefinitions[$showcase_id];
      }
    }

    return $item;
  }

  /**
   * Helper function to load all showcase items.
   *
   * @param bool $include_disabled
   *   Include disabled items?
   *
   * @return array
   *   Showcase item definitions array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function loadItems($include_disabled = FALSE) {
    $cid_all = 'item.definitions.all';
    $path_prefix = '/' . trim($this->config->get("paths.showcase"), '/') . '/';

    // If we already have definitions then we are done.
    if (!$this->itemDefinitions) {

      // Check for cached version of all definitions.
      $cache = $this->cacheBackend->get($cid_all);
      if ($cache) {
        $this->itemDefinitions = $cache->data;
      }

      // Nothing cached so we need to do it the hard way.
      else {

        // Load definitions via installed plugins.
        $this->itemDefinitions = $this->loadItemsPlugins();

        // Sort items by id.
        ksort($this->itemDefinitions);

        // Cache these results for use later.
        $this->cacheBackend->set($cid_all, $this->itemDefinitions);
      }

      // We need to do a little housekeeping and create indexes by item
      // type/category.
      /* @var ShowcaseItem $item */
      foreach ($this->itemDefinitions as $id => $item) {

        // This is a good time to set the path to this item.  We use the Base id
        // for the path to simplify the URL and because the type namespacing
        // for URL's is handle in the path, not in the id per internal storage.
        $item->setPath($path_prefix . $item->getType() . '/' . $item->idBase());

        // Cache this item while we are here.
        $cid = 'item.definitions.item.' . $id;
        $this->cacheBackend->set($cid, $item);

        // Cull disabled items as needed.
        if (!$include_disabled) {
          if (!$item->isEnabled()) {
            unset($this->itemDefinitions[$id]);
          }
        }

        // If item is a readme that references a showcase item, link it.
        if ($item->getType() == 'readme') {
          if ($item->getRelatedShowcaseId()) {
            if (isset($this->itemDefinitions[$item->getRelatedShowcaseId()])) {
              $this->itemDefinitions[$item->getRelatedShowcaseId()]->setLink([
                'text' => $item->getShortTitle(),
                'link' => $item->getPath(),
              ]);
            }
          }
        }

        // Do our indexing.
        $this->itemIndex['type'][$item->getType()][$id] = $id;
        $categories = explode(',', $item->getCategory());
        if ($categories) {
          foreach ($categories as $delta => $category) {
            $this->itemIndex['category'][trim($category)][$id] = $id;
          }
        }
      }
    }

    return $this->itemDefinitions;
  }

  /**
   * Helper function to load showcase item definitions from item plugins.
   *
   * @return array
   *   Showcase item definitions array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function loadItemsPlugins() {
    $definitions = [];
    foreach ($this->pluginManager->getDefinitions() as $plugin_id => $plugin) {
      $instance = $this->pluginManager->createInstance($plugin_id);
      $definitions += $instance->getDefinitions();
    }
    return $definitions;
  }

  /**
   * Method to invalidate all Showcase related cache.
   */
  public function invalidateCache() {
    $this->cacheBackend->deleteAll();
    Cache::invalidateTags(['showcase']);
  }

  /**
   * Get showcase status.
   *
   * @return array
   *   Status array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getStatus() {
    $status = [];

    // Load and validate items.
    $this->loadItems(TRUE);
    foreach ($this->itemDefinitions as $id => $item) {

      // Validate this item.
      $status[$id] = $item->validate($this->getThemePath());

      // Make sure local item definitions are update with status info.
      $this->itemDefinitions[$id] = $item;

      // Update the cache for this item while we are here.
      $cid = 'item.definitions.item.' . $id;
      $this->cacheBackend->set($cid, $item);
    }

    // Update cache for all definitions as well.
    $cid_all = 'item.definitions.all';
    $this->cacheBackend->set($cid_all, $this->itemDefinitions);

    return $status;
  }

  /**
   * Helper function to get default theme path.
   *
   * @return string
   *   Default theme path.
   */
  private function getThemePath() {
    $default_theme = $this->themeHandler->getDefault();
    return $this->themeHandler->getTheme($default_theme)->getPath();
  }

  /**
   * Function to set list of body classes required by showcase elements.
   *
   * This is some tricky business that I trust is proper.  The static nature of
   * this function and related classes is so that components can add their
   * body classes to the page along the way and they can be rendered via
   * hook_preprocess_html in `it_showcase.module` at a later time.
   *
   * @param mixed $class
   *   Class.
   *
   * @return array
   *   Classes array.
   */
  public static function it_showcase_body_classes($class = NULL) {
    $classes = &drupal_static(__FUNCTION__);
    if (!$classes) {
      $classes = [];
    }
    if ($class) {
      $singles = explode(' ', $class);
      foreach ($singles as $class) {
        $classes[$class] = $class;
      }
    }
    return $classes;
  }

}
