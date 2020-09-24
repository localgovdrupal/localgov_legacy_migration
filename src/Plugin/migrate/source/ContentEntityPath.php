<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal_d8\Plugin\migrate\source\d8\ContentEntity;

/**
 * Content entity migration for content with paths to migrate.
 *
 * @MigrateSource(
 *   id = "d8_entity_path",
 *   source_provider = "localgov_leagcy_migration"
 * )
 */
class ContentEntityPath extends ContentEntity {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return ['alias' => $this->t('Path alias')] + parent::fields();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $entity_type = $this->configuration['entity_type'];

    if ($entity_type == 'node') {
      $id = $row->getSourceProperty('nid');
      $path = '/node/' . $id;
    }
    elseif ($entity_type == 'taxonomy_term') {
      $id = $row->getSourceProperty('tid');
      $path = '/taxonomy/term/' . $id;
    }

    $query = $this->select('path_alias', 'p')
      ->fields('p', ['alias']);
    $query->condition('p.path', $path);
    $alias = $query->execute()->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', $alias);
    }

    return parent::prepareRow($row);
  }

}
