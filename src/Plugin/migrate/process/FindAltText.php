<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Find alt text from image fields with a provided file_id.
 *
 * @MigrateProcessPlugin(
 *   id = "find_alt_text"
 * )
 *
 * @code
 * field_image_field:
 *   plugin: find_alt_text
 *   source: fid
 *   field_tables:
 *     - node__field_image
 *     - node__field_second_image
 *   wysiwyg_field_tables:
 *     - node__body
 *     - node__formatted_text
 * @endcode
 */
class FindAltText extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $plugin) {
    $configuration += [
      'field_tables' => [],
      'wysiwyg_field_tables' => [],
    ];
    parent::__construct($configuration, $plugin_id, $plugin_definition, $plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
  
    // Get the source database.
    $source_db_key = \Drupal::config('migrate_plus.migration_group.localgov_migration')->get('shared_configuration')['source']['key'];
    $connection = Database::getConnection('default', $source_db_key);

    // Add a query for each table.
    $field_tables = $this->configuration['field_tables'];
    foreach ($field_tables as $table) {
      $parts = explode('__', $table);
      $entity = $parts[0];
      $field = $parts[1];
      $query = $connection->select($table);
      $query->addField($table, $field . '_alt');
      $query->condition($field . '_target_id', $value);
      $result = $query->execute();
      $alt_text = $result->fetchField();
      if (!empty($alt_text)) {
        break;
      }
    } 
    
    return $alt_text ?? NULL;
  }


}
