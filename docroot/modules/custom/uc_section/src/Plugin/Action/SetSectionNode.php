<?php

namespace Drupal\uc_section\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Sets specified section on a node.
 *
 * @Action(
 *   id = "node_set_section_action",
 *   label = @Translation("Set the Section of content"),
 *   type = "node"
 * )
 */
class SetSectionNode extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The term storage class.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $termStorage;

  /**
   * Constructs a new AssignOwnerNode action.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\node\Entity\Node|null $entity
   *   Entity object.
   */
  public function execute($entity = NULL) {
    $entity->set('field_section', [$this->configuration['section_tid']]);
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'section_tid' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    // Get last used value for this user.
    $default_tid = \Drupal::state()->get($this->getStateKey());
    $default = Term::load($default_tid);

    // I would like to use a dropdown or radio buttons, but I was having a hard
    // time finding an already existing method for loading the options on those
    // form elements.  For the sake of time I'm abandoning this approach in
    // favor of the built-in entity_autocomplete element.
    $form['section_tid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => [
          'section',
        ],
      ],
      '#title' => t('Section'),
      '#size' => 24,
      '#default_value' => $default,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // $form_state->setErrorByName('section_tid', t('Enter a valid Section.'));.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['section_tid'] = $form_state->getValue('section_tid');
    \Drupal::state()->set($this->getStateKey(), $form_state->getValue('section_tid'));
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /* @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * Helper method to get proper state key.
   */
  private function getStateKey() {
    return 'uc_section_vbo_default.' . \Drupal::currentUser()->id();
  }

}
