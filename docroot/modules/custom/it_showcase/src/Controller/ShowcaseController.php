<?php

namespace Drupal\it_showcase\Controller;

use DOMDocument;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\it_showcase\Showcase;
use Parsedown;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Module handler object definition.
   *
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
   *   Route match information.
   * @param \Drupal\it_showcase\Showcase $showcase
   *   Showcase object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler object.
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
   * API endpoint to return a list of all showcase items.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Proper redirect response back to index page.
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

        foreach ($subclass as $item_id) {

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
   * Get CSS ID from Text value.
   *
   * @param string $text
   *   Text string.
   *
   * @return string
   *   CSS ID value.
   */
  private function getCssIdFromText($text) {
    $id = strtolower($text);
    $id = str_replace('/', '', $id);
    $id = str_replace(' ', '-', $id);

    return $id;
  }

  /**
   * Clear showcase related cache and show the index page.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   Proper redirect response back to index page.
   */
  public function refreshData() {

    // Blast the cache.
    $this->showCase->invalidateCache();

    // Just redirect back to index.
    return new TrustedRedirectResponse('/it/showcase', 302);
  }

  /**
   * Render navigation elements to be used on some showcase pages.
   *
   * @param string $page
   *   Represents page being rendered and which is used to conditional render.
   *
   * @return string
   *   Concatenated text representing navigation HTML.
   */
  private function renderNavElements($page) {
    // @codingStandardsIgnoreStart
    $nav = '<nav>' . PHP_EOL;
    $nav .= '  <ul class="action-links">' . PHP_EOL;

    $index_class = "enabled";
    $summary_class = "enabled";
    $status_class = "enabled";
    switch ($page) {
      case "index":
        $index_class = "disabled";
        break;
      case "summary":
        $summary_class = "disabled";
        break;
      case "status":
        $status_class = "disabled";
        break;
    }
    $nav .= '    <li><a href="/it/showcase" class="button button-action button--primary button-small ' . $index_class .'">Index</a></li>' . PHP_EOL;
    $nav .= '    <li><a href="/it/showcase/summary" class="button button-action button--primary button-small ' . $summary_class .'">Summary</a></li>' . PHP_EOL;
    $nav .= '    <li><a href="/it/showcase/status" class="button button-action button--primary button-small ' . $status_class .'" disabled="true">Status</a></li>' . PHP_EOL;
    $nav .= '    <li><a href="/it/showcase/refresh" class="button button-action button--primary button-small">Rebuild Config</a></li>' . PHP_EOL;

    $nav .= '  </ul>' . PHP_EOL;
    $nav .= '  <a href="/it/showcase/readme">Showcase Readme</a>' . PHP_EOL;
    $nav .= '  <br />' . PHP_EOL;
    $nav .= '</nav>' . PHP_EOL;
    $nav .= '<br /><hr /><br />';
    // @codingStandardsIgnoreEnd

    return $nav;
  }

  /**
   * Render related link elements for readme pages.
   *
   * @param array $related_links
   *   Array of related links to include.
   *
   * @return string
   *   Concatenated text representing related links HTML.
   */
  private function renderRelatedReadmeElements(array $related_links) {

    // @codingStandardsIgnoreStart
    $links = '<br /><br /><hr /><br />';
    $links .= '<h2>Related Links</h2>';
    $links .= '<ul>' . PHP_EOL;
    foreach ($related_links as $link) {
      $links .= '<li><a href="' . $link['url'] . '">' . $link['title'] . '</a> - ' . $link['description'] . '</li>';
    }
    $links .= '</ul>' . PHP_EOL;
    // @codingStandardsIgnoreEnd

    return $links;
  }

  /**
   * Helper function to set page title.
   *
   * @param string $title
   *   Page title string.
   */
  private function setPageTitle($title) {
    /* @var RouteMatchInterface $route_match */
    $route_match = $this->routeMatch->getCurrentRouteMatch();
    /* @var \Symfony\Component\Routing\Route $route */
    $route = $route_match->getRouteObject();
    $route->setDefault('_title', $title);
  }

  /**
   * Generate a showcase component page.
   *
   * @param string $component_id
   *   Showcase ID for the desired component.
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
          // @codingStandardsIgnoreLine
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
        // @codingStandardsIgnoreLine
        '#markup' => '<div>Invalid component ID provided: ' . $component_id . '</div>',
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
   * @param string $endpoint_id
   *   Showcase ID for the desired endpoint.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response object.
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
      'documentation' => $this->showCase->getIndexContent('type', 'readme'),
    ];

    // Generate a categorized index of showcase items.
    $categorized = [];
    foreach ($content as $type => $items) {
      foreach ($items as $id => $item) {
        foreach ($item['categories'] as $category) {
          $categorized[$type][$category][] = $id;
        }
      }
    }
    ksort($categorized);

    // Return render array.
    return [
      '#show_messages' => TRUE,
      '#theme' => 'showcase-index',
      '#content' => $content,
      '#showcase' => [
        'category_index' => $categorized,
        'navigation' => $this->renderNavElements('index'),
        'template_suggestion' => 'showcase--index.html.twig',
      ],
      '#cache' => [
        'tags' => ['showcase'],
      ],
    ];
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
   *   Showcase item type.
   * @param string $id
   *   Showcase ID.
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
   * @param string $page_id
   *   Showcase ID for the desired page.
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
        // @codingStandardsIgnoreLine
        '#markup' => '<div>Invalid page ID provided: ' . $page_id . '</div>',
        '#cache' => [
          'tags' => ['showcase'],
        ],
      ];
    }

    return $build;
  }

  /**
   * Generate a showcase readme page.
   *
   * @param string $readme_id
   *   Showcase ID for the desired readme file.
   *
   * @return array
   *   Return render array for the page.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function showReadme($readme_id = NULL) {
    $content = '';
    $title = 'Showcase Item';

    // Get Readme file contents.
    $file_uri = FALSE;
    $related_links = [];
    if ($readme_id) {
      $readme = $this->showCase->getItem($readme_id);
      if ($readme) {
        $file_uri = DRUPAL_ROOT . '/' . $readme->getFileAttribute();
        $title = $readme->getTitle();

        // Get all related readme links to add to rendered content.
        $related_ids = $readme->getRelatedReadmeShowcaseIds();
        foreach ($related_ids as $related_id) {
          $related = $this->showCase->getItem($related_id);
          if ($related) {
            $related_links[] = [
              'description' => $related->getDescription(),
              'title' => $related->getTitle(),
              'url' => $related->getPath(),
            ];
          }
        }
      }
    }
    else {
      $path = $this->moduleHandler->getModule('it_showcase')->getPath();
      if ($path) {
        $file_uri = $path . '/README.md';
      }
    }

    // @codingStandardsIgnoreStart
    if ($file_uri && file_exists($file_uri)) {
      $content = trim(file_get_contents($file_uri), '"');
    }
    // @codingStandardsIgnoreEnd

    // If we have some content to display.
    if ($content) {
      // Curly braces are a problem.
      $content = str_replace('{{', '', $content);
      $content = str_replace('}}', '', $content);

      // Parse.
      $parser = new Parsedown();
      $parser->setSafeMode(FALSE);
      $parser->setMarkupEscaped(FALSE);
      $parser->setBreaksEnabled(FALSE);
      $content = $parser->parse($content);

      // Now we want to get ready to do a few things to the HTML.
      $html = new DOMDocument();
      $html->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

      // Add an id attribute to some H tags.
      foreach (['h1', 'h2', 'h3', 'h4'] as $tag) {
        $elements = $html->getElementsByTagName($tag);
        /* @var \DOMElement $element */
        foreach ($elements as $element) {
          $css_id = $this->getCssIdFromText($element->nodeValue);
          $element->setAttribute('id', $css_id);
        }
      }

      // Jam related links in at the end of the HTML.
      if ($related_links) {
        $element = $html->createElement('div');
        $element->setAttribute('class', 'showcase_related_links');
        $fragment = $html->createDocumentFragment();
        $fragment->appendXML($this->renderRelatedReadmeElements($related_links));
        $element->appendChild($fragment);
        $html->appendChild($element);

        // Tack a "top" link onto the end of the HTML.
        $link = $html->createElement('a', 'Top &#8593;');
        $link->setAttribute('href', '#');
        $link->setAttribute('id', 'showcase_to_top');
        $html->appendChild($link);
      }

      // Save the adjusted HTML.
      $content = $html->saveHTML();

      // Build render arrays.
      $build = [
        '#title' => $title,
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
          // @codingStandardsIgnoreLine
          '#value' => $content,
          '#cache' => [
            'tags' => ['showcase'],
          ],
          '#attached' => [
            'library' => [
              'it_showcase/it_showcase.markdown',
            ],
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

    /*
    $build['heading'] = [
    '#type' => 'html_tag',
    '#tag' => 'h1',
    // @codingStandardsIgnoreLine
    '#value' => 'Showcase Status',
    ];
     */

    // Build render array for navigation elements.
    $build['navigation'] = [
      '#markup' => $this->renderNavElements('status'),
    ];

    // Look through all status items and render in groups.
    foreach ($items as $id => $status) {
      $group = 'item-' . $id;

      // Only if we have messages.
      if (isset($status['messages']) && $status['messages']) {

        // Group each showcase item together.
        $build[$group] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
        ];

        $build[$group]['title'] = [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          // @codingStandardsIgnoreLine
          '#value' => $status['title'] . ' (' . $id . ')',
        ];

        // Add template to the group.
        if (isset($status['template']['file'])) {
          $build[$group]['template'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            // @codingStandardsIgnoreLine
            '#value' => $status['template']['file'],
          ];
        }

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
            // @codingStandardsIgnoreLine
            '#value' => $message,
          ];
        }
      }
    }
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

    // Build render array for navigation elements.
    $build['navigation'] = [
      '#markup' => $this->renderNavElements('summary'),
    ];

    // Build render array for summary data.
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

}
