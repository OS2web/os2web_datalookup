<?php

namespace Drupal\os2web_datalookup\LookupResult;

/**
 * Representation or value object for the result of a CPR lookup.
 */
class AddressLookupResult {

  /**
   * Is request successful.
   *
   * @var bool
   */
  protected bool $successful = FALSE;

  /**
   * Status of the request.
   *
   * @var string
   */
  protected string $errorMessage;

  /**
   * ID of the address number.
   *
   * @var string
   */
  protected string $id;

  /**
   * Address house ID.
   *
   * @var string
   */
  protected $houseId;

  /**
   * Name of the person.
   *
   * @var string
   */
  protected string $fullAddress;

  /**
   * Street of the person.
   *
   * @var string
   */
  protected string $street;

  /**
   * Street house number.
   *
   * @var string
   */
  protected string $houseNr;

  /**
   * Floor number.
   *
   * @var string
   */
  protected string $floor;

  /**
   * Apartment number.
   *
   * @var string
   */
  protected string $apartmentNr;

  /**
   * Postal code.
   *
   * @var string
   */
  protected string $postalCode;

  /**
   * City.
   *
   * @var string
   */
  protected string $city;

  /**
   * Municipality code.
   *
   * @var string
   */
  protected string $municipalityCode;

  /**
   * Supplementary city.
   *
   * @var string
   */
  protected string $supplementaryCity;

  /**
   * Check successfulness state.
   *
   * @return bool
   *   TRUE on success else FALSE.
   */
  public function isSuccessful(): bool {
    return $this->successful;
  }

  /**
   * Set state of successfulness.
   *
   * @param bool $successful
   *   The state.
   */
  public function setSuccessful(bool $successful = TRUE): void {
    $this->successful = $successful;
  }

  /**
   * Get an error message.
   *
   * @return string
   *   The message.
   */
  public function getErrorMessage(): string {
    return $this->errorMessage;
  }

  /**
   * Set error message.
   *
   * @param string $errorMessage
   *   The message.
   */
  public function setErrorMessage(string $errorMessage): void {
    $this->errorMessage = $errorMessage;
  }

  /**
   * Get ID.
   *
   * @return string
   *   The ID number.
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Set ID.
   *
   * @param string $id
   *   ID number.
   */
  public function setId(string $id): void {
    $this->id = $id;
  }

  /**
   * Get house ID.
   *
   * @return string
   *   Access id number.
   */
  public function getHouseId(): string {
    return $this->houseId;
  }

  /**
   * Sets house ID.
   *
   * @param string $houseId
   *   Access id number.
   */
  public function setHouseId(string $houseId): void {
    $this->houseId = $houseId;
  }

  /**
   * Get full address.
   *
   * @return string
   *   The full address.
   */
  public function getFullAddress(): string {
    return $this->fullAddress;
  }

  /**
   * Set full address.
   *
   * @param string $fullAddress
   *   The full address.
   */
  public function setFullAddress(string $fullAddress): void {
    $this->fullAddress = $fullAddress;
  }

  /**
   * Get street.
   *
   * @return string
   *   The street.
   */
  public function getStreet(): string {
    return $this->street;
  }

  /**
   * Set street.
   *
   * @param string $street
   *   The street.
   */
  public function setStreet(string $street): void {
    $this->street = $street;
  }

  /**
   * Get house number.
   *
   * @return string
   *   The number.
   */
  public function getHouseNr(): string {
    return $this->houseNr;
  }

  /**
   * Set house number.
   *
   * @param string $houseNr
   *   The number.
   */
  public function setHouseNr(string $houseNr): void {
    $this->houseNr = $houseNr;
  }

  /**
   * Get floor.
   *
   * @return string
   *   The floor.
   */
  public function getFloor(): string {
    return $this->floor;
  }

  /**
   * Set floor.
   *
   * @param string $floor
   *   The floor.
   */
  public function setFloor(string $floor): void {
    $this->floor = $floor;
  }

  /**
   * Get apartment number.
   *
   * @return string
   *   The number.
   */
  public function getApartmentNr(): string {
    return $this->apartmentNr;
  }

  /**
   * Set apartment number.
   *
   * @param string $apartmentNr
   *   The number.
   */
  public function setApartmentNr(string $apartmentNr): void {
    $this->apartmentNr = $apartmentNr;
  }

  /**
   * Get postal code.
   *
   * @return string
   *   The code.
   */
  public function getPostalCode(): string {
    return $this->postalCode;
  }

  /**
   * Set postal code.
   *
   * @param string $postalCode
   *   The code.
   */
  public function setPostalCode(string $postalCode): void {
    $this->postalCode = $postalCode;
  }

  /**
   * Get city name.
   *
   * @return string
   *   The city name.
   */
  public function getCity(): string {
    return $this->city;
  }

  /**
   * Set city.
   *
   * @param string $city
   *   The city name.
   */
  public function setCity(string $city): void {
    $this->city = $city;
  }

  /**
   * Get municipality code.
   *
   * @return string
   *   The code.
   */
  public function getMunicipalityCode(): string {
    return $this->municipalityCode;
  }

  /**
   * Set municipality code.
   *
   * @param string $municipalityCode
   *   The municipality code.
   */
  public function setMunicipalityCode(string $municipalityCode): void {
    $this->municipalityCode = $municipalityCode;
  }

  /**
   * Set supplementary locality / village name.
   *
   * @param string $supplementaryCity
   *   Supplementary city name.
   */
  public function setSupplementaryCity(string $supplementaryCity): void {
    $this->supplementaryCity = $supplementaryCity;
  }

  /**
   * Get supplementary locality / village name.
   *
   * @return string
   *   Supplementary city name.
   */
  public function getSupplementaryCity(): string {
    return $this->supplementaryCity;
  }

}
