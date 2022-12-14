<?php

namespace Drupal\maestro\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\maestro\Engine\MaestroEngine;

/**
 * The Maestro Reassign form.
 */
class MaestroReassign extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'maestro_reassignment_form';
  }

  /**
   * This is the reassignment form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $assignmentID = NULL) {
    // first, see if this is a legit assignment ID.
    $assignRecord = \Drupal::entityTypeManager()->getStorage('maestro_production_assignments')->load($assignmentID);
    if ($assignRecord) {
      $queueRecord = MaestroEngine::getQueueEntryById($assignRecord->queue_id->getString());
      // now, display what's in the assignment record and based on the assign type, let this module or others set the allowable selections to be displayed.
      $form = [];

      $form['assignment_id'] = [
        '#type' => 'hidden',
        '#default_value' => $assignmentID,
      ];

      $form['assign_type'] = [
        '#type' => 'hidden',
        '#default_value' => $assignRecord->assign_type->getString(),
      ];

      $form['assignment_table'] = [
        '#type' => 'table',
        '#caption' => $this->t('Current Assignment'),
        '#header' =>
          [$this->t('Task'), $this->t('By'), $this->t('Assigned'), $this->t('How Assigned')],
      // This really shouldn't happen, but it's a catch all.
        '#empty' => $this->t('Nothing to reassign!'),
      ];

      $form['assignment_table'][0]['task'] = [
        '#plain_text' => $queueRecord->task_label->getString(),
      ];

      $form['assignment_table'][0]['by'] = [
        '#plain_text' => $assignRecord->assign_type->getString(),
      ];

      $form['assignment_table'][0]['assigned'] = [
        '#plain_text' => $assignRecord->assign_id->getString(),
      ];

      $form['assignment_table'][0]['by_variable'] = [
        '#plain_text' => $assignRecord->by_variable->getString() == 0 ? $this->t('Fixed') : $this->t('Variable'),
      ];

      // OK, so now, when we reassign this task, we will be changing the assignment to fixed no matter if it is by variable....
      // Developers/Administrator note:  Please note that a by-variable assignment can get augmented on-the-fly if the variable assignment alters
      //                                at some point during it's lifetime.  That means that this task may get assigned, at some point, to a variable value
      //                                along with the fixed assignment if the variable changes via the Maestro API.
      // now provide the reassignment mechanism here.  We provide user and role.  Other modules can provide whatever they want.
      // Provide a user lookup.
      if ($assignRecord->assign_type->getString() == 'user') {

        $form['select_assigned_user'] = [
          '#id' => 'select_assigned_user',
          '#type' => 'entity_autocomplete',
          '#target_type' => 'user',
          '#default_value' => '',
          '#selection_settings' => ['include_anonymous' => FALSE],
          '#title' => $this->t('User to Reassign To'),
          '#required' => FALSE,
        ];

      }
      // Provide a role lookup.
      elseif ($assignRecord->assign_type->getString() == 'role') {
        $form['select_assigned_role'] = [
          '#id' => 'select_assigned_role',
          '#type' => 'entity_autocomplete',
          '#target_type' => 'user_role',
          '#default_value' => '',
          '#title' => $this->t('Role to Reassign To'),
          '#required' => FALSE,
        ];
      }
      else {
        // TODO: let other modules handle their form elements here.
      }

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Do Reassignment'),
      ];

      return $form;
    }
    // This entry doesn't exist.  Stop messing around!
    else {
      \Drupal::messenger()->addError(t('Invalid assignment record!'));
      return ['#markup' => $this->t('Invalid Assignment Record. Operation Halted.')];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Based on the assignment type, we must validate the form to ensure either the user or role has been entered, and that it's valid.
    // if the assign type is anything but the role or user, we offload to other modules to handle.
    $assign_type = $form_state->getValue('assign_type');
    if ($assign_type == 'user') {
      $user = $form_state->getValue('select_assigned_user');
      if (!isset($user)) {
        $form_state->setErrorByName('select_assigned_user', $this->t('You must choose a user to reassign to'));
      }
    }
    elseif ($assign_type == 'role') {
      $role = $form_state->getValue('select_assigned_role');
      if (!isset($role)) {
        $form_state->setErrorByName('select_assigned_role', $this->t('You must choose a role to reassign to'));
      }
    }
    else {
      // TODO: offload to module to do validation of whatever assign type this is.
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We do the reassignment here.
    $assign_type = $form_state->getValue('assign_type');
    $entity = '';
    if ($assign_type == 'user') {
      $uid = $form_state->getValue('select_assigned_user');
      // This now holds the user ID.  translate that into username.
      $account = User::load($uid);
      $entity = $account->getDisplayName();

    }
    elseif ($assign_type == 'role') {
      $entity = $form_state->getValue('select_assigned_role');
    }
    else {
      // TODO: offload to module to do validation of whatever assign type this is
      // set the $entity variable here.
    }

    if (isset($entity)) {
      // Set the field here.
      $assignmentID = $form_state->getValue('assignment_id');
      $assignRecord = \Drupal::entityTypeManager()->getStorage('maestro_production_assignments')->load($assignmentID);
      if ($assignRecord) {
        $assignRecord->set('assign_id', $entity);
        // We force this to be by fixed value now.
        $assignRecord->set('by_variable', '0');
        $assignRecord->save();
      }
    }

  }

}
