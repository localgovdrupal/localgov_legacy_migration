<?php

namespace Drupal\localgov_legacy_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\geocoder\GeocoderInterface;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Extract the lat / lon from a legacy BHCC style addrrss field.
 *
 * @MigrateProcessPlugin(
 *   id = "legacy_address_to_lat_lon"
 * )
 */
class LegacyAddressToLatLon extends ProcessPluginBase implements ContainerFactoryPluginInterface  {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GeocoderInterface $geocoder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->geocoder = $geocoder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('geocoder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (isset($value['latitude']) and isset($value['longitude'])) {
      return [
        'lat' => $value['latitude'],
        'lng' => $value['longitude'],
      ];
    }
    if (!empty($value['postcode'])) {
      $result = $this->geocoder->geocode($value['postcode'], ['localgov_default_osm']);
      if ($result) {
        return [
          'lat' => $result->get(0)->getCoordinates()->getLatitude(),
          'lng' => $result->get(0)->getCoordinates()->getLongitude(),
        ];
      }
    }

    if (!empty($value['street']) || !empty($value['city'])) {
      $result = $this->geocoder->geocode(implode(' ', [$value['street'], $value['city']]) , ['localgov_default_osm']);
      if ($result) {
        return [
          'lat' => $result->get(0)->getCoordinates()->getLatitude(),
          'lng' => $result->get(0)->getCoordinates()->getLongitude(),
        ];
      }
    }

    return NULL;
  }

}
