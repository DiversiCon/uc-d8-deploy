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
 *   id = "people_topics_rest_resource",
 *   label = @Translation("People topics"),
 *   uri_paths = {
 *     "canonical" = "/migrate/people_topics"
 *   }
 * )
 */
class PeopleTopicsRestResource extends ResourceBase {

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
        $topic_values = [];

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Go no further if have no topics.
          if (isset($source_row['people_areas_of_expertise']) && $source_row['people_areas_of_expertise']) {

            $rows[] = [
              'areas' => $source_row['people_areas_of_expertise'],
            ];

            // And process each topic based on new lines.
            $topics1 = explode('\n', $source_row['people_areas_of_expertise']);
            if ($topics1) {
              foreach ($topics1 as $topic1) {

                // Since some use commas, we have to double split.
                $topics = explode(',', $topic1);

                if ($topics) {
                  foreach ($topics as $topic) {
                    $key = md5($topic);
                    if (!isset($topic_values[$key])) {
                      $topic_values[$key] = trim($topic);
                    }
                  }
                }
              }
            }
          }
        }

        // Return results.
        foreach ($topic_values as $key => $topic) {
          $rows[] = [
            'key' => $key,
            'name' => $topic,
          ];
        }
      }
    }

    // Setup response and add cache-ability metadata.
    $response = new ResourceResponse($rows, 200);
    $list_tags = \Drupal::entityTypeManager()->getDefinition('node')->getListCacheTags();
    $response->getCacheableMetadata()->addCacheTags($list_tags);
    $response->getCacheableMetadata()->addCacheContexts(['url.path', 'url.query_args']);

    return $response;
  }

}
