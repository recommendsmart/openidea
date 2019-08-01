<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UserStatusCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;

/**
 * User status condition class.
 */
class UserStatusCondition extends Condition {

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'user_status_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => t('User Status'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UserStatusCondition\\UserStatusCondition',
      'library' => 'if_then_else/UserStatusCondition',
      'control_class_name' => 'UserStatusConditionControl',
      'compare_options' => [
        ['code' => 'blocked', 'name' => 'Blocked'],
        ['code' => 'active', 'name' => 'Active'],
      ],
      'inputs' => [
        'user' => [
          'label' => t('User'),
          'description' => t('The user account to check.'),
          'sockets' => ['object.entity.user'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => t('Success'),
          'description' => t('TRUE if the account is blocked.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_status->code)) {
      $event->errors[] = t('Select status to check in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $user = $this->inputs['user'];
    $status = $this->data->selected_status->code;

    $output = FALSE;
    if ($status == 'blocked') {
      $output = $user->isBlocked();
    }
    elseif ($status == 'active') {
      $output = $user->isActive();
    }
    $this->outputs['success'] = $output;

  }

}