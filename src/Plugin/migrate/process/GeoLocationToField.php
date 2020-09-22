<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\geofield\Plugin\migrate\process\GeofieldLatLon;

/**
 * Convert a Geolocation data to Geofield data.
 *
 * @MigrateProcessPlugin(
 *   id = "geo_location_to_field"
 * )
 */
class GeoLocationToField extends GeofieldLatLon {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value['lat']) and isset($value['lng'])) {
      $lat = $value['lat'];
      $lon = $value['lng'];
      return $this->wktGenerator->WktBuildPoint([$lon, $lat]);
    }

    return NULL;
  }

}
