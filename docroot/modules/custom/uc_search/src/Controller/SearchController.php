<?php

namespace Drupal\uc_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a search page.
 */
class SearchController extends ControllerBase {

  /**
   * Returns the search page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function showSearch() {

    $build = [
      '#theme' => 'uc_search',
      '#datasource' => '/api/v1/search/list/',
      '#filtered' => 'false',
      '#itemsPerPage' => 10,
      '#paginate' => 'true',
      '#isSearch' => 'true',
      '#cache' => [
        'max-age' => Cache::PERMANENT,
        'tags' => ['search_page'],
        'keys' => ['search'],
      ],
    ];

    return $build;
  }

}
