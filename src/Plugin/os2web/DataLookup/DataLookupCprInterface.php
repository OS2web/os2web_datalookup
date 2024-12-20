<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\os2web_datalookup\LookupResult\CprLookupResult;

/**
 * Data lookup CPR interface.
 */
interface DataLookupCprInterface extends DataLookupInterface {

  /**
   * Performs lookup for the provided CPR.
   *
   * @param string $cpr
   *   The CPR number to query for.
   *
   * @return \Drupal\os2web_datalookup\LookupResult\CprLookupResult
   *   The CPR lookup Result.
   */
  public function lookup(string $cpr): CprLookupResult;

}
