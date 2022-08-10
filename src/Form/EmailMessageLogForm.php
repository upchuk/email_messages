<?php

namespace Drupal\email_messages\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the message log entity edit forms.
 */
class EmailMessageLogForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New message log %label has been created.', $message_arguments));
      $this->logger('email_messages')->notice('Created new message log %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The message log %label has been updated.', $message_arguments));
      $this->logger('email_messages')->notice('Updated new message log %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.email_message_log.canonical', ['email_message_log' => $entity->id()]);
  }

}
