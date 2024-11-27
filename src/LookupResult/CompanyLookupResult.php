<?php

namespace Drupal\os2web_datalookup\LookupResult;

/**
 * Representation or value object for the result of a company lookup.
 */
class CompanyLookupResult {

  const CVR = 'cvr';
  const NAME = 'name';
  const STREET = 'street';
  const HOUSE_NR = 'houseNr';
  const FLOOR = 'floor';
  const APARTMENT_NR = 'apartmentNr';
  const POSTAL_CODE = 'postalCode';
  const CITY = 'city';
  const MUNICIPALITY_CODE = 'municipalityCode';
  const ADDRESS = 'address';

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
   * The CVR number.
   *
   * @var string
   */
  protected string $cvr;

  /**
   * Company name.
   *
   * @var string
   */
  protected string $name;

  /**
   * Street of the person.
   *
   * @var string
   */
  protected string $street;

  /**
   * Street house number of the person.
   *
   * @var string
   */
  protected string $houseNr;

  /**
   * Floor number of the person.
   *
   * @var string
   */
  protected string $floor;

  /**
   * Apartment number of the person.
   *
   * @var string
   */
  protected string $apartmentNr;

  /**
   * Postal code of the person.
   *
   * @var string
   */
  protected string $postalCode;

  /**
   * City of the person.
   *
   * @var string
   */
  protected string $city;

  /**
   * Municipality code of the person.
   *
   * @var string
   */
  protected string $municipalityCode;

  /**
   * Address of the person.
   *
   * @var string
   */
  protected string $address;

  /**
   * Check the state of successful.
   *
   * @return bool
   *   TRUE if successfully else FALSE.
   */
  public function isSuccessful(): bool {
    return $this->successful;
  }

  /**
   * The state of successful.
   *
   * @param bool $successful
   *   The state.
   */
  public function setSuccessful(bool $successful = TRUE): void {
    $this->successful = $successful;
  }

  /**
   * Get error message.
   *
   * @return string
   *   The error message.
   */
  public function getErrorMessage(): string {
    return $this->errorMessage;
  }

  /**
   * Set error message.
   *
   * @param string $errorMessage
   *   The error message.
   */
  public function setErrorMessage(string $errorMessage): void {
    $this->errorMessage = $errorMessage;
  }

  /**
   * Get CVR number.
   *
   * @return string
   *   The number.
   */
  public function getCvr(): string {
    return $this->cvr;
  }

  /**
   * Set CVR number.
   *
   * @param string $cpr
   *   The number.
   */
  public function setCvr(string $cpr): void {
    $this->cvr = $cpr;
  }

  /**
   * Get name.
   *
   * @return string
   *   The name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Set name.
   *
   * @param string $name
   *   The name.
   */
  public function setName(string $name): void {
    $this->name = $name;
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
   * Get a floor.
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
   * Get city.
   *
   * @return string
   *   The city.
   */
  public function getCity(): string {
    return $this->city;
  }

  /**
   * Set city.
   *
   * @param string $city
   *   The city.
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
   *   The code.
   */
  public function setMunicipalityCode(string $municipalityCode): void {
    $this->municipalityCode = $municipalityCode;
  }

  /**
   * Get address.
   *
   * @return string
   *   The address.
   */
  public function getAddress(): string {
    return $this->address;
  }

  /**
   * Set address.
   *
   * @param string $address
   *   The address to set.
   */
  public function setAddress(string $address): void {
    $this->address = $address;
  }

  /**
   * Returns the value of the provided field.
   *
   * @param string $field
   *   Field name.
   *
   * @return mixed
   *   The field value or the empty string if the field does not exist.
   */
  public function getFieldValue(string $field): mixed {
    if (property_exists($this, $field) && isset($this->{$field})) {
      return $this->{$field};
    }

    return '';
  }

}
