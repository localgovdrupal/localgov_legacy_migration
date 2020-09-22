<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Lookup IDs from migrations using taxonomy term IDs.
 *
 * @MigrateProcessPlugin(
 *   id = "taxonomy_lookup"
 * )
 *
 * @code
 * localgov_directory_facets_select::
 *   plugin: taxonomy_lookup
 *   migration: directories_facets
 *   source: field_facet_options
 * @endcode
 */
class TaxonomyLookup extends ProcessPluginBase {

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
