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
 *   id = "basic_pages_rest_resource",
 *   label = @Translation("Basic pages"),
 *   uri_paths = {
 *     "canonical" = "/migrate/basic_pages"
 *   }
 * )
 */
class BasicPagesRestResource extends ResourceBase {

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

    // Go ahead and figure out the deal with query string parameters.
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $limit = $request->query->get('limit');
    $limit_sections = FALSE;
    if ($limit) {
      switch ($limit) {
        case 'news':
          $limit_sections = [
            'News',
          ];
          break;
        case 'events':
          $limit_sections = [
            'Events',
          ];
          break;
      }
    }

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
        $response = $client->get('/site/export/basic', $request);
        $source = Json::decode($response->getBody());
      }
      catch (\Exception $e) {
        \Drupal::logger('uc_migrate')
          ->error('An error occurred when requesting source data: ' .
            $e->__toString() . ' (' . $e->getCode() . ')');
      }

      // Get rows to include and sections from appropriate file.
      $filename = '/sites/default/files/legacy/basic-include.csv';
      $csv_data = $helper->getCSVData($filename, 'id');

      // If we got the data we need, then turn it around.
      if ($source && $csv_data) {

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Go no further if it is not an included item.
          if (isset($csv_data[$source_row['entry_id']])) {
            $section = $csv_data[$source_row['entry_id']]['section'];

            // Get legacy URL data for additional insight.
            $url_path = 'not-found';
            $query = db_query("select * from legacy_sitemap where id = '" . $source_row['url_title'] . "'");
            $url_data = $query->fetchAssoc();
            if ($url_data) {
              $url_path = $url_data['section'];
            }

            // Only for certain sections, as neeeded.
            if (!$limit_sections || in_array($section, $limit_sections)) {

              // Figure out main image to use for the item.
              $main_image = '';
              $field = FALSE;
              if (!empty($source_row['basic_main_images'])) {
                $field = 'basic_main_images';
              }
              else {
                if (!empty($source_row['basic_sidebar_image'])) {
                  $field = 'basic_sidebar_image';
                }
              }
              if ($field) {
                $image_metadata = $helper->normalizeImageMetadata('basic', $field, $source_row[$field][0]);
                if ($image_metadata) {
                  $main_image = $field . '-' . $source_row['entry_id'] . '-0';
                }
              }

              // Add to output rows.
              $rows[] = [
                'entry_id' => $source_row['entry_id'],
                'title' => $source_row['title'],
                'url_title' => $source_row['url_title'],
                'url_path' => $url_path,
                'section' => $section,
                'entry_date' => floor($source_row['entry_date'] / 1000),
                'edit_date' => floor($source_row['edit_date'] / 1000),
                'basic_text' => $source_row['basic_text'],
                'basic_related_links' => $source_row['basic_related_links'],
                'basic_main_image' => $main_image,
              ];
            }
          }
        }

        // Return results.
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
