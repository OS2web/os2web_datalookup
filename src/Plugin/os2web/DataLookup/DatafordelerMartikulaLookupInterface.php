<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

/**
 * DatafordelerDataLookupInterface plugin interface.
 *
 * Provides functions for getting the plugin configuration values.
 *
 * @ingroup plugin_api
 */
interface DatafordelerMartikulaLookupInterface extends DataLookupInterface {

  /**
   * Returns list of ID for Matrikula / jordstykke related with this address.
   *
   * @param string $addressAccessId
   *   Address to make search against.
   *
   * @return string|null
   *   List if IDs.
   */
  public function getMatrikulaId(string $addressAccessId) : ?string;

  /**
   * Returns matrikula entries that is found byt this ID.
   *
   * @param string $matrikulaId
   *   Id to make search  against.
   *
   * @return array
   *   Matrikula entries list.
   */
  public function getMatrikulaEntries(string $matrikulaId) : array;

}
