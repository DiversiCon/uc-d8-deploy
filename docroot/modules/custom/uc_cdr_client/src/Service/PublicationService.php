<?php

namespace Drupal\uc_cdr_client\Service;


use Drupal\uc_cdr_client\Entity\Publication;

/**
 * Provides an abstraction layer between the CDR API call, and the logic
 * using the data. Provides objects for easier use.
 */
class PublicationService {
  /**
   * @var \Drupal\uc_cdr_client\CdrClient
   */
  protected $cdr_client;

  public function __construct() {
    $this->cdr_client = \Drupal::service('uc_cdr_client.cdr_client');
  }

  /**
   * Gets the first 50 publications for a person entity based on the URL alias.
   *
   * @param $person_url_alias
   *
   * @return Publication[]
   */
  public function getPublicationsByPerson($person_url_alias) {
    $publications = [];

    try {
      // Retrieve the UUID for the person.
      $person_uuid = $this->cdr_client->getUuidFromPathAlias($person_url_alias);

      // Retrieve the publications for the person.
      $publications = $this->getPublications($person_uuid);
    }
    catch (\Exception $e) {
      // Nothing to really log here. Any exceptions thrown from the CDR client
      // will already be logged if necessary.
    }

    return $publications;
  }

  /**
   * A generic method for retrieving publications for any entity with a uuid.
   *
   * @param $uuid
   *
   * @return Publication[]
   */
  protected function getPublications($uuid) {
    // Get up to the first 50 publications.
    $publications_data = $this->cdr_client->getPublications($uuid);

    // Initialize the list of publications.
    $publications = [];

    // Loop through the publications data and convert to a list of publications.
    if ($publications_data != NULL) {
      foreach ($publications_data['data'] as $publication_datum) {
        $data = $publication_datum['attributes'];

        // Convert the data to a Publication object.
        $publication = new Publication();
        $publication->setTitle($data['title']);
        $publication->setAuthorList($data['field_author_list']);
        $publication->setCitation($data['field_citation']);
        $publication->setPmid($data['field_pmid']);
        $publication->setPublicationName($data['field_publication']);
        $publication->setPublicationVenue($data['field_publication_venue']);
        $publication->setDate($data['field_publication_date']);
        $publication->setYear($data['field_year']);
        $publication->setLinkUrl($data['field_link_single']['uri']);
        $publication->setDrupalId($data['drupal_internal__nid']);

        // Add the new publication to the list.
        $publications[] = $publication;
      }
    }

    return $publications;
  }
}
