<?php

namespace Drupal\eca_views\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\ItemList;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\views\Entity\View;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Run views query.
 *
 * @Action(
 *   id = "eca_views_query",
 *   label = @Translation("Views: Execute query")
 * )
 */
class ViewsQuery extends ConfigurableActionBase {

  /**
   * The executable view.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected ViewExecutable $view;

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies(): array {
    return [
      'config' => [
        'views.view.' . $this->getViewId(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL): void {
    if (!($display = $this->getDisplay())) {
      return;
    }
    $display->execute();
    $token_name = trim($this->configuration['token_name']);
    if ($token_name === '') {
      $token_name = implode(':', [
        'eca',
        'view',
        $this->getViewId(),
        $this->getDisplayId(),
      ]);
    }
    $entities = ItemList::createInstance(ListDataDefinition::create('entity'));
    $entities->setValue($this->getEntities());
    $this->tokenServices->addTokenData($token_name, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::forbidden();
    if (($display = $this->getDisplay()) && $display->access($account)) {
      $result = AccessResult::allowed();
    }
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'token_name' => '',
      'view_id' => '',
      'display_id' => 'default',
      'arguments' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $views = [];
    foreach (View::loadMultiple() as $view) {
      if ($view->status()) {
        $views[$view->id()] = $view->label();
      }
    }
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#weight' => -60,
    ];
    $form['view_id'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#default_value' => $this->configuration['view_id'],
      '#weight' => -50,
      '#options' => $views,
      '#required' => TRUE,
    ];
    $form['display_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display'),
      '#default_value' => $this->configuration['display_id'],
      '#weight' => -40,
    ];
    $form['arguments'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Arguments'),
      '#default_value' => $this->configuration['arguments'],
      '#weight' => -30,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    $this->configuration['view_id'] = $form_state->getValue('view_id');
    $this->configuration['display_id'] = $form_state->getValue('display_id');
    $this->configuration['arguments'] = $form_state->getValue('arguments');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Prepare and return display of given view with optional arguments applied.
   *
   * The view gets loaded, its executable set to $this->view and the given
   * display being prepared with optional arguments.
   *
   * @return \Drupal\views\Plugin\views\display\DisplayPluginBase|null
   *   The view display ready for access check and execution, or NULL if either
   *   the view or display does not exist or the view is disabled.
   */
  protected function getDisplay(): ?DisplayPluginBase {
    $view_id = $this->getViewId();
    $display_id = $this->getDisplayId();
    if ($view_id === '' || $display_id === '') {
      return NULL;
    }

    $view = View::load($view_id);
    if (!$view || !$view->status()) {
      return NULL;
    }

    $this->view = $view->getExecutable();
    if (!$this->view->setDisplay($this->getDisplayId())) {
      return NULL;
    }

    $args = [];
    foreach (explode('/', $this->getArguments()) as $argument) {
      if ($argument !== '') {
        $args[] = $argument;
      }
    }
    $this->view->setArguments($args);
    return $this->view->getDisplay();
  }

  /**
   * Get the configured view ID.
   *
   * @return string
   *   The view ID.
   */
  protected function getViewId(): string {
    return trim((string) $this->tokenServices->replaceClear($this->configuration['view_id']));
  }

  /**
   * Get the configured display ID.
   *
   * @return string
   *   The display ID.
   */
  protected function getDisplayId(): string {
    return trim((string) $this->tokenServices->replaceClear($this->configuration['display_id']));
  }

  /**
   * Get the configured Views arguments.
   *
   * @return string
   *   The arguments, multiple arguments are seraparated by "/".
   */
  protected function getArguments(): string {
    return trim((string) $this->tokenServices->replaceClear($this->configuration['arguments']));
  }

  /**
   * Extracts the entities out of the executed View query.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The extracted entities.
   */
  protected function getEntities(): array {
    $entities = [];

    $view = $this->view;
    foreach ($view->result as $row) {
      $entity = $row->_entity ?? NULL;
      if (!$entity) {
        continue;
      }

      if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
        // Try to find a field alias for the langcode.
        // Assumption: translatable entities always
        // have a langcode key.
        $language_field = '';
        $langcode_key = $entity->getEntityType()->getKey('langcode');
        $base_table = $view->storage->get('base_table');
        foreach ($view->query->fields as $field) {
          if (
            $field['field'] === $langcode_key && (
              empty($field['base_table']) ||
              $field['base_table'] === $base_table
            )
          ) {
            $language_field = $field['alias'];
            break;
          }
        }
        if (!$language_field) {
          $language_field = $langcode_key;
        }

        if (isset($row->{$language_field})) {
          $entity = $entity->getTranslation($row->{$language_field});
        }
      }

      if (!in_array($entity, $entities, TRUE)) {
        $entities[] = $entity;
      }
    }

    return $entities;
  }

}
