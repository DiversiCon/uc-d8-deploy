<?php

namespace Drupal\uc_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\Component\Serialization\Json;
use Drupal\rest\ResourceResponse;
use Drupal\uc_migrate\MigrateHelper;

/**
 * Provides a resource to get staff data.
 *
 * @RestResource(
 *   id = "people_types_rest_resource",
 *   label = @Translation("People types"),
 *   uri_paths = {
 *     "canonical" = "/migrate/people_types"
 *   }
 * )
 */
class PeopleTypesRestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Throws exception expected.
   */
  public function get() {
    $rows = [];
    $helper = new MigrateHelper();

    /* @var \GuzzleHttp\Client $client */
    $http = \Drupal::service('http_client_factory');
    if ($http) {

      // Setup our HTTP client.
      $request = [];
      $client = $http->fromOptions([
        'base_uri' => 'https://pme.uchicago.edu',
      ]);

      // Get our source data.
      $source = FALSE;
      try {
        $response = $client->get('/site/export/people', $request);
        $source = Json::decode($response->getBody());
      }
      catch (\Exception $e) {
        \Drupal::logger('uc_migrate')
          ->error('An error occurred when requesting source data: ' .
            $e->__toString() . ' (' . $e->getCode() . ')');
      }

      // If we got the data we need, then turn it around.
      if ($source) {
        $type_data = [];

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Go no further if have no types.
          if (isset($source_row['categories']) && $source_row['categories']) {

            // Process each category.
            foreach ($source_row['categories'] as $index => $type) {
              if (!isset($type_data[$type['category_id']]) && $type['category_id'] != 1) {
                $type_data[$type['category_id']] = $type['category_name'];
              }
            }
          }
        }
      }
    }

    // Return results.
    foreach ($type_data as $key => $type) {
      $rows[] = [
        'key' => $key,
        'name' => $type,
      ];
    }

    // Setup response and add cache-ability metadata.
    $response = new ResourceResponse($rows, 200);
    $list_tags = \Drupal::entityTypeManager()->getDefinition('node')->getListCacheTags();
    $response->getCacheableMetadata()->addCacheTags($list_tags);
    $response->getCacheableMetadata()->addCacheContexts(['url.path', 'url.query_args']);

    return $response;
  }

}
