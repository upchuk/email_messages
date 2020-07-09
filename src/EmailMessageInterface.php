<?php

namespace Drupal\email_messages;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an email message entity type.
 */
interface EmailMessageInterface extends ConfigEntityInterface {

  /**
   * Returns the subject.
   *
   * @return string
   */
  public function getSubject();

  /**
   * Returns the message.
   *
   * @return array
   */
  public function getMessage();
}
