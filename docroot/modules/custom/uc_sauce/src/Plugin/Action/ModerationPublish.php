<?php

namespace Drupal\uc_sauce\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sets moderation state on a node to 'published'.
 *
 * @Action(
 *   id = "node_moderation_publish_action",
 *   label = @Translation("Change moderation state to Published"),
 *   type = "node"
 * )
 */
class ModerationPublish extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\node\Entity\Node|null $entity
   *   Entity object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function execute($entity = NULL) {
    if ($entity->hasField('moderation_state')) {
      $current = $entity->get('moderation_state')->getString();
      if ($current == 'draft' or $current == 'unpublished' or $current == 'archived') {
        $entity->set('moderation_state', 'published');
        $entity->save();
      }
      else {
        $this->messenger()
          ->addMessage(
          $entity->getTitle() . ' (' . $entity->id() . ') can not be Published because the current status is not Unpublished, Draft or Archived.  (' . $current . ')',
          'warning');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['confirm_message'] = [
      '#markup' => '<p>The selected items will be Published.</p>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /* @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

}
