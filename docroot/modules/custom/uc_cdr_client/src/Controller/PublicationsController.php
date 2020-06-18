<?php

namespace Drupal\uc_cdr_client\Controller;


use Drupal\config_pages\Entity\ConfigPages;
use Drupal\Core\Controller\ControllerBase;
use Drupal\uc_cdr_client\CdrClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublicationsController extends ControllerBase {

  /**
   * @var \Drupal\uc_cdr_client\CdrClient
   */
  private $cdr_client;

  public function __construct(CdrClient $cdr_client) {
    $this->cdr_client = $cdr_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uc_cdr_client.cdr_client')
    );
  }

  public function getPersonPublications($section, $person_name) {
    // Retrieve the node id via a reverse lookup with the path.
    $node_path = \Drupal::service('path.alias_manager')->getPathByAlias("/$section/people/$person_name");
    preg_match('/node\/(\d+)/', $node_path, $matches);
    $node_id = $matches[1];

    $render_array = $this->getPublicationsByEntityType('person', $node_id);

    return $render_array;
  }

  public function getGroupPublications($group_name) {
    $render_array = $this->getPublicationsByEntityType('group');

    return $render_array;
  }

  private function getPublicationsByEntityType($entity_type, $node_id = NULL) {
    /* @var \Symfony\Component\HttpFoundation\Request $request */
    $request = \Drupal::request();
    $request_path = '';

    // If a node id is present, determine the request path with it.
    if ($node_id != NULL) {
      $request_path = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node_id);
    }
    else {
      $request_path = str_replace('/publications', '', $request->getRequestUri());
    }

    $uuid = $this->cdr_client->getUuidFromPathAlias($request_path);

    $publications_config = ConfigPages::load('publications');

    $hide_topics = 'false';
    if ($publications_config != NULL) {
      if ($entity_type == 'group') {
        $hide_topics = $publications_config->get('field_cp_boolean')->getValue()[0]['value'];
      }
      else {
        $hide_topics = $publications_config->get('field_hide')->getValue()[0]['value'];
      }
    }

    return [
      '#theme' => 'cdr_publications',
      '#publications_data' => [
        'uuid' => $uuid,
        'headline' => 'Publications',
        'return_url' => $request_path,
        'author_type' => $entity_type,
        'hide_topics' => $hide_topics,
      ],
    ];
  }

}
