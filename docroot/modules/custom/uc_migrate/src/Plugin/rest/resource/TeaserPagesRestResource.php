<?php

namespace Drupal\uc_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\Component\Serialization\Json;
use Drupal\rest\ResourceResponse;
use Drupal\uc_migrate\MigrateHelper;

/**
 * Provides a resource to get teaser data.
 *
 * @RestResource(
 *   id = "teaser_pages_rest_resource",
 *   label = @Translation("Teaser nodes"),
 *   uri_paths = {
 *     "canonical" = "/migrate/teaser_pages"
 *   }
 * )
 */
class TeaserPagesRestResource extends ResourceBase {

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
    $section = 26;
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
        $response = $client->get('/site/export/news', $request);
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

          // Make sure we have a valid external URL.
          $external_url = $source_row['news_url'];
          if
            ($external_url &&
                (
                  strpos($external_url, 'http://') !== FALSE ||
                  strpos($external_url, 'https://') !== FALSE
                ) &&
                strpos($external_url, '//pme.uchicago.edu') === FALSE &&
                strpos($external_url, '//ime.uchicago.edu') === FALSE
            )
          {
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
            if (!empty($source_row['news_image'])) {
              $field = 'news_image';
            }
            if ($field) {
              $image_metadata = $helper->normalizeImageMetadata('news', $field, $source_row[$field][0]);
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
              'external_url' => $external_url,
              'section' => $section,
              'entry_date' => floor($source_row['entry_date'] / 1000),
              'edit_date' => floor($source_row['edit_date'] / 1000),
              'headline' => $source_row['news_headline'],
              'groups' => $source_row['news_lab'],
              'people' => $source_row['news_people'],
              'theme' => $source_row['news_theme'],
              'main_image' => $main_image,
            ];
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
