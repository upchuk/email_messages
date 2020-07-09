<?php

namespace Drupal\email_messages;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * EmailMessageManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
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

    $message = $entity->getMessage();
    $markup = new FormattableMarkup($message['value'], $tokens);
    $message['value'] = $markup->__toString();
    $entity->set('message', $message);
    return $entity;
  }
}
