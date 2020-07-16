<?php

namespace Drupal\email_messages;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Renders email messages in the current theme.
 */
class EmailMessageRenderer {

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
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   * @param \Drupal\Core\Theme\ThemeInitializationInterface $themeInitialization
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(ThemeManagerInterface $themeManager, ThemeInitializationInterface $themeInitialization, ThemeHandlerInterface $themeHandler, RendererInterface $renderer) {
    $this->themeManager = $themeManager;
    $this->themeInitialization = $themeInitialization;
    $this->themeHandler = $themeHandler;
    $this->renderer = $renderer;
  }

  /**
   * Renders a render array for the email.
   *
   * @param array $build
   *
   * @return \Drupal\Component\Render\MarkupInterface
   */
  public function render(array $build) {
    $active_theme = $this->setRenderTheme();
    $render = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($build) {
      return $this->renderer->render($build);
    });
    $this->restoreDefaultTheme($active_theme);

    return $render;
  }

  /**
   * Sets the render theme and returns the original.
   *
   * @return \Drupal\Core\Theme\ActiveTheme
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
