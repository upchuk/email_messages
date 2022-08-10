<?php

namespace Drupal\email_messages\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\email_messages\EmailMessageLogInterface;
use Drupal\user\UserInterface;

/**
 * Defines the message log entity class.
 *
 * @ContentEntityType(
 *   id = "email_message_log",
 *   label = @Translation("Message Log"),
 *   label_collection = @Translation("Message Logs"),
 *   handlers = {
 *     "view_builder" = "Drupal\email_messages\EmailMessageLogViewBuilder",
 *     "list_builder" = "Drupal\email_messages\EmailMessageLogListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\email_messages\Form\EmailMessageLogForm",
 *       "edit" = "Drupal\email_messages\Form\EmailMessageLogForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "email_message_log",
 *   admin_permission = "administer message log",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/email-message-logs/add",
 *     "canonical" = "/admin/structure/email-message-logs/{email_message_log}",
 *     "edit-form" = "/admin/structure/email-message-logs/{email_message_log}/edit",
 *     "delete-form" = "/admin/structure/email-message-logs/{email_message_log}/delete",
 *     "collection" = "/admin/structure/email-message-logs"
 *   },
 *   field_ui_base_route = "entity.email_message_log.settings"
 * )
 */
class EmailMessageLog extends ContentEntityBase implements EmailMessageLogInterface {

  /**
   * {@inheritdoc}
   *
   * When a new message log entity is created, set the uid entity reference to
   * the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A custom description.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['rendered_message'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Rendered message'))
      ->setDescription(t('The rendered message with all variables replaced.'))
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['message'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('The message'))
      ->setDescription(t('The message being sent.'))
      ->setSetting('target_type', 'email_message')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
     ->setDisplayConfigurable('form', TRUE)
     ->setDisplayConfigurable('view', TRUE);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t("The target's email"))
      ->setDescription(t('The email where the message went.'))
      ->setDisplayOptions('view', [
        'type' => 'string',
        'label' => 'above',
        'weight' => 30,
      ]);

    $fields['language'] = BaseFieldDefinition::create('language')
      ->setLabel(new TranslatableMarkup('Language'))
      ->setDescription(t('The language in which the email was sent.'))
      ->setDisplayOptions('view', [
        'type' => 'language',
        'label' => 'above',
        'weight' => 40,
      ]);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the message log author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 50,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Sent time'))
      ->setDescription(t('The time that the message log was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 60,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
