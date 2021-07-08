<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal_d8\Plugin\migrate\source\d8\ContentEntity;

/**
 * Content entity migration for content with paths to migrate.
 *
 * @MigrateSource(
 *   id = "d8_entity_revisions",
 *   source_provider = "localgov_leagcy_migration"
 * )
 */
class ContentEntityRevisions extends ContentEntity {

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues($entity_type, $field_name, $entity_id, $revision_id = NULL) {
    // Clone of migrate_drupal_d8 getFieldValues,
    // with the bool option set to force fetching the revision tables.
    $table = $this->getDedicatedDataTableName($entity_type, $field_name, (bool) $revision_id);

    $query = $this->select($table, 't')
      ->fields('t')
      ->condition('entity_id', $entity_id)
      ->condition('deleted', 0);

    if ($revision_id) {
      $query->condition('revision_id', $revision_id);
    }

    $values = [];
    foreach ($query->execute() as $row) {
      foreach ($row as $key => $value) {
        $delta = $row['delta'];
        if (strpos($key, $field_name) === 0) {
          $column = substr($key, strlen($field_name) + 1);
          $values[$delta][$column] = $value;
        }
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $entityDefinition = $this->entityTypeManager->getDefinition($this->configuration['entity_type']);

    // Get the revision id key.
    $revisionKey = $entityDefinition->getKey('revision');

    // Get the entity id.
    $idKey = $entityDefinition->getKey('id');

    // Get the bundle key.
    $bundleKey = $entityDefinition->getKey('bundle');

    // Set the base table to the revision table.
    $baseTable = $entityDefinition->getRevisionTable();

    // Get the revision data table.
    $revisionDataTable = $entityDefinition->getRevisionDataTable();

    // Get the entity table.
    $entityTable = $entityDefinition->getBaseTable();

    // Get the entity data table.
    $dataTable = $entityDefinition->getDataTable();

    // If there is a data table (nodes etc..)
    if ($dataTable) {

      // Query the revision data table.
      $query = $this->select($revisionDataTable, 'r')
        ->fields('r');

      // Join the base revision table to get extra fields.
      $alias = $query->innerJoin($baseTable, 'b', "r.{$revisionKey} = b.{$revisionKey}");

      // Join to the main data table to get the bundle.
      $query->innerJoin($dataTable, 'd', "r.{$idKey} = d.{$idKey}");

      // Add base fields.
      $query->fields($alias);

      // Only get entites from the selected bundle.
      if (!empty($this->configuration['bundle'])) {
        $query->condition("d.{$bundleKey}", $this->configuration['bundle']);
      }
    }

    // Entities that don't have a data table (untested).
    else {
      $query = $this->select($baseTable, 'b')
        ->fields('b');

      // Join to the entity table to query the selected bundle only.
      $entity = $query->innerJoin($entityTable, 'e', "b.{$idKey} = e.{$idKey}");
      if (!empty($this->configuration['bundle'])) {
        $query->condition("e.{$bundleKey}", $this->configuration['bundle']);
      }
    }

    return $query;

  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $entityDefinition = $this->entityTypeManager->getDefinition($this->configuration['entity_type']);

    // Use the revision_id and the entity_id (nid) as the source IDs.
    $revisionKey = $entityDefinition->getKey('revision');
    $idKey = $entityDefinition->getKey('id');
    $ids[$revisionKey] = $this->getDefinitionFromEntity($revisionKey);
    $ids[$idKey] = $this->getDefinitionFromEntity($idKey);

    // If hs a language code, also add that as a source ID.
    if ($entityDefinition->isTranslatable()) {
      $langcodeKey = $entityDefinition->getKey('langcode');
      $ids[$langcodeKey] = $this->getDefinitionFromEntity($langcodeKey);
    }

    return $ids;
  }

}
