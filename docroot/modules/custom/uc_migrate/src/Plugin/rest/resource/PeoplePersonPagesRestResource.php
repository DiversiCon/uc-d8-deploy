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
 *   id = "people_person_rest_resource",
 *   label = @Translation("People person pages"),
 *   uri_paths = {
 *     "canonical" = "/migrate/people_person_pages"
 *   }
 * )
 */
class PeoplePersonPagesRestResource extends ResourceBase {

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
    $limit_categories = FALSE;
    if ($limit) {
      switch ($limit) {
        case 'faculty':
          $limit_categories = [
            'Faculty',
          ];
          break;
        case 'other':
          $limit_categories = [
            'Administration',
            'Alumni',
            'Institute Fellow',
            'Instructors',
            'Junior Fellow',
            'Researcher',
            'STAGE/IME Fellow',
            'Student',
            NULL,
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

        // Process each row.
        foreach ($source as $source_row) {
          $source_row = $source_row['entry'];

          // Get legacy URL data for additional insight.
          $url_path = 'not-found';
          $query = db_query("select * from legacy_sitemap where id = '" . $source_row['url_title'] . "'");
          $url_data = $query->fetchAssoc();
          if ($url_data) {
            $url_path = $url_data['section'];
          }

          // Figure out category to use.
          $category = NULL;
          $category_id = NULL;
          if (isset($source_row['categories']) && $source_row['categories']) {
            if (isset($source_row['categories'][0]) && $source_row['categories'][0]) {
              $category = $source_row['categories'][0]['category_name'];
              $category_id = $source_row['categories'][0]['category_id'];
            }
          }

          // Only for certain categories, as needed.
          if ($category && !$limit_categories || in_array($category, $limit_categories)) {

            // Extract address, email, phone.
            $address = '';
            $email = '';
            $phone = '';
            if (isset($source_row['people_contact']) && $source_row['people_contact']) {
              if (isset($source_row['people_contact'][0]['people_address']) && $source_row['people_contact'][0]['people_address']) {
                $address = $source_row['people_contact'][0]['people_address'];
                $address = strip_tags($address);
                $address = str_replace("\n\n\n", "\n", $address);
                $address = str_replace("\n\n", "\n", $address);
                $address = str_replace("\n", "<br />", $address);
              }
              if (isset($source_row['people_contact'][0]['people_email']) && $source_row['people_contact'][0]['people_email']) {
                $email = $source_row['people_contact'][0]['people_email'];
              }
              if (isset($source_row['people_contact'][0]['people_phone']) && $source_row['people_contact'][0]['people_phone']) {
                $phone = $source_row['people_contact'][0]['people_phone'];
                // Normalize phone number format.
                $phone = str_replace("(", "", $phone);
                $phone = str_replace(") ", ".", $phone);
                $phone = str_replace(")", ".", $phone);
                $phone = str_replace("-", ".", $phone);
              }
            }

            // Figure out main image to use for the item.
            $main_image = '';
            if (isset($source_row['people_profile_picture'][0])) {
              $image_metadata = $helper->normalizeImageMetadata('people', 'people_profile_picture', $source_row['people_profile_picture'][0]);
              if ($image_metadata) {
                $main_image = 'people_profile_picture-' . $source_row['entry_id'] . '-0';
              }
            }

            // Try to convert academic title to positions.
            $positions = [];
            if (isset($source_row['people_academic_title'])) {
              $title = $source_row['people_academic_title'];

              // First check for semicolons (;).
              if (strpos($title, ';')) {
                $positions = explode(';', $title);
              }
              else {
                $positions[] = $title;
              }
            }
            $positions_text = implode(PHP_EOL, $positions);

            // Add to output rows.
            $rows[] = [
              'entry_id' => $source_row['entry_id'],
              'title' => $source_row['title'],
              'url_title' => $source_row['url_title'],
              'url_path' => $url_path,
              'name_first' => $source_row['people_first_name'],
              'name_last' => $source_row['people_last_name'],
              'academic_title' => (strlen($source_row['people_academic_title']) > 255) ? substr($source_row['people_academic_title'],0,255) : $source_row['people_academic_title'],
              'positions' => $positions_text,
              'group' => isset($source_row['people_lab'][0]) && $source_row['people_lab'][0] ? $source_row['people_lab'][0] : NULL,
              'entry_date' => floor($source_row['entry_date'] / 1000),
              'edit_date' => floor($source_row['edit_date'] / 1000),
              'address' => $address,
              'email' => $email,
              'phone' => $phone,
              'bio' => $source_row['people_biography'],
              'research' => $source_row['people_research'],
              'main_image' => $main_image,
              'related_links' => $source_row['people_related_links'],
              'category' => $category,
              'category_id' => $category_id,
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
