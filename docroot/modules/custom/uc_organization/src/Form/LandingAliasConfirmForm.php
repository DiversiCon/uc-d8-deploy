<?php

namespace Drupal\uc_organization\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Class LandingAliasConfirmForm.
 *
 * @package Drupal\uc_organization\Form
 */
class LandingAliasConfirmForm extends FormBase {

  protected $affected = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_organization_landing_alias_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get old/new organization landing page nid.
    $nid_was = \Drupal::request()->query->get('was', NULL);
    $nid_is = \Drupal::request()->query->get('is', NULL);
    $form['nid_was'] = [
      '#type' => 'hidden',
      '#value' => $nid_was,
    ];
    $form['nid_is'] = [
      '#type' => 'hidden',
      '#value' => $nid_is,
    ];

    // Load affected landing page nodes.
    $this->getAffectedNode($nid_was, 'was');
    $this->getAffectedNode($nid_is, 'is');

    $desc = $this->t('You just changed the main landing page for this organization.  This change may impact the automatically assigned aliases of the related pages.  Please review the following and select the appropriate action.');
    $form['description'] = [
      '#markup' => '<p>' . $desc . '</p>',
    ];

    // Create a field group for each affected node.
    foreach ($this->affected as $delta => $affected_item) {
      $group_key = 'landing_' . $affected_item['which'];

      // Setup text value to reflect status of auto-aliasing.
      if ($affected_item['auto_alias']) {
        $auto_alias_status = 'ENABLED';
        if ($affected_item['url'] !== $affected_item['new_url']) {
          $auto_alias_suffix = 'Clicking the "Update aliases" button will update the current URL alias with the new URL alias as shown below.';
        }
        else {
          $auto_alias_suffix = 'However, no changes are required because the current and proposed URL aliases are identical.';
          $form['nid_' . $affected_item['which']]['#value'] = NULL;
        }
      }
      else {
        $auto_alias_status = 'DISABLED';
        $auto_alias_suffix = 'Therefore, the current URL alias shown below can not be automatically changed.';
        $form['nid_' . $affected_item['which']]['#value'] = NULL;
      }

      // Add group wrapper element.
      $group_title = ($affected_item['which'] == 'was') ? $this->t('Original Landing Page') : $this->t('New Landing Page');
      $form[$group_key] = [
        '#type' => 'fieldset',
        '#title' => $group_title,
      ];

      // Add node title.
      $form[$group_key]['node_title'] = [
        '#markup' => '<h3>' . $affected_item['node']->getTitle() . '</h3>',
      ];

      // Add auto-alias setting.
      $form[$group_key]['auto_alias'] = [
        '#markup' => '<p>Automatic alias generation is <strong>' . $auto_alias_status . '</strong> for this page.&nbsp;&nbsp;' . $auto_alias_suffix . '</p>',
      ];

      // Add current url.
      $form[$group_key]['url'] = [
        '#markup' => '<p>Current URL alias: <strong>' . $affected_item['url'] . '</strong></p>',
      ];

      // Add new url if it is being auto-aliased.
      if ($affected_item['auto_alias']) {
        $form[$group_key]['new_url'] = [
          '#markup' => '<p>Proposed URL alias: <strong>' . $affected_item['new_url'] . '</strong></p>',
        ];
      }
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update aliases'),
      '#button_type' => 'primary',
    ];
    $form['actions']['skip'] = [
      '#type' => 'submit',
      '#value' => $this->t('Skip'),
      '#submit' => ['::cancelForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('nid_was') || $form_state->getValue('nid_is')) {
      $this->saveNode($form_state->getValue('nid_was'));
      $this->saveNode($form_state->getValue('nid_is'));
      drupal_set_message($this->t('Aliases updated.'));
    }
    else {
      drupal_set_message($this->t('No alias updates required.'));
    }
  }

  /**
   * Cancel submit handler.
   */
  public function cancelForm() {
    drupal_set_message($this->t('Alias update skipped.'));
  }

  /**
   * Save a node.
   */
  private function saveNode($nid) {
    if ($nid) {
      $node = Node::load($nid);
      $node->save();
    }
  }

  /**
   * Helper function to get a node.
   */
  private function getAffectedNode($nid, $which) {
    if ($nid) {
      $node = Node::load($nid);
      if ($node) {
        $scheme = \Drupal::request()->getSchemeAndHttpHost();
        $path = \Drupal::service('pathauto.generator')
          ->createEntityAlias($node, 'return');
        $this->affected[] = [
          'which' => $which,
          'nid' => $nid,
          'node' => $node,
          'url' => str_replace($scheme, '', $node->toUrl()->setAbsolute()->toString()),
          'new_url' => $path,
          'auto_alias' => $this->getKeyValue($nid),
        ];
      }
    }
  }

  /**
   * Helper function to get a key value.
   */
  private function getKeyValue($nid) {
    $value = FALSE;

    $database = \Drupal::service('database');
    /* @var \Drupal\Core\Database\Query\Select $query */
    $query = $database->select('key_value', 'k');
    $query->condition('k.collection', 'pathauto_state.node');
    $query->condition('k.name', $nid);
    $query->addField('k', 'value');
    $results = $query->execute();
    $rows = $results->fetchAll();
    if ($rows) {
      $value = unserialize($rows[0]->value);
    }

    return $value;
  }

}
