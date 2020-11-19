<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use PDO;

/**
 * Migrate paragraphs entity reference to single column layout paragraphs.
 *
 * @MigrateProcessPlugin(
 *   id = "layout_paragraphs",
 *   handle_multiples = TRUE
 * )
 *
 * @code
 * localgov_subsites_content:
 *   plugin: layout_paragraphs
 *   source: field_page_builder_advanced
 * @endcode
 */
class LayoutParagraphs extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $paragraph_ids = [];

    if (!empty($value)) {
      foreach ($value as $pid) {

        // Check if the paragraph has been migrated.
        $paragraph = \Drupal::entityTypeManager()
          ->getStorage('paragraph')
          ->load($pid['target_id']);
        if (!is_null($paragraph)) {
          $paragraph_ids[] = $pid;
          continue;
        }

        // Get connection to source database.
        $source_db_key = \Drupal::config('migrate_plus.migration_group.localgov_migration')->get('shared_configuration')['source']['key'];
        $connection = Database::getConnection('default', $source_db_key);

        // Get any child paragraphs from the source database.
        $query = $connection->select('paragraphs_item_field_data', 'p');
        $query->addField('p', 'id', 'target_id');
        $query->addField('p', 'revision_id', 'target_revision_id');
        $query->condition('p.parent_id', $pid['target_id']);
        $query->condition('p.parent_type', 'paragraph');
        $results = $query->execute();

        // Check if child paragraphs have been migrated.
        foreach ($results->fetchAll(PDO::FETCH_ASSOC) as $result) {
          $paragraph = \Drupal::entityTypeManager()
            ->getStorage('paragraph')
            ->load($result['target_id']);
          if (!is_null($paragraph)) {
            $paragraph_ids[] = $result;
          }
        }

      }
    }

    return $paragraph_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
