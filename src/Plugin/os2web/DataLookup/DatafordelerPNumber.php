<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\os2web_datalookup\LookupResult\CompanyLookupResult;
use GuzzleHttp\Exception\ClientException;

/**
 * Defines a plugin for DatafordelerPNumber.
 *
 * @DataLookup(
 *   id = "datafordeler_pnumber",
 *   label = @Translation("Datafordeler P-Number"),
 *   group = "pnumber_lookup"
 * )
 */
class DatafordelerPNumber extends DatafordelerBase implements DataLookupCompanyInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'webserviceurl_live' => 'https://s5-certservices.datafordeler.dk/CVR/HentCVRData/1/REST/',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

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
    parent::submitConfigurationForm($form, $form_state);

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
      $msg = sprintf('Hent produktionsenhed med PNummer: %s', $param);
      $this->auditLogger->info('DataLookup', $msg);
      $response = $this->httpClient->get('hentProduktionsenhedMedPNummer', ['query' => ['ppNummer' => $param]]);
      $result = json_decode((string) $response->getBody());
    }
    catch (ClientException $e) {
      $msg = sprintf('Hent produktionsenhed med PNummer (%s): %s', $param, $e->getMessage());
      $this->auditLogger->error('DataLookup', $msg);
      $result = $e->getMessage();
    }

    $cvrResult = new CompanyLookupResult();
    if ($result && isset($result->produktionsenhed) && !empty((array) $result->produktionsenhed)) {
      $cvrResult->setSuccessful();
      $cvrResult->setCvr($result->produktionsenhed->tilknyttetVirksomhedsCVRNummer ?? '');

      if ($result->produktionsenhedsnavn) {
        $cvrResult->setName($result->produktionsenhedsnavn->vaerdi);
      }

      if ($result->beliggenhedsadresse) {
        $address = $result->beliggenhedsadresse;

        $cvrResult->setStreet($address->CVRAdresse_vejnavn ?? '');
        $cvrResult->setHouseNr($address->CVRAdresse_husnummerFra ?? '');
        $cvrResult->setFloor($address->CVRAdresse_etagebetegnelse ?? '');
        $cvrResult->setApartmentNr($address->CVRAdresse_doerbetegnelse ?? '');
        $cvrResult->setPostalCode($address->CVRAdresse_postnummer ?? '');
        $city = implode(' ', array_filter([
          $address->CVRAdresse_postdistrikt ?? NULL,
          $cvrResult->getPostalCode() ?? NULL,
        ]));
        $cvrResult->setCity($city);
        $cvrResult->setMunicipalityCode($address->CVRAdresse_kommunekode ?? '');
        $address = $cvrResult->getStreet() . ' ' . $cvrResult->getHouseNr() . ' ' . $cvrResult->getFloor() . $cvrResult->getApartmentNr();
        $cvrResult->setAddress(trim($address));
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

}
