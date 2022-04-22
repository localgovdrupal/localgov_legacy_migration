<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\geofield\Plugin\migrate\process\GeofieldLatLon;

/**
 * Fix the order of Lat / Lon pairs.
 *
 * @MigrateProcessPlugin(
 *   id = "fix_lat_lon_order"
 * )
 */
class FixLatLonOrder extends GeofieldLatLon {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value['lat']) and isset($value['lng'])) {

      // Because some editors put in the wrong order for the lat / lon pair,
      // This will reverse if lat is smaller than lon.
      // Assume lat should be 50.* and lon -0.* because in Brighton / Hove.
      $lat = $value['lat'];
      $lon = $value['lng'];
      if ($lat < $lon) {
        $value['lat'] = $lon;
        $value['lng'] = $lat;
      }
    }

    return $value;
  }

}
