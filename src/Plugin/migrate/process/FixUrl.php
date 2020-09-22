<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Extract and fix URLs from a text field.
 *
 * @MigrateProcessPlugin(
 *   id = "fix_url"
 * )
 *
 * @code
 * localgov_directory_website/uri:
 *   plugin: fix_url
 *   source: field_website_plain
 * @endcode
 */
class FixUrl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value['value'])) {
      $url = $value['value'];

      // This is very crude, but will ensure $url passes Drupal URL validation.
      if (substr($url, 0, 1) === '/') {
        $url = \Drupal::request()->getSchemeAndHttpHost() . $url;
      }
      elseif (!preg_match('/^(http|https):\/\//i', $url)) {
        $url = 'http://' . $url;
      }

      return ['uri' => $url];
    }
    return NULL;
  }

}
