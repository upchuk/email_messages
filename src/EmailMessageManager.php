<?php

namespace Drupal\email_messages;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\email_messages\Entity\EmailMessageLog;

/**
 * Manages the email messages.
 */
class EmailMessageManager {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * @var \Drupal\email_messages\EmailMessageRenderer
   */
  protected $renderer;

  /**
   * EmailMessageManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   * @param \Drupal\email_messages\EmailMessageRenderer $renderer
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, MailManagerInterface $mailManager, EmailMessageRenderer $renderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->mailManager = $mailManager;
    $this->renderer = $renderer;
  }

  /**
   * Returns a a given message with the tokens replaced.
   *
   * @param $id
   *   The config ID.
   * @param array $tokens
   *   The tokens to replace.
   * @param string $langcode
   *   The language code to load the config in.
   *
   * @return EmailMessageInterface
   */
  public function getMessage($id, $tokens = [], $langcode = NULL) {
    if (!$langcode) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $language = $this->languageManager->getLanguage($langcode);
    $original_language = $this->languageManager->getConfigOverrideLanguage();
    $this->languageManager->setConfigOverrideLanguage($language);

    /** @var \Drupal\email_messages\EmailMessageInterface $entity */
    $entity = $this->entityTypeManager->getStorage('email_message')->load($id);
    $this->languageManager->setConfigOverrideLanguage($original_language);

    if (!$entity) {
      return NULL;
    }

    if (!$tokens) {
      return $entity;
    }

    // Render each token if it's a render array.
    foreach ($tokens as $token => &$value) {
      if (is_string($value)) {
        continue;
      }

      if (is_array($value) && (isset($value['#type']) || isset($value['#theme']))) {
        $value = $this->renderer->render($value);
        continue;
      }

      // Otherwise, we cannot process it so we should replace with empty.
      $value = '';
    }

    // Replace the message variables.
    $message = $entity->getMessage();
    $markup = new FormattableMarkup($message['value'], $tokens);
    $message['value'] = $markup->__toString();
    $entity->set('message', $message);

    // Replace the subject variables.
    $subject = $entity->getSubject();
    $entity->set('subject', (new FormattableMarkup($subject, $tokens))->__toString());

    return $entity;
  }

  /**
   * Mails the message.
   *
   * @see \Drupal\Core\Mail\MailManagerInterface::mail()
   */
  public function mailMessage(EmailMessageInterface $message, $to, $params = [], $module = NULL, $key = NULL, $reply = NULL, $send = TRUE) {
    if (!$module) {
      $module = 'email_messages';
    }

    if (!$key) {
      $key = 'notification';
    }

    if (!isset($params['subject'])) {
      $params['subject'] = $message->getSubject();
    }

    if (!isset($params['message'])) {
      $params['message'] = [
        '#type' => 'processed_text',
        '#text' => $message->getMessage()['value'],
        '#format' => $message->getMessage()['format'],
      ];
    }

    $params['message'] = $this->renderer->render($params['message']);

    $result = $this->mailManager->mail($module, $key, $to, $message->language()->getId(), $params, $reply, $send);

    if ($message->logsMessage()) {
      $values = $params['log_message_values'] ?? [];
      $values += [
        'rendered_message' => [
          'value' => $params['message'],
          'format' => $message->getMessage()['format'],
        ],
        'email' => $to,
        'message' => $message->id(),
        'language' => $message->language()->getId(),
      ];
      EmailMessageLog::create($values)->save();
    }

    return $result;
  }
}
