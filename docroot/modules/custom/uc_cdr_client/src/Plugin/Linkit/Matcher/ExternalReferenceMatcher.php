<?php

namespace Drupal\uc_cdr_client\Plugin\Linkit\Matcher;

use Drupal\Component\Utility\Html;
use Drupal\linkit\ConfigurableMatcherBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Matcher(
 *   id = "external_reference",
 *   label = @Translation("External Reference")
 * )
 */
class ExternalReferenceMatcher extends ConfigurableMatcherBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return [
      'External reference types: ' . implode(', ', $this->configuration['reference_types']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'reference_types' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['reference_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select the external reference types to match.'),
      '#options' => [
        'faculty' => 'Faculty',
      ],
      '#default_value' => ['faculty'],
      '#weight' => -50,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['reference_types'] = $form_state->getValue('reference_types');
  }

  /**
   * {@inheritdoc}
   */
  public function getMatches($string) {
    // Retrieve cdr client.
    /* @var \Drupal\uc_cdr_client\CdrClient $cdr_client */
    $cdr_client = \Drupal::service('uc_cdr_client.cdr_client');

    // Query for faculty members.
    $data = $cdr_client->getFacultyByTitle($string);

    /* @var \Drupal\uc_cdr_client\Service\CdrFacultyService $cdr_faculty_service */
    $cdr_faculty_service = \Drupal::service('uc_cdr_client.cdr_faculty_service');
    $cdr_results = $cdr_faculty_service->getFacultyMembersByName($string);

    // Process results get relevant data.
    $results = [];
    foreach ($cdr_results as $external_id => $faculty) {
      $title = $faculty['first_name'] . ' ' . $faculty['last_name'];

      $results[] = [
        'title' => $this->buildLabel($title),
        'description' => 'Faculty',
        'path' => $faculty['url_alias'],
        'group' => $this->buildGroup(),
      ];
    }

    return $results;
  }

  /**
   * Builds the external reference label.
   *
   * @param $value
   *   The string to display as the label.
   *
   * @return string
   */
  protected function buildLabel($value) {
    return Html::escape($value);
  }

  /**
   * The group label.
   *
   * @return string
   */
  protected function buildGroup() {
    return 'External Reference';
  }

}
