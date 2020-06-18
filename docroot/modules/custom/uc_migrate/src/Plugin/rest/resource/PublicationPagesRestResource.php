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
 *   id = "publication_pages_rest_resource",
 *   label = @Translation("Publication nodes"),
 *   uri_paths = {
 *     "canonical" = "/migrate/publication_pages"
 *   }
 * )
 */
class PublicationPagesRestResource extends ResourceBase {

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
        $response = $client->get('/site/export/publication', $request);
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
          if (isset($source_row['categories']) && $source_row['categories']) {
            if (isset($source_row['categories'][0]) && $source_row['categories'][0]) {
              $category = $source_row['categories'][0]['category_name'];
            }
          }

          // Figure out group to use.
          $group = NULL;
          if (isset($source_row['publication_ime_lab'][0]) && $source_row['publication_ime_lab'][0]) {
            $group = $source_row['publication_ime_lab'][0];
          }

          // Refine people array.
          $people = [];
          if (isset($source_row['publication_ime_authors']) && $source_row['publication_ime_authors']) {
            foreach ($source_row['publication_ime_authors'] as $delta => $id) {
              $people[] = ['id' => $id];
            }
          }

          // Tack group onto the end of the people array.  Bleh.
          if ($group) {
            $people[] = ['id' => $group];
          }

          // Extract journal information.
          $journal = '';
          $journal_page = '';
          $journal_year = '';
          if (isset($source_row['publication_journal_information']) && $source_row['publication_journal_information']) {
            $journal_vp = [];
            $journal_info = $source_row['publication_journal_information'][0];
            $journal = $journal_info['publication_journal_name'];
            if ($journal_info['publication_journal_volume']) {
              // $journal .= ', Vol. ' . $journal_info['publication_journal_volume'];
              $journal_vp[] = 'Vol. ' . $journal_info['publication_journal_volume'];
            }
            if ($journal_info['publication_journal_pages']) {
              //$journal .= ', Pg. ' . $journal_info['publication_journal_pages'];
              $journal_vp[] = 'Pg. ' . $journal_info['publication_journal_pages'];
            }
            if ($journal_vp) {
              $journal_page = implode(', ', $journal_vp);
            }
            $journal_year = $journal_info['publication_journal_year'];
          }

          // Build up a string to use for citation.
          $citation = '';
          if ($source_row['publication_authors']) {
            $citation .= $source_row['publication_authors'] . '. ';
          }
          if ($source_row['title']) {
            $citation .= $source_row['title'] . '. ';
          }
          if ($journal) {
            $citation .= $journal . '. ';
          }
          if ($journal_year) {
            $citation .= $journal_year . '. ';
          }
          if ($journal_page) {
            $citation .= $journal_page . '. ';
          }

          // Figure out main image to use for the item.
          $main_image = '';
          $field = FALSE;
          if (!empty($source_row['publication_image'])) {
            $field = 'publication_image';
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
            'external_url' => $source_row['publication_pdf_link'],
            'section' => $section,
            'entry_date' => floor($source_row['entry_date'] / 1000),
            'edit_date' => floor($source_row['edit_date'] / 1000),
            'headline' => $source_row['publication_title'],
            'authors' => trim($source_row['publication_authors']),
            'overview' => $source_row['publication_overview'],
            'group' => $group,
            'people' => $people,
            'journal' => $journal,
            'journal_page' => $journal_page,
            'journal_year' => $journal_year,
            'citation' => trim($citation),
            'main_image' => $main_image,
            'category' => $category,
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
