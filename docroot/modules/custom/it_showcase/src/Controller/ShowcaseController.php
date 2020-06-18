<?php

namespace Drupal\it_showcase\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\it_showcase\Showcase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Parsedown;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class ShowcaseController.
 */
class ShowcaseController extends ControllerBase {

  /**
   * Drupal\Core\Routing\RouteMatchInterface definition.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Showcase elements definition.
   *
   * @var \Drupal\it_showcase\Showcase
   */
  protected $showCase;

  /**
   * Constructs a new ShowcaseController object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param \Drupal\it_showcase\Showcase $showcase
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(RouteMatchInterface $route_match, Showcase $showcase, ModuleHandlerInterface $module_handler) {
    $this->routeMatch = $route_match;
    $this->showCase = $showcase;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('it_showcase'),
      $container->get('module_handler')
    );
  }

  /**
   * Generate a showcase index page.
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showIndex() {

    // Get pages/components configuration as content.
    $content = [
      'components' => $this->showCase->getIndexContent('type', 'component'),
      'pages' => $this->showCase->getIndexContent('type', 'page'),
      'endpoints' => $this->showCase->getIndexContent('type', 'endpoint'),
      'readmes' => $this->showCase->getIndexContent('type', 'readme'),
    ];

    // Build render array.
    $build = [
      '#show_messages' => TRUE,
      '#theme' => 'showcase-index',
      '#content' => $content,
      '#showcase' => [
        'template_suggestion' => 'showcase--index.html.twig',
      ],
      '#cache' => [
        'tags' => ['showcase'],
      ],
    ];

    return $build;
  }

  /**
   * Generate a showcase summary page.
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showSummary() {

    // Get all showcase items.
    $content = $this->showCase->getSummaryContent();

    // Build render array.
    $build['summary'] = [
      '#type' => 'table',
      '#header' => $content['header'],
      '#rows' => $content['rows'],
      '#show_messages' => TRUE,
      '#cache' => [
        'tags' => ['showcase'],
      ],
    ];

    return $build;
  }

  /**
   * Generate a showcase item page.
   *
   * This function is just a traffic cop for rendering showcase item pages.
   * Based on the type and id the request will be routed to the appropriate
   * method. We kinda just reverse the type and id then pass everything
   * back-and-forth.
   *
   * @param string $type
   * @param string $id
   *
   * @return array
   *   Return render array for the page.
   */
  public function showItem($type, $id) {
    $map = [
      'component' => 'showComponent',
      'endpoint' => 'showEndpoint',
      'page' => 'showPage',
      'readme' => 'showReadme',
    ];
    $showcase_id = $id . '.' . $type;
    return $this->{$map[$type]}($showcase_id);
  }

  /**
   * Generate a showcase component page.
   *
   * @param string $component_id
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showComponent($component_id) {
    $build = [];

    // Get component and show it.
    $component = $this->showCase->getItem($component_id);
    if ($component) {

      // Change the page title in route to match config.
      if ($component->getTitle()) {
        $this->setPageTitle($component->getTitle());
      }

      // Add Description.
      if ($component->getDescription()) {
        $build['description'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $component->getDescription(),
          '#cache' => [
            'tags' => ['showcase'],
          ],
        ];
      }

      // Render component.
      $build['component'] = $this->showCase->renderComponent($component_id);
    }

    // Undefined component.
    else {
      $build = [
        '#show_messages' => TRUE,
        '#type' => 'markup',
        '#markup' => '<div>Invalid component ID provided: ' . $component_id . '</div>',
        '#cache' => [
          'tags' => ['showcase'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Generate a showcase component page.
   *
   * @param string $page_id
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showPage($page_id) {

    // Get page and show it.
    $page = $this->showCase->getItem($page_id);
    if ($page) {

      // Change the page title in route to match config.
      if ($page->getTitle()) {
        $this->setPageTitle($page->getTitle());
      }

      // Build render array.
      $build = $this->showCase->renderPage($page_id);
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
   * Generate an endpoint showcase item response.
   *
   * @param $endpoint_id
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showEndpoint($endpoint_id) {
    $data = [];

    // Get Endpoint data and return it.
    $endpoint = $this->showCase->getItem($endpoint_id);
    if ($endpoint) {
      $data = $endpoint->getVariantContent();
    }

    // Prepare response.
    $cache = new CacheableMetadata();
    $cache->setCacheTags(['showcase']);
    $response = new CacheableJsonResponse($data);
    $response->addCacheableDependency($cache);
    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

    return $response;
  }

  /**
   * Generate a showcase readme page.
   *
   * @param $readme_id
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showReadMe($readme_id = NULL) {
    $content = '';

    // Get Readme file contents.
    $file_uri = FALSE;
    if ($readme_id) {
      $readme = $this->showCase->getItem($readme_id);
      if ($readme) {
        $file_uri = DRUPAL_ROOT . '/' . $readme->getFileAttribute();
      }
    }
    else {
      $path = $this->moduleHandler->getModule('it_showcase')->getPath();
      if ($path) {
        $file_uri = $path . '/README.md';
      }
    }
    if ($file_uri && file_exists($file_uri)) {
      $content = trim(file_get_contents($file_uri), '"');
    }

    // If we have some content to display.
    if ($content) {
      // Curly braces are a problem.
      $content = str_replace('{{', '', $content);
      $content = str_replace('}}', '', $content);

      // Parse and render.
      $parser = new Parsedown();
      $parser->setSafeMode(TRUE);
      $parser->setMarkupEscaped(TRUE);
      $content = $parser->parse($content);
      $build = [
        'back' => [
          '#title' => $this->t('Back to Showcase'),
          '#type' => 'link',
          '#url' => Url::fromRoute('it_showcase.index'),
          '#cache' => [
            'tags' => ['showcase'],
          ],
        ],
        'body' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => 'showcase__readme',
          ],
          '#value' => $content,
          '#cache' => [
            'tags' => ['showcase'],
          ],
        ],
      ];
    }
    else {
      $build = [
        '#show_messages' => TRUE,
        '#type' => 'markup',
        '#markup' => '<br /><br /><div><strong>Oops!  I could not find anything to display for this page.</strong></div>',
        '#cache' => [
          'tags' => ['showcase'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Generate a showcase status page.
   *
   * ****EXPERIMENTAL****
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showStatus() {
    $build = [];

    // Get status information.
    $items = $this->showCase->getStatus();

    $build['heading'] = [
      '#type' => 'html_tag',
      '#tag' => 'h1',
      '#value' => 'Showcase Status',
    ];

    // Look through all status items and render in groups.
    foreach ($items as $id => $status) {
      $group = 'item-' . $id;

      // Group each showcase item together.
      $build[$group] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
      ];

      $build[$group]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t($status['title'] . ' (' . $id . ')'),
      ];
      // Add template to the group.
      $build[$group]['template'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $status['template']['file'],
      ];

      // Only if we have messages.
      if (isset($status['messages']) && $status['messages']) {

        // Add messages to the group.
        $build[$group]['messages'] = [
          '#type' => 'html_tag',
          '#tag' => 'ol',
        ];

        // Add individual messages.
        foreach ($status['messages'] as $delta => $message) {
          $build[$group]['messages']['message-' . $delta] = [
            '#type' => 'html_tag',
            '#tag' => 'li',
            '#value' => $message,
          ];
        }
      }
    }
    return $build;
  }

  /**
   * API endpoint to return a list of all showcase items.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function apiList() {
    $options = [];
    $items = [];
    $item_list = [];

    // Get showcase item indexes.
    $indexes = $this->showCase->getIndexes();
    foreach ($indexes as $class_id => $class) {

      // Set top level options id.
      $options[$class_id] = [];

      foreach ($class as $subclass_id => $subclass) {

        // Set second level options info.
        $options[$class_id][] = ['name' => ucfirst($subclass_id)];

        foreach ($subclass as $delta => $item_id) {

          // Avoid duplicates.
          if (!isset($item_list[$item_id])) {
            $item_list[$item_id] = $item_id;

            // Set item data.
            $item = $this->showCase->getItem($item_id);
            if ($item) {
              if (!$item->isHiddenOnIndex() && $item->isEnabled()) {
                $items[] = [
                  'id' => $item->idBase(),
                  'type' => ucfirst($item->getType()),
                  'title' => $item->getTitle(),
                  'description' => $item->getDescription(),
                  'thumbnail' => $item->getThumbnail(),
                  'path' => $item->getPath(),
                  'enabled' => $item->isEnabled(),
                  'category' => $item->getCategories(),
                  'links' => $item->getLinks(),
                ];
              }
            }
          }
        }
      }
    }

    // Set final data structure.
    $data = $options;
    $data += ['items' => $items];

    // Prepare response.
    $cache = new CacheableMetadata();
    $cache->setCacheTags(['showcase']);
    $response = new CacheableJsonResponse($data);
    $response->addCacheableDependency($cache);
    $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

    return $response;
  }

  /**
   * Clear showcase related cache and show the index page.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   */
  public function refreshData() {

    // Blast the cache.
    $this->showCase->invalidateCache();

    // Just redirect back to index.
    $response = new TrustedRedirectResponse('/it/showcase', 302);

    return $response;
  }

  /**
   * Helper function to set page title.
   *
   * @param string $title
   */
  private function setPageTitle($title) {
    $route = $this->routeMatch->getCurrentRouteMatch()->getRouteObject();
    $route->setDefault('_title', $title);
  }

}
