<?php

namespace Drupal\email_messages\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Email Message form.
 *
 * @property \Drupal\email_messages\EmailMessageInterface $entity
 */
class EmailMessageForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The message subject.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\email_messages\Entity\EmailMessage::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $default = $this->entity->get('message');
    $form['message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('The message'),
      '#default_value' => $default ? $default['value'] : '',
      '#description' => $this->t('The message body. For custom tokens, use "@variable" and they will be replaced dynamically.'),
      '#format' => $default ? $default['format'] : 'basic_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->label()];

    $message = $result == SAVED_NEW
      ? $this->t('Created new message with the subject %label.', $message_args)
      : $this->t('Updated the message with the subject %label.', $message_args);

    $this->messenger()->addStatus($message);

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
