<?php

namespace Drupal\uc_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\Component\Serialization\Json;
use Drupal\rest\ResourceResponse;
use Drupal\uc_migrate\MigrateHelper;

/**
 * Provides a resource to get lab/group data.
 *
 * @RestResource(
 *   id = "lab_pages_rest_resource",
 *   label = @Translation("Lab pages"),
 *   uri_paths = {
 *     "canonical" = "/migrate/lab_pages"
 *   }
 * )
 */
class LabPagesRestResource extends ResourceBase {

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
    /*
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $limit = $request->query->get('limit');
    $limit_categories = FALSE;
    if ($limit) {
      switch ($limit) {
        case 'faculty':
          $limit_categories = [
            'Faculty',
          ];
          break;
        case 'people':
          $limit_categories = [
            'Administration',
            'Alumni',
            'Institute Fellow',
            'Instructors',
            'Junior Fellow',
            'Researcher',
            'STAGE/IME Fellow',
            'Student',
          ];
          break;
      }
    }
    */

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
        $response = $client->get('/site/export/lab', $request);
        $source = Json::decode($response->getBody());
      }
      catch (\Exception $e) {
        \Drupal::logger('uc_migrate')
          ->error('An error occurred when requesting source data: ' .
            $e->__toString() . ' (' . $e->getCode() . ')');
      }

      // If we got the data we need, then turn it around.
      if ($source) {

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];
          $section = '';

          // Get legacy URL data for additional insight.
          $url_path = 'not-found';
          $query = db_query("select * from legacy_sitemap where id = '" . $source_row['url_title'] . "'");
          $url_data = $query->fetchAssoc();
          if ($url_data) {
            $url_path = $url_data['section'];
          }

          // Figure out main image to use for the item.
          $main_image = '';
          $field = FALSE;
          if (!empty($source_row['lab_main_images'])) {
            $field = 'lab_main_images';
          }
          if ($field) {
            $image_metadata = $helper->normalizeImageMetadata('lab', $field, $source_row[$field][0]);
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
            'short_text' => $source_row['lab_short_description'],
            'long_text' => $source_row['lab_long_description'] . '<br />' . PHP_EOL . $source_row['lab_join'],
            'investigator' => isset($source_row['lab_principal_investigator'][0]) ? $source_row['lab_principal_investigator'][0] : FALSE,
            'lab_main_image' => $main_image,
          ];
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
