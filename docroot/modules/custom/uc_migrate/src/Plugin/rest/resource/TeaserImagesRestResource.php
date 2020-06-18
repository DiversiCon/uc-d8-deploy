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
 *   id = "teaser_images_rest_resource",
 *   label = @Translation("Teaser images"),
 *   uri_paths = {
 *     "canonical" = "/migrate/teaser_images"
 *   }
 * )
 */
class TeaserImagesRestResource extends ResourceBase {

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

    // Media fields to check and related field mapping.
    $media_fields = [
      'news_image',
    ];

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

      // If we got data then turn it around.
      if ($source) {

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Process all appropriate media fields.
          foreach ($media_fields as $media_field) {

            // Make sure we have data for the current field.
            if (isset($source_row[$media_field]) && !empty($source_row[$media_field])) {

              // Process each instance of each field.
              foreach ($source_row[$media_field] as $delta => $field_values) {

                // Get a normalized set of metadata for this image.
                $image_metadata = $helper->normalizeImageMetadata('news', $media_field, $field_values);

                // So far so good, we have a valid image.
                if ($image_metadata) {

                  // Figure out values to use for migration when conflicted.
                  if ($image_metadata['title']) {
                    $migrate_title = $image_metadata['title'];
                  }
                  else {
                    $migrate_title = $source_row['title'];
                  }

                  // Alt text sans tags.
                  $alt_text = strip_tags($image_metadata['description']);

                  // Add to output rows.
                  $rows[] = [
                    'key' => $media_field . '-' . $source_row['entry_id'] . '-' . $delta,
                    'parent_title' => $source_row['title'],
                    'entry_date' => floor($source_row['entry_date'] / 1000),
                    'edit_date' => floor($source_row['edit_date'] / 1000),
                    'image' => $image_metadata['image'],
                    'image_path' => $image_metadata['path'],
                    'image_mimetype' => $image_metadata['mimetype'],
                    'image_width' => $image_metadata['size'][0],
                    'image_height' =>$image_metadata['size'][1],
                    'description' => $image_metadata['description'],
                    'title' => $image_metadata['title'],
                    'url' => $image_metadata['url'],
                    'migrate_title' => $migrate_title,
                    'alt_text' => $alt_text,
                  ];
                }
              }
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
