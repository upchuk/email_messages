<?php

namespace Drupal\email_messages;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides a view controller for a message log entity type.
 */
class EmailMessageLogViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);
    // The message log has no entity template itself.
    unset($build['#theme']);
    return $build;
  }

}
