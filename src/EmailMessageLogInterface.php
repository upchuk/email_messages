<?php

namespace Drupal\email_messages;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a message log entity type.
 */
interface EmailMessageLogInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the message log creation timestamp.
   *
   * @return int
   *   Creation timestamp of the message log.
   */
  public function getCreatedTime();

  /**
   * Sets the message log creation timestamp.
   *
   * @param int $timestamp
   *   The message log creation timestamp.
   *
   * @return \Drupal\email_messages\EmailMessageLogInterface
   *   The called message log entity.
   */
  public function setCreatedTime($timestamp);

}
