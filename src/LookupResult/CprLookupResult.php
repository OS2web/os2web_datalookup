<?php

namespace Drupal\os2web_datalookup\LookupResult;

/**
 * Representation or value object for the result of a CPR lookup.
 */
class CprLookupResult {

  const CPR = 'cpr';
  const NAME = 'name';
  const STREET = 'street';
  const HOUSE_NR = 'houseNr';
  const FLOOR = 'floor';
  const APARTMENT_NR = 'apartmentNr';
  const POSTAL_CODE = 'postalCode';
  const CITY = 'city';
  const MUNICIPALITY_CODE = 'municipalityCode';
  const ADDRESS = 'address';
  const CO_NAME = 'coName';

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
   * CPR number.
   *
   * @var string
   */
  protected string $cpr;

  /**
   * Name of the person.
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
   * Floor number the person.
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
   * CO Name of the person.
   *
   * @var string
   */
  protected string $coName;

  /**
   * Is name/address protected of the person.
   *
   * @var bool
   */
  protected bool $nameAddressProtected = FALSE;

  /**
   * Array of children.
   *
   * @var array
   */
  protected array $children = [];

  /**
   * Array of guardians.
   *
   * @var array
   */
  protected array $guardians = [];

  /**
   * Is subscribed to digital post.
   *
   * @var bool
   */
  protected bool $digitalPostSubscribed = FALSE;

  /**
   * Is alive.
   *
   * @var bool
   */
  protected bool $alive = TRUE;

  /**
   * Is Danish citizen.
   *
   * @var bool
   */
  protected bool $citizen = TRUE;

  /**
   * Citizenship date obtained.
   *
   * @var \DateTime
   */
  protected \DateTime $citizenshipDate;

  /**
   * Citizenship country code.
   *
   * @var int
   */
  protected $citizenshipCountryCode;

  /**
   * Date of birth.
   *
   * @var \DateTime
   */
  protected \DateTime $birthDate;

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
   * Get CPR number.
   *
   * @return string
   *   The number.
   */
  public function getCpr(): string {
    return $this->cpr;
  }

  /**
   * Set CPR number.
   *
   * @param string $cpr
   *   The number.
   */
  public function setCpr(string $cpr): void {
    $this->cpr = $cpr;
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
   *   The name.
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
   *   The address.
   */
  public function setAddress(string $address): void {
    $this->address = $address;
  }

  /**
   * Get CO name.
   *
   * @return string
   *   The CO name.
   */
  public function getCoName(): string {
    return $this->coName;
  }

  /**
   * Set CO name.
   *
   * @param string $coName
   *   The CO name to set.
   */
  public function setCoName(string $coName): void {
    $this->coName = $coName;
  }

  /**
   * Check if the address is protected.
   *
   * @return bool
   *   TRUE if it is else FALSE.
   */
  public function isNameAddressProtected(): bool {
    return $this->nameAddressProtected;
  }

  /**
   * Set address protected state.
   *
   * @param bool $nameAddressProtected
   *   TRUE if protected else FALSE.
   */
  public function setNameAddressProtected(bool $nameAddressProtected): void {
    $this->nameAddressProtected = $nameAddressProtected;
  }

  /**
   * Returns the children.
   *
   * @return array
   *   Children array as
   *   [
   *     0 => [
   *       'cpr' => xxxxx,
   *       'name' => full name,
   *     ],
   *     ...
   *   ].
   */
  public function getChildren(): array {
    return $this->children;
  }

  /**
   * Sets the children.
   *
   * @param array $children
   *   Children array as
   *   [
   *     0 => [
   *       'cpr' => xxxxx,
   *       'name' => full name,
   *       'nameAddressProtected' => TRUE/FALSE
   *     ],
   *     ...
   *   ].
   */
  public function setChildren(array $children): void {
    $this->children = $children;
  }

  /**
   * Returns the guardians.
   *
   * @return array
   *   Guardians array as
   *   [
   *     0 => [
   *       'cpr' => xxxxx,
   *       'type' => type of guardian,
   *       'nameAddressProtected' => TRUE/FALSE
   *     ],
   *     ...
   *   ].
   */
  public function getGuardians(): array {
    return $this->guardians;
  }

  /**
   * Sets the guardians.
   *
   * @param array $guardians
   *   Guardians array as
   *   [
   *     0 => [
   *       'cpr' => xxxxx,
   *       'type' => 1|2|3|4,
   *     ],
   *     ...
   *   ].
   */
  public function setGuardians(array $guardians): void {
    $this->guardians = $guardians;
  }

  /**
   * Set digital post subscriber state.
   *
   * @param bool $digitalPostSubscribed
   *   The state.
   */
  public function setDigitalPostSubscribed(bool $digitalPostSubscribed): void {
    $this->digitalPostSubscribed = $digitalPostSubscribed;
  }

  /**
   * Check the state of digital post subscription.
   *
   * @return bool
   *   TRUE if subscriber else FALSE.
   */
  public function isDigitalPostSubscribed(): bool {
    return $this->digitalPostSubscribed;
  }

  /**
   * Check if citizen is alive.
   *
   * @return bool
   *   The living state.
   */
  public function isAlive(): bool {
    return $this->alive;
  }

  /**
   * Set is alive.
   *
   * @param bool $alive
   *   TRUE if alive else FALSE.
   */
  public function setAlive(bool $alive): void {
    $this->alive = $alive;
  }

  /**
   * Check if this is a citizen.
   *
   * @return bool
   *   TRUE if it is else FALSE.
   */
  public function isCitizen(): bool {
    return $this->citizen;
  }

  /**
   * Set citizen.
   *
   * @param bool $citizen
   *   The citizen (TRUE or FALSE).
   */
  public function setCitizen(bool $citizen): void {
    $this->citizen = $citizen;
  }

  /**
   * Get citizenship date.
   *
   * @return \DateTime
   *   The date.
   */
  public function getCitizenshipDate(): \DateTime {
    return $this->citizenshipDate;
  }

  /**
   * Set citizenship date.
   *
   * @param \DateTime $citizenshipDate
   *   The date.
   */
  public function setCitizenshipDate(\DateTime $citizenshipDate): void {
    $this->citizenshipDate = $citizenshipDate;
  }

  /**
   * Get citizenship country code.
   *
   * @return int
   *   The citizenship country code.
   */
  public function getCitizenshipCountryCode(): int {
    return $this->citizenshipCountryCode;
  }

  /**
   * Set citizenship country code.
   *
   * @param int $citizenshipCountryCode
   *   The citizenship country code.
   */
  public function setCitizenshipCountryCode(int $citizenshipCountryCode): void {
    $this->citizenshipCountryCode = $citizenshipCountryCode;
  }

  /**
   * Get birthdate.
   *
   * @return \DateTime
   *   The date.
   */
  public function getBirthDate(): \DateTime {
    return $this->birthDate;
  }

  /**
   * Set birthdate.
   *
   * @param \DateTime $birthDate
   *   The date.
   */
  public function setBirthDate(\DateTime $birthDate): void {
    $this->birthDate = $birthDate;
  }

  /**
   * Checks if the provided CPR is in list of guardians.
   *
   * @param string $cpr
   *   Cpr number.
   *
   * @return bool
   *   TRUE if the person has provided CPR as guardian, FALSE otherwise.
   */
  public function hasGuardian(string $cpr): bool {
    foreach ($this->guardians as $guardian) {
      if ($guardian['cpr'] == $cpr) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Returns the value of the provided field.
   *
   * @param string $field
   *   Field name.
   *
   * @return mixed
   *   The value of the field or the empty string.
   */
  public function getFieldValue(string $field): mixed {
    if (property_exists($this, $field) && isset($this->{$field})) {
      return $this->{$field};
    }

    return '';
  }

}
