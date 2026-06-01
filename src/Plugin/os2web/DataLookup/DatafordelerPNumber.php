<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\os2web_datalookup\LookupResult\CompanyLookupResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Defines a plugin for DatafordelerPNumber.
 *
 * @DataLookup(
 *   id = "datafordeler_pnumber",
 *   label = @Translation("Datafordeler P-Number"),
 *   group = "pnumber_lookup"
 * )
 */
class DatafordelerPNumber extends DataLookupBase implements DataLookupCompanyInterface {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected Client $httpClient;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'webserviceurl_live' => 'https://graphql.datafordeler.dk/flexibleCurrent/v1',
      'api_key' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['webserviceurl_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webservice URL (LIVE)'),
      '#description' => $this->t('Live URL against which to make the request, e.g. https://graphql.datafordeler.dk/flexibleCurrent/v1'),
      '#default_value' => $this->configuration['webserviceurl_live'],
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $this->configuration['api_key'],
    ];

    $form['test_pnumber'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test P-Number'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $keys = array_keys($this->defaultConfiguration());
    $configuration = $this->getConfiguration();
    foreach ($keys as $key) {
      $configuration[$key] = $form_state->getValue($key);
    }
    $this->setConfiguration($configuration);

    if (!empty($form_state->getValue('test_pnumber'))) {
      $lookupResult = $this->lookup($form_state->getValue('test_pnumber'));
      $response = (array) $lookupResult;

      \Drupal::messenger()->addMessage(
        Markup::create('<pre>' . print_r($response, 1) . '</pre>'),
        $lookupResult->isSuccessful() ? MessengerInterface::TYPE_STATUS : MessengerInterface::TYPE_WARNING
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lookup(string $param): CompanyLookupResult {
    try {
      if (!preg_match('/^\d{10}$/', $param)) {
        throw new \InvalidArgumentException('P-number must be exactly 10 digits.');
      }

      $msg = sprintf('Hent virksomhed med PNummer: %s', $param);
      $this->auditLogger->info('DataLookup', $msg);
      $response = $this->executeQuery($param);
      $result = json_decode((string) $response->getBody());
    }
    catch (GuzzleException | \InvalidArgumentException $e) {
      $msg = sprintf('Hent virksomhed med PNummer (%s): %s', $param, $e->getMessage());
      $this->auditLogger->error('DataLookup', $msg);
      $result = $e->getMessage();
    }

    $companyLookupResult = new CompanyLookupResult();
    if ($result && isset($result->data->CVR_Produktionsenhed->nodes) && !empty($result->data->CVR_Produktionsenhed->nodes)) {
      $companyGraph = $result->data->CVR_Produktionsenhed->nodes[0]->id_CVR_CVREnhed_id_ref->nodes[0];

      if ($result->data->CVR_Produktionsenhed->nodes[0]->tilknyttetVirksomhedsCVRNummer) {
        $companyLookupResult->setCvr($result->data->CVR_Produktionsenhed->nodes[0]->tilknyttetVirksomhedsCVRNummer);
      }

      $companyLookupResult->setSuccessful();
      $companyLookupResult->setPnumber($param);

      if ($companyGraph->id_CVR_Navn_CVREnhedsId_ref) {
        $companyLookupResult->setName($companyGraph->id_CVR_Navn_CVREnhedsId_ref->vaerdi);
      }

      if ($companyGraph->id_CVR_Adressering_CVREnhedsId_ref) {
        $address = $companyGraph->id_CVR_Adressering_CVREnhedsId_ref->nodes[0];

        $companyLookupResult->setStreet($address->CVRAdresse_vejnavn ?? '');
        $companyLookupResult->setHouseNr($address->CVRAdresse_husnummerFra ?? '');
        $companyLookupResult->setFloor($address->CVRAdresse_etagebetegnelse ?? '');
        $companyLookupResult->setApartmentNr($address->CVRAdresse_doerbetegnelse ?? '');
        $companyLookupResult->setPostalCode($address->CVRAdresse_postnummer ?? '');
        $city = $address->CVRAdresse_postdistrikt ?? $companyLookupResult->getPostalCode();
        $companyLookupResult->setCity($city);
        $companyLookupResult->setMunicipalityCode($address->CVRAdresse_kommunekode ?? '');
      }
    }
    else {
      $companyLookupResult->setSuccessful(FALSE);
      if (is_string($result)) {
        $companyLookupResult->setErrorMessage($result);
      }
    }

    return $companyLookupResult;
  }

  /**
   * Executes the GraphQL lookup request for a specific P-number number.
   *
   * Builds the GraphQL payload and sends it to the configured Datafordeler
   * endpoint using the shared HTTP request flow from the parent class.
   *
   * @param string $pNumber
   *   The P-Number number to look up.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The raw HTTP response from the GraphQL endpoint.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function executeQuery($pNumber): ResponseInterface {
    $this->httpClient = new Client();

    // Setting date to TODAY 00:00:00, so that we are always getting up-to-date
    // information.
    $virkningstid = (new \DateTimeImmutable('today', new \DateTimeZone('UTC')))
      ->format('Y-m-d\T00:00:00\Z');

    $query = <<<GRAPHQL
{ CVR_Produktionsenhed(first: 1, virkningstid: "{$virkningstid}", where: { pNummer: { eq: {$pNumber} } }) {
    nodes {
      pNummer
      tilknyttetVirksomhedsCVRNummer
      id_CVR_CVREnhed_id_ref(first: 1) {
        nodes {
          id_CVR_Navn_CVREnhedsId_ref { vaerdi }
          id_CVR_Adressering_CVREnhedsId_ref(first: 1, where: { AdresseringAnvendelse: { eq: "beliggenhedsadresse" } }) {
            nodes {
              CVRAdresse_vejnavn
              CVRAdresse_husnummerFra
              CVRAdresse_etagebetegnelse
              CVRAdresse_doerbetegnelse
              CVRAdresse_postnummer
              CVRAdresse_postdistrikt
              CVRAdresse_kommunekode
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

    $webserviceUrl = $this->configuration['webserviceurl_live'];

    return $this->httpClient->post($webserviceUrl, [
      'query' => [
        'apiKey' => $this->configuration['api_key'],
      ],
      'json' => [
        'query' => $query,
      ],
    ]);
  }

}
