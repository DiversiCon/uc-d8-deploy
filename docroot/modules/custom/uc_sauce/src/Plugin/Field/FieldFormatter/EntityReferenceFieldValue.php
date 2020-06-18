<?php

namespace Drupal\uc_sauce\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_field_value",
 *   label = @Translation("Referenced Entity Field Value"),
 *   description = @Translation("Display the value of a selected field."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceFieldValue extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a EntityReferenceEntityFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'entity_field' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Get the field definitions of the entity reference.
    $fields_definitions = $this->getReferenceFieldDefinitions();

    // Prepare the selectable fields with.
    $entity_fields = [];

    // Loop through the fields.
    foreach ($fields_definitions as $field_name => $field_definition) {
      // For now we're only interested in string field types.
      if ($field_definition->getType() === 'string') {
        $entity_fields[$field_name] = $field_definition->getLabel();
      }
    }

    // Build the settings form.
    $elements['entity_field'] = [
      '#type' => 'select',
      '#options' => $entity_fields,
      '#title' => t('Field'),
      '#default_value' => $this->getSetting('entity_field'),
      '#required' => TRUE,
    ];

    $elements['display'] = [
      '#type' => 'markup',
      '#markup' => '<strong>Display Type:</strong> Comma Separated',
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // Retrieve the field definition of the selected field.
    $field_name = $this->getSetting('entity_field');
    $field_definition = $this->getReferenceFieldDefinition($field_name);

    // Build the summary based on if a field is currently selected.
    $summary = [];
    if ($field_definition) {
      $summary[] = t('Rendering @field as comma separated list.', ['@field' => $field_definition->getLabel()]);
    }
    else {
      $summary[] = t('Select a field.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $selected_field = $this->getSetting('entity_field');

    // If a field is selected.
    if ($selected_field) {
      // Loop through the references, retrieving the value of the selected field.
      foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
        $field_value = $entity->get('field_course_name')->getValue()[0]['value'];

        if ($field_value) {
          $elements[] = $field_value;
        }
      }
    }

    // Build the render array. In this case it's just plain text.
    $render = [
      '#plain_text' => implode(', ', $elements),
    ];

    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return ($field_definition->getType() == 'entity_reference');
  }

  /**
   * Get the field definitions of the referenced entity type and bundle.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]|mixed
   *   An array of field definitions.
   */
  protected function getReferenceFieldDefinitions() {
    /* @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = \Drupal::service('entity_field.manager');

    // Get the entity type and bundle of the entity reference field.
    $entity_type = $this->fieldDefinition->get('entity_type');
    $bundle = $this->fieldDefinition->get('bundle');

    // Get the field definitions of the entity reference.
    return $entity_field_manager->getFieldDefinitions($entity_type, $bundle);
  }

  /**
   * Get the definition of the given field from the referenced entity type and bundle.
   *
   * @param string $field_name
   *   The field machine name.
   *
   * @return bool|\Drupal\Core\Field\FieldDefinitionInterface
   *   A field definition.
   */
  protected function getReferenceFieldDefinition($field_name) {
    $field_definitions = $this->getReferenceFieldDefinitions();

    if (array_key_exists($field_name, $field_definitions)) {
      return $field_definitions[$field_name];
    }

    return FALSE;
  }

}
