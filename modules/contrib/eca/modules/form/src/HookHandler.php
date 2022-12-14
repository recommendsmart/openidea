<?php

namespace Drupal\eca_form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\eca\Event\BaseHookHandler;

/**
 * The handler for hook implementations within the eca_form.module file.
 *
 * @internal
 *   This class is not meant to be used as a public API. It is subject for name
 *   change or may be removed completely, also on minor version updates.
 */
class HookHandler extends BaseHookHandler {

  /**
   * Get the hook handler as service instance.
   *
   * @return \Drupal\eca_form\HookHandler
   *   The hook handler as service instance.
   */
  public static function get(): HookHandler {
    return \Drupal::service('eca_form.hook_handler');
  }

  /**
   * Triggers the event to alter a form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function alter(array &$form, FormStateInterface $form_state): void {
    if (isset($form['#form_id']) && ($form['#form_id'] === 'system_modules_uninstall_confirm_form')) {
      // When this module is being uninstalled via UI, it will lead to a fatal.
      // To avoid this, the module uninstall confirm form is not supported.
      // @see https://www.drupal.org/project/eca/issues/3305797
      return;
    }
    $this->triggerEvent->dispatchFromPlugin('form:form_build', $form, $form_state);
    // Add the handlers on class-level, to avoid expensive and possibly faulty
    // serialization of nested object references during form submissions.
    $form['#process'][] = [static::class, 'process'];
    $form['#after_build'][] = [static::class, 'afterBuild'];
    $form['#validate'][] = [static::class, 'validate'];
    $form['#submit'][] = [static::class, 'submit'];
    $this->addSubmitHandler($form);
  }

  /**
   * Add submit handler to nested elements if necessary.
   *
   * Walks through the element array recursively and adds the extra
   * submit-handler to all elements where necessary.
   *
   * @param array $elements
   *   A render array to walk through.
   */
  protected function addSubmitHandler(array &$elements): void {
    foreach (Element::children($elements) as $key) {
      if (is_array($elements[$key])) {
        if (isset($elements[$key]['#submit'])) {
          $elements[$key]['#submit'][] = [static::class, 'submit'];
        }
        $this->addSubmitHandler($elements[$key]);
      }
    }
  }

  /**
   * Triggers the event to process a form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public static function process(array $form, FormStateInterface $form_state): array {
    static::get()->triggerEvent->dispatchFromPlugin('form:form_process', $form, $form_state);
    return $form;
  }

  /**
   * Triggers the event after form building was completed.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public static function afterBuild(array $form, FormStateInterface $form_state): array {
    static::get()->triggerEvent->dispatchFromPlugin('form:form_after_build', $form, $form_state);
    return $form;
  }

  /**
   * Triggers the event to validate a form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validate(array $form, FormStateInterface $form_state): void {
    static::get()->triggerEvent->dispatchFromPlugin('form:form_validate', $form, $form_state);
  }

  /**
   * Triggers the event to submit a form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function submit(array $form, FormStateInterface $form_state): void {
    static::get()->triggerEvent->dispatchFromPlugin('form:form_submit', $form, $form_state);
  }

}
