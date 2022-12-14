<?php

namespace Drupal\tms\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Ticket entities.
 *
 * @ingroup tms
 */
interface TicketInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Ticket name.
   *
   * @return string
   *   Name of the Ticket.
   */
  public function getName();

  /**
   * Sets the Ticket name.
   *
   * @param string $name
   *   The Ticket name.
   *
   * @return \Drupal\tms\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setName($name);

  /**
   * Gets the Ticket creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Ticket.
   */
  public function getCreatedTime();

  /**
   * Sets the Ticket creation timestamp.
   *
   * @param int $timestamp
   *   The Ticket creation timestamp.
   *
   * @return \Drupal\tms\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Ticket revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Ticket revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\tms\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Ticket revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Ticket revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\tms\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setRevisionUserId($uid);

}
