<?php

namespace Drupal\email_messages\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\email_messages\EmailMessageInterface;

/**
 * Defines the email message entity type.
 *
 * @ConfigEntityType(
 *   id = "email_message",
 *   label = @Translation("Email Message"),
 *   label_collection = @Translation("Email Messages"),
 *   label_singular = @Translation("email message"),
 *   label_plural = @Translation("email messages"),
 *   label_count = @PluralTranslation(
 *     singular = "@count email message",
 *     plural = "@count email messages",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\email_messages\EmailMessageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\email_messages\Form\EmailMessageForm",
 *       "edit" = "Drupal\email_messages\Form\EmailMessageForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "email_message",
 *   admin_permission = "administer email messages",
 *   links = {
 *     "collection" = "/admin/structure/email-message",
 *     "add-form" = "/admin/structure/email-message/add",
 *     "edit-form" = "/admin/structure/email-message/{email_message}",
 *     "delete-form" = "/admin/structure/email-message/{email_message}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "subject",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "subject",
 *     "message"
 *   }
 * )
 */
class EmailMessage extends ConfigEntityBase implements EmailMessageInterface {

  /**
   * The ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The subject.
   *
   * @var string
   */
  protected $subject = '';

  /**
   * The email message.
   *
   * @var array
   */
  protected $message = [];

  /**
   * @inheritDoc
   */
  public function getSubject() {
    return $this->subject;
  }

  /**
   * @inheritDoc
   */
  public function getMessage() {
    return $this->message;
  }
}
