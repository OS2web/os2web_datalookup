<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\key\KeyRepositoryInterface;
use Drupal\os2forms_dawa\Entity\DatafordelerMatrikula;
use Drupal\os2forms_dawa\Entity\DawaAddress;
use Drupal\os2web_audit\Service\Logger;
use Drupal\os2web_datalookup\LookupResult\AddressLookupResult;
use Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Defines a plugin for Datafordeler address lookup.
 *
 * @DataLookup(
 *   id = "datafordeler_address_lookup",
 *   label = @Translation("Datafordeler Address Lookup"),
 * )
 */
class DatafordelerAddressLookup extends DataLookupBase implements DatafordelerAddressLookupInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected ClientInterface $httpClient,
    Logger $auditLogger,
    KeyRepositoryInterface $keyRepository,
    FileSystem $fileSystem,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $auditLogger, $keyRepository, $fileSystem);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\os2web_audit\Service\Logger $auditLogger */
    $auditLogger = $container->get('os2web_audit.logger');
    /** @var \Drupal\key\KeyRepositoryInterface $keyRepository */
    $keyRepository = $container->get('key.repository');
    /** @var \Drupal\Core\File\FileSystem $fileSystem */
    $fileSystem = $container->get('file_system');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $auditLogger,
      $keyRepository,
      $fileSystem,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAddressMatches(ParameterBag $params, $fetchColumn = 'titel') : array {
    $token = $this->getConfiguration()['token'];
    $url = "https://adressevaelger.dk/adresser/soeg";

    $json = '';

    // Get autocomplete query.
    $q = $params->get('q') ?: '';
    // Adding limit by municipality limit, if present.
    $limitByMunicipality = $params->get('limit_by_municipality') ?: '';

    $query =[
      'token' => $token,
      'tekst' => $q
    ];

    if (!empty($limitByMunicipality)) {
      $query['kommunekode'] = $limitByMunicipality;
    }

    try {
      $json = $this->httpClient->request('GET', $url, [
        'query' => $query,
      ])->getBody();
    } catch (GuzzleException $e) {
      \Drupal::logger('os2web_datalookup')->warning('Request failed: @e', ['@e' => $e->getMessage()]);
    }

    $jsonDecoded = json_decode($json, TRUE);
    $addresses = [];
    if (is_array($jsonDecoded) && !empty($jsonDecoded['fund'])) {
      $addresses = $jsonDecoded['fund'];
    }

    if ($fetchColumn) {
      // Checking if remove_place_name is enabled.
      $removePlaceName = $params->get('remove_place_name') ?: '';
      if ($removePlaceName) {
        foreach ($addresses as &$entry) {
          $addressId = $entry['id'];

          $address = $this->fetchAddressLookupResult($addressId);
          if ($supplerendebynavn = $address->getSupplementaryCity()) {
            $entry['titel'] = preg_replace("/$supplerendebynavn,/", '', $entry['titel']);
          }
        }
      }

      $matches = array_column($addresses, $fetchColumn);
    }
    else {
      $matches = $addresses;
    }

    return $matches;
  }

  /**
   * {@inheritdoc}
   */
  public function getSingleAddress(ParameterBag $params) : AddressLookupResult  {
    $address = NULL;

    // Getting address_id.
    $matches = $this->getAddressMatches($params, NULL);
    if (!empty($matches)) {
      $addressSource = $matches[0];

      // Fetching address.
      $address = $this->fetchAddressLookupResult($addressSource['id']);
    }

    return $address;
  }

  /**
   * Returns single address from address API.
   *
   * @param string $id
   *   ID of the address.
   *
   * @return \Drupal\os2web_datalookup\LookupResult\AddressLookupResult
   *   The found address.
   */
  public function fetchAddressLookupResult($id) {
    $token = $this->getConfiguration()['token'];
    $url = "https://adressevaelger.dk/adresser/$id";

    $address = new AddressLookupResult();
    $address->setSuccessful(FALSE);

    $json = '';

    // Fetching address.
    try {
      $json = $this->httpClient->request('GET', $url, [
        'query' => [
          'token' => $token,
        ],
      ])->getBody();
    } catch (GuzzleException $e) {
      \Drupal::logger('os2web_datalookup')->warning('Request failed: @e', ['@e' => $e->getMessage()]);
    }

    $jsonDecoded = json_decode($json, TRUE);
    if (is_array($jsonDecoded) && !empty($jsonDecoded)) {
      if ($jsonDecoded['status'] == 'ok') {
        $address->setSuccessful();

        $address_raw = $jsonDecoded['adresse'];
        $address->setId($address_raw['id_lokalid']);
        $address->setAccessAddressId($address_raw['husnummer']['adgangspunkt']['id_lokalid']);
        $address->setFullAddress($address_raw['adressebetegnelse']);

        $address->setStreet($address_raw['husnummer']['vejnavn'] ?? '');
        $address->setHouseNr($address_raw['husnummer']['husnummertekst'] ?? '');
        $address->setFloor($address_raw['etagebetegnelse'] ?? '');
        $address->setApartmentNr($address_raw['doerbetegnelse'] ?? '');
        $address->setPostalCode($address_raw['husnummer']['postnummer']['postnr'] ?? '');
        $address->setCity($address_raw['husnummer']['postnummer']['navn'] ?? '');
        $address->setMunicipalityCode($address_raw['husnummer']['navngivenvejkommunedel']['kommune'] ?? '');
        $address->setSupplementaryCity($address_raw['husnummer']['supplerendebynavn']['navn'] ?? '');
      }
    }

    return $address;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'username' => '',
      'password' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token for service calls'),
      '#default_value' => $this->configuration['token'] ?? '',
      '#required' => TRUE,
      '#description' => $this->t('Token required for performing API requests'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $configuration['token'] = $form_state->getValue('token');
    $this->setConfiguration($configuration);
  }

}
