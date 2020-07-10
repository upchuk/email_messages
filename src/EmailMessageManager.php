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
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * @var \Drupal\Core\Theme\ThemeInitializationInterface
   */
  protected $themeInitialization;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * EmailMessageManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   * @param \Drupal\Core\Theme\ThemeInitializationInterface $themeInitialization
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, MailManagerInterface $mailManager, ThemeManagerInterface $themeManager, ThemeInitializationInterface $themeInitialization, ThemeHandlerInterface $themeHandler, RendererInterface $renderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->mailManager = $mailManager;
    $this->themeManager = $themeManager;
    $this->themeInitialization = $themeInitialization;
    $this->themeHandler = $themeHandler;
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

    $message = $entity->getMessage();
    $markup = new FormattableMarkup($message['value'], $tokens);
    $message['value'] = $markup->__toString();
    $entity->set('message', $message);
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

    $active_theme = $this->setRenderTheme();
    $params['message'] = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($params) {
      return $this->renderer->render($params['message']);
    });
    $this->restoreDefaultTheme($active_theme);

    return $this->mailManager->mail($module, $key, $to, $message->language()->getId(), $params, $reply, $send);
  }

  /**
   * Sets the render theme and returns the original.
   *
   * @return \Drupal\Core\Theme\ActiveTheme
   * @throws \Drupal\Core\Theme\MissingThemeDependencyException
   */
  protected function setRenderTheme() {
    $active_theme = $this->themeManager->getActiveTheme();
    $frontend_theme = $this->themeInitialization->getActiveThemeByName($this->themeHandler->getDefault());
    $this->themeManager->setActiveTheme($frontend_theme);
    return $active_theme;
  }

  /**
   * Restores the original theme.
   *
   * @param $active_theme
   */
  protected function restoreDefaultTheme($active_theme) {
    $this->themeManager->setActiveTheme($active_theme);
  }
}
