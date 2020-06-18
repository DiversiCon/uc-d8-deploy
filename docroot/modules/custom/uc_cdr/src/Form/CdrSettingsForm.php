<?php

namespace Drupal\uc_cdr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for UC CDR functionality.
 */
class CdrSettingsForm extends ConfigFormBase {
  /**
 * @var string Config settings */
  const SETTINGS = 'uc_cdr.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_cdr_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Use this form to associate BSD Instances to Departments'),
    ];

    $config = $this->configFactory->getEditable(static::SETTINGS);
    foreach ($config->get('department_mapping') as $instance => $depts) {
      $instances[] = $instance;

      $terms = [];
      foreach ($depts as $term_name) {
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $term_name]);
        $terms[] = array_shift($term);
      }

      $departments[] = $terms;
    }

    $num_instances = $form_state->get('num_instances');
    if ($num_instances === NULL) {
      $instance_field = $form_state->set('num_instances', count($instances));
      $num_instances = count($instances);
    }

    $form['#tree'] = TRUE;
    $form['instances_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('BSD Instance Mapping'),
      '#prefix' => "<div id='instances-fieldset-wrapper'>",
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_instances; $i++) {
      $form['instances_fieldset'][$i]['instance'] = [
        '#type' => 'fieldset',
        '#prefix' => "<div id='instance-wrapper'>",
        '#suffix' => '</div>',
      ];
      $form['instances_fieldset'][$i]['instance']['instance_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('BSD Instance ID'),
        '#description' => $this->t('Enter a BSD Instance ID (ex: "pritzker")'),
        '#default_value' => $instances[$i],
        '#prefix' => "<div class='inner-fieldset'>",
      ];

      $form['instances_fieldset'][$i]['instance']['department'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'taxonomy_term',
        '#title' => $this->t('Departments'),
        '#maxlength' => 2048,
        '#description' => $this->t('Enter departments separated by commas.'),
        '#default_value' => $departments[$i],
        '#tags' => TRUE,
        '#selection_settings' => [
          'target_bundles' => ['event_departments'],
        ],
        '#suffix' => '</div>',
      ];
    }

    $form['instances_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    $form['instances_fieldset']['actions']['add_instance'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Instance'),
      '#submit'  => ['::addInstance'],
      '#ajax' => [
        'callback' => '::addInstanceCallback',
        'wrapper' => 'instances-fieldset-wrapper',
      ],
    ];

    if ($num_instances > 1) {
      $form['instances_fieldset']['actions']['remove_instance'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove Instance'),
        '#submit' => ['::removeInstanceCallback'],
        '#ajax' => [
          'callback' => '::addInstanceCallback',
          'wrapper' => 'instances-fieldset-wrapper',
        ],
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   *
   */
  public function addInstanceCallback(array &$form, FormStateInterface $form_state) {
    return $form['instances_fieldset'];
  }

  /**
   *
   */
  public function addInstance(array &$form, FormStateInterface $form_state) {
    $instance_field = $form_state->get('num_instances');
    $add_button = $instance_field + 1;
    $form_state->set('num_instances', $add_button);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function removeInstanceCallback(array &$form, FormStateInterface $form_state) {
    $instance_field = $form_state->get('num_instances');
    if ($instance_field > 1) {
      $remove_button = $instance_field - 1;
      $form_state->set('num_instances', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    foreach ($form_state->getValue(['instances_fieldset']) as $key => $instance) {
      if (is_array($instance) && isset($instance['instance'])) {
        $value = $instance['instance'];
        if (is_array($value) && array_key_exists('instance_id', $value)) {
          foreach (array_values($value['department']) as $dept_key => $dept_value) {
            $tid = (int) $dept_value['target_id'];
            $term = \Drupal::entityTypeManager()
              ->getStorage('taxonomy_term')
              ->load($tid);
            $instance_map[$value['instance_id']][] = $term->getName();
          }
        }
      }
    }

    $config = $this->configFactory->getEditable(static::SETTINGS);
    $config->set("department_mapping", []);
    foreach ($instance_map as $instance_id => $departments) {
      $config->set("department_mapping.$instance_id", $departments);
    }
    $config->save();

    parent::submitForm($form, $form_state);

  }

}
