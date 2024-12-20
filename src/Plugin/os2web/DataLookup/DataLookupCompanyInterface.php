<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\os2web_datalookup\LookupResult\CompanyLookupResult;

/**
 * DataLookupInterfaceCompany plugin interface.
 *
 * This interface provides some simple tools for code receiving a plugin to
 * interact with the plugin system.
 *
 * @ingroup plugin_api
 */
interface DataLookupCompanyInterface extends DataLookupInterface {

  /**
   * Performs lookup for the provided param.
   *
   * @param string $param
   *   The param to query for.
   *
   * @return \Drupal\os2web_datalookup\LookupResult\CompanyLookupResult
   *   The company lookup Result.
   */
  public function lookup(string $param): CompanyLookupResult;

}
