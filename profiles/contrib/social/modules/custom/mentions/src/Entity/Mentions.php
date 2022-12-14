<?php

namespace Drupal\mentions\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\mentions\MentionsInterface;
use Drupal\user\UserInterface;

/**
 * The Mentions entity.
 *
 * @ContentEntityType(
 *   id = "mentions",
 *   label = @Translation("Mentions"),
 *   handlers = {
 *     "view_builder" = "Drupal\mentions\MentionsViewBuilder",
 *     "views_data" = "Drupal\mentions\MentionsViewsData",
 *     "access" = "Drupal\mentions\MentionsAccessControlHandler",
 *   },
 *   base_table = "mentions",
 *   translatable = TRUE,
 *   data_table = "mentions_field_data",
 *   entity_keys = {
 *     "id" = "mid",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/mentions/{mentions}",
 *   },
 *   field_ui_base_route = "entity.mentions_type.list",
 * )
 */
class Mentions extends ContentEntityBase implements MentionsInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['mid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Mention ID'))
      ->setDescription(t('The primary identifier for a mention.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('Entity ID'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('internal uuid'))
      ->setDescription(t('internal uuid'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('UUID'))
      ->setDescription(t('Mention UUID.'))
      ->setSetting('target_type', 'user')
      ->setReadOnly(TRUE);

    $fields['auid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The author ID of the mention'))
      ->setSetting('target_type', 'user')
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the mention was created.'));

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setDescription(t('The entity type to which this mention is attached.'))
      ->setSetting('max_length', 32);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The user language code.'))
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return (int) $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    $entity = $this->get('auid')->entity;
    assert($entity instanceof UserInterface);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId(): ?int {
    return (int) $this->get('auid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('auid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('auid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMentionedEntity(): ?EntityInterface {
    $entity_type = $this->getMentionedEntityTypeId();
    $entity_id = $this->getMentionedEntityId();
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);

    return $storage->load($entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getMentionedEntityId(): int {
    return (int) $this->get('entity_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getMentionedEntityTypeId(): string {
    return $this->get('entity_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getMentionedUserId(): int {
    return (int) $this->get('uid')->target_id;
  }

}
