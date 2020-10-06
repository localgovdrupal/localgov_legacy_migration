<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Lookup IDs from migrations using entity reference IDs.
 *
 * @MigrateProcessPlugin(
 *   id = "entity_ref_lookup"
 * )
 *
 * To do custom value transformations use the following:
 *
 * @code
 * localgov_image:
 *   plugin: entity_ref_lookup
 *   source: field_image
 *   migration: media_image
 * @endcode
 */
class EntityReferenceLookup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value['target_id'])) {

      // Query migrate map table for destination ID using term ID.
      $migration = $this->configuration['migration'];
      $connection = Database::getConnection();
      $query = $connection->select('migrate_map_' . $migration, 'm');
      $query->addField('m', 'destid1');
      $query->condition('m.sourceid1', $value['target_id']);
      $result = $query->execute();

      if ($id = $result->fetchField()) {
        return $id;
      }
    }

    return NULL;
  }

}
