<?php

namespace Drupal\node_singles\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeTypeInterface;

/**
 * Alters the node type form.
 */
class NodeTypeFormEventSubscriber {

  use StringTranslationTrait;

  /**
   * Add a checkbox to node type forms to mark them as single.
   */
  public function alterNodeTypeForm(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\node\NodeTypeInterface $type */
    $type = $form_state->getFormObject()->getEntity();

    $form['node_singles'] = [
      '#type' => 'details',
      '#title' => $this->t('Singles'),
      '#group' => 'additional_settings',
    ];

    $form['node_singles']['is-single'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('This is a content type with a single entity.'),
      '#default_value' => $type->getThirdPartySetting('node_singles', 'is_single', FALSE),
      '#description' => $this->t('The entity will be created after you save this content type.'),
    ];

    $form['#entity_builders'][] = [static::class, 'formBuilder'];
  }

  /**
   * Store the setting in the node type's third party settings.
   */
  public static function formBuilder($entity_type, NodeTypeInterface $type, &$form, FormStateInterface $form_state): void {
    $type->setThirdPartySetting('node_singles', 'is_single', $form_state->getValue('is-single'));
  }

}
