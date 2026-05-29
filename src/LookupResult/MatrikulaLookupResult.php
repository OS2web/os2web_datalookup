<?php

namespace Drupal\os2web_datalookup\LookupResult;

/**
 * Class MatrikulaLookupResult.
 *
 * Wrapper class for Matrikula object that easies
 * the matrikula property access.
 */
class MatrikulaLookupResult {

  /**
   * Owner licence code / ejerlavskode.
   *
   * @var string
   */
  protected string $ownerLicenseCode;

  /**
   * Ownership name / ejerlavsnavn.
   *
   * @var string
   */
  protected string $ownershipName;


  /**
   * Matrikula number / matrikelnummer.
   *
   * @var string
   */
  protected string $matrikulaNumber;

  /**
   * Returns owner license code.
   *
   * @param string $ownerLicenseCode
   *   Owner license code.
   */
  public function setOwnerLicenseCode(string $ownerLicenseCode): void {
    $this->ownerLicenseCode = $ownerLicenseCode;
  }

  /**
   * Returns owner license code.
   *
   * @return string
   *   Owners license code.
   */
  public function getOwnerLicenseCode(): string {
    return $this->ownerLicenseCode;
  }

  /**
   * Sets ownership name.
   *
   * @param string $ownershipName
   *   Ownership name.
   */
  public function setOwnershipName(string $ownershipName): void {
    $this->ownershipName = $ownershipName;
  }

  /**
   * Returns ownership name.
   *
   * @return string
   *   Ownership name.
   */
  public function getOwnershipName(): string {
    return $this->ownershipName;
  }

  /**
   * Sets matrikula number.
   *
   * @param string $matrikulaNumber
   *   Matrikula number.
   */
  public function setMatrikulaNumber(string $matrikulaNumber): void {
    $this->matrikulaNumber = $matrikulaNumber;
  }

  /**
   * Returns matrikula number.
   *
   * @return string
   *   Matrikula number.
   */
  public function getMatrikulaNumber(): string {
    return $this->matrikulaNumber;
  }

}
