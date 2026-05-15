<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\os2web_datalookup\LookupResult\AddressLookupResult;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * DatafordelerDataLookupInterface plugin interface.
 *
 * Provides functions for getting the plugin configuration values.
 *
 * @ingroup plugin_api
 */
interface DatafordelerAddressLookupInterface extends DataLookupInterface {

  /**
   * Returns array of matches for 'os2forms_dawa_address' element autocomplete route.
   *
   * @param \Symfony\Component\HttpFoundation\ParameterBag $params
   *   The query params.
   * @param string $fetchColumn
   *   The name of the column to return, set to NULL to get all columns.
   *
   * @return array
   *   Array of matches.
   */
  public function getAddressMatches(ParameterBag $params, $fetchColumn = 'titel') : array;


  /**
   * Returns single address from address API.
   *
   * @param \Symfony\Component\HttpFoundation\ParameterBag $params
   *   The query params.
   *
   * @return \Drupal\os2web_datalookup\LookupResult\AddressLookupResult|NULL
   *   The found address.
   */
  public function getSingleAddress(ParameterBag $params) : ?AddressLookupResult;
}
