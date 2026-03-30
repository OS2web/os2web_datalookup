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
 * Defines a plugin for DatafordelerCVR.
 *
 * @DataLookup(
 *   id = "datafordeler_cvr",
 *   label = @Translation("Datafordeler CVR"),
 *   group = "cvr_lookup"
 * )
 */
class DatafordelerCVR extends DataLookupBase implements DataLookupCompanyInterface {

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

    $form['test_cvr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test CVR nr.'),
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

    if (!empty($form_state->getValue('test_cvr'))) {
      $lookupResult = $this->lookup($form_state->getValue('test_cvr'));
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
      $msg = sprintf('Hent virksomhed med CVRNummer: %s', $param);
      $this->auditLogger->info('DataLookup', $msg);
      $response = $this->executeQuery($param);
      $result = json_decode((string) $response->getBody());
    }
    catch (GuzzleException $e) {
      $msg = sprintf('Hent virksomhed med CVRNummer (%s): %s', $param, $e->getMessage());
      $this->auditLogger->error('DataLookup', $msg);
      $result = $e->getMessage();
    }

    $cvrResult = new CompanyLookupResult();
    if ($result && isset($result->data->CVR_Virksomhed->nodes) && !empty($result->data->CVR_Virksomhed->nodes)) {
      $companyGraph = $result->data->CVR_Virksomhed->nodes[0]->id_CVR_CVREnhed_id_ref->nodes[0];

      $cvrResult->setSuccessful();
      $cvrResult->setCvr($param);

      if ($companyGraph->id_CVR_Navn_CVREnhedsId_ref) {
        $cvrResult->setName($companyGraph->id_CVR_Navn_CVREnhedsId_ref->vaerdi);
      }

      if ($companyGraph->id_CVR_Adressering_CVREnhedsId_ref) {
        $address = $companyGraph->id_CVR_Adressering_CVREnhedsId_ref->nodes[0];

        $cvrResult->setStreet($address->CVRAdresse_vejnavn ?? '');
        $cvrResult->setHouseNr($address->CVRAdresse_husnummerFra ?? '');
        $cvrResult->setFloor($address->CVRAdresse_etagebetegnelse ?? '');
        $cvrResult->setApartmentNr($address->CVRAdresse_doerbetegnelse ?? '');
        $cvrResult->setPostalCode($address->CVRAdresse_postnummer ?? '');
        $city = $address->CVRAdresse_postdistrikt ?? $cvrResult->getPostalCode();
        $cvrResult->setCity($city);
        $cvrResult->setMunicipalityCode($address->CVRAdresse_kommunekode ?? '');

        // Composing full address in one line.
        $address = $cvrResult->getStreet();
        if ($cvrResult->getHouseNr()) {
          $address .= ' ' . $cvrResult->getHouseNr();
        }
        if ($cvrResult->getFloor()) {
          $address .= ' ' . $cvrResult->getFloor();
        }
        if ($cvrResult->getApartmentNr()) {
          $address .= ' ' . $cvrResult->getApartmentNr();
        }
        if ($cvrResult->getPostalCode() && $cvrResult->getCity()) {
          $address .= ', ' . $cvrResult->getPostalCode() . ' ' . $cvrResult->getCity();
        }

        $cvrResult->setAddress($address);
      }
    }
    else {
      $cvrResult->setSuccessful(FALSE);
      if (is_string($result)) {
        $cvrResult->setErrorMessage($result);
      }
    }

    return $cvrResult;
  }

  /**
   * Executes the GraphQL lookup request for a specific CVR number.
   *
   * Builds the GraphQL payload and sends it to the configured Datafordeler
   * endpoint using the shared HTTP request flow from the parent class.
   *
   * @param string $cvr
   *   The CVR number to look up.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The raw HTTP response from the GraphQL endpoint.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function executeQuery($cvr): ResponseInterface {
    $this->httpClient = new Client();

    $query = <<<GRAPHQL
{ CVR_Virksomhed(first: 1, virkningstid: "2024-01-01T00:00:00Z", where: { CVRNummer: { eq: {$cvr} } }) {
    nodes {
      CVRNummer
      id_CVR_CVREnhed_id_ref(first: 1) {
        nodes {
          id_CVR_Navn_CVREnhedsId_ref { vaerdi }
          id_CVR_Adressering_CVREnhedsId_ref(first: 1, where: { AdresseringAnvendelse: { in: ["beliggenhedsadresse", "postadresse"] } }) {
            nodes {
              AdresseringAnvendelse
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
