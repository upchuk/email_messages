<?php

namespace Drupal\email_messages\Plugin\views\filter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\email_messages\Entity\EmailMessage;
use Drupal\email_messages\Entity\EmailMessageLog;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filter handler for email messages.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("email_message")
 */
class Message extends InOperator {

  protected $alwaysMultiple = TRUE;

  protected function valueForm(&$form, FormStateInterface $form_state) {
    $messages = $this->value ? EmailMessage::loadMultiple($this->value) : [];
    $default_value = EntityAutocomplete::getEntityLabels($messages);
    $form['value'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Messages'),
      '#description' => $this->t('Enter a comma separated list of messages.'),
      '#target_type' => 'email_message',
      '#tags' => TRUE,
      '#default_value' => $messages,
      '#process_default_value' => $this->isExposed(),
    ];

    $user_input = $form_state->getUserInput();
    if ($form_state->get('exposed') && !isset($user_input[$this->options['expose']['identifier']])) {
      $user_input[$this->options['expose']['identifier']] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }

  protected function valueValidate($form, FormStateInterface $form_state) {
    $ids = [];
    if ($values = $form_state->getValue(['options', 'value'])) {
      foreach ($values as $value) {
        $ids[] = $value['target_id'];
      }
      sort($ids);
    }
    $form_state->setValue(['options', 'value'], $ids);
  }

  public function acceptExposedInput($input) {
    $rc = parent::acceptExposedInput($input);

    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validated_exposed_input)) {
        $this->value = $this->validated_exposed_input;
      }
    }

    return $rc;
  }

  public function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }

    if (empty($this->options['expose']['identifier'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];
    $input = $form_state->getValue($identifier);

    if ($this->options['is_grouped'] && isset($this->options['group_info']['group_items'][$input])) {
      $this->operator = $this->options['group_info']['group_items'][$input]['operator'];
      $input = $this->options['group_info']['group_items'][$input]['value'];
    }

    $ids = [];
    $values = $form_state->getValue($identifier);
    if ($values && (!$this->options['is_grouped'] || ($this->options['is_grouped'] && ($input != 'All')))) {
      foreach ($values as $value) {
        $ids[] = $value['target_id'];
      }
    }

    if ($ids) {
      $this->validated_exposed_input = $ids;
    }
  }

  protected function valueSubmit($form, FormStateInterface $form_state) {
    // prevent array filter from removing our anonymous user.
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    return $this->valueOptions;
  }

  public function adminSummary() {
    $this->valueOptions = [];

    if ($this->value) {
      $result = \Drupal::entityTypeManager()->getStorage('email_message')
        ->loadByProperties(['id' => $this->value]);
      foreach ($result as $message) {
        $this->valueOptions[$message->id()] = $message->label();
      }
    }

    return parent::adminSummary();
  }

}
