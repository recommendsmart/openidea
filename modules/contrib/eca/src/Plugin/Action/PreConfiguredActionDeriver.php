<?php

namespace Drupal\eca\Plugin\Action;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\eca\PluginManager\Action;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver for preconfigured actions.
 */
class PreConfiguredActionDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The action entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected ConfigEntityStorageInterface $actionEntityStorage;

  /**
   * This flag is used to prevent recursion within ::getDerivativeDefinitions().
   *
   * @var bool
   */
  static protected bool $recursion = FALSE;

  /**
   * The action plugin manager.
   *
   * @var \Drupal\eca\PluginManager\Action
   */
  protected Action $actionPluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = new static();
    $instance->setActionEntityStorage($container->get('entity_type.manager')->getStorage('action'));
    $instance->setActionPluginManager($container->get('plugin.manager.eca.action'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    // We need to prevent recursion here because $action->getPluginDefinition()
    // below within this method will run into this again.
    if (self::$recursion) {
      return [];
    }
    self::$recursion = TRUE;

    $this->derivatives = [];
    /** @var \Drupal\system\Entity\Action $action */
    foreach ($this->actionEntityStorage->loadMultiple() as $action) {
      $id = $action->id();
      $this->derivatives[$id] = [
        'label' => 'Pre-configured: ' . $action->label(),
        'action_entity_id' => $id,
      ] + $base_plugin_definition;
      $pluginDefinition = $action->getPluginDefinition();
      foreach (['type', 'confirm_form_route_name'] as $key) {
        if (isset($pluginDefinition[$key])) {
          $this->derivatives[$action->id()][$key] = $pluginDefinition[$key];
        }
      }
    }

    // Cache needs to be cleared here, because $action->getPluginDefinition()
    // above within this method may build up an incomplete set of definitions,
    // as we may return an empty array once we detect recursion.
    $this->actionPluginManager->clearCachedDefinitions();
    // Reset the flag as we are now finished building up the action plugins.
    self::$recursion = FALSE;
    return $this->derivatives;
  }

  /**
   * Set the action entity storage.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage
   *   The action entity storage.
   */
  public function setActionEntityStorage(ConfigEntityStorageInterface $storage): void {
    $this->actionEntityStorage = $storage;
  }

  /**
   * Set the action plugin manager.
   *
   * @param \Drupal\eca\PluginManager\Action $manager
   *   The action plugin manager.
   */
  public function setActionPluginManager(Action $manager): void {
    $this->actionPluginManager = $manager;
  }

}
