<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\os2web_datalookup\LookupResult\CvrLookupResult;

/**
 * Defines a plugin for ServiceplatformenCVR.
 *
 * @DataLookup(
 *   id = "serviceplatformen_cvr",
 *   label = @Translation("Serviceplatformen CVR"),
 *   group = "cvr_lookup"
 * )
 */
class ServiceplatformenCVR extends ServiceplatformenBase implements DataLookupInterfaceCvr {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['test_cvr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test CVR nr.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!empty($form_state->getValue('test_cvr'))) {
      $response = $this->getInfo($form_state->getValue('test_cvr'));
      \Drupal::messenger()->addMessage(
        Markup::create('<pre>' . print_r($response, 1) . '</pre>'),
        $response['status'] ? MessengerInterface::TYPE_STATUS : MessengerInterface::TYPE_WARNING
      );
    }
  }

  /**
   * Implementation of getLegalUnit call.
   *
   * @param string $cvr
   *   Requested CVR.
   *
   * @return array
   *   [status] => TRUE/FALSE
   *   [cvr] => CVR code
   *   [company_name] => Name of the organization
   *   [company_street] => Street name
   *   [company_house_nr] => House nr
   *   [company_floor] => Floor nr
   *   [company_zipcode] => ZIP code
   *   [company_city] => City
   */
  public function getLegalUnit($cvr) {
    $request = $this->prepareRequest();
    $request['GetLegalUnitRequest'] = [
      'level' => 1,
      'UserId' => NULL,
      'Password' => NULL,
      'LegalUnitIdentifier' => $cvr,
    ];
    return $this->query('getLegalUnit', $request);
  }

  /**
   * Translates the fetch CVR information to a nice looking array.
   *
   * @param string $cvr
   *   Requested CVR.
   *
   * @return array
   *   [status] => TRUE/FALSE
   *   [cvr] => CVR code,
   *   [company_name] => Name of the organization,
   *   [company_street] => Street name,
   *   [company_house_nr] => House nr,
   *   [company_floor] => Floor nr,
   *   [company_zipcode] => ZIP code
   *   [company_city] => City,
   */
  public function getInfo($cvr) {
    $result = $this->getLegalUnit($cvr);
    if ($result['status']) {
      $legalUnit = (array) $result['GetLegalUnitResponse']->LegalUnit;
      return [
        'status' => TRUE,
        'cvr' => $legalUnit['LegalUnitIdentifier'],
        'company_name' => $legalUnit['LegalUnitName']->name,
        'company_street' => $legalUnit['AddressOfficial']->AddressPostalExtended->StreetName,
        'company_house_nr' => $legalUnit['AddressOfficial']->AddressPostalExtended->StreetBuildingIdentifier,
        'company_floor' => $legalUnit['AddressOfficial']->AddressPostalExtended->FloorIdentifier,
        'company_zipcode' => $legalUnit['AddressOfficial']->AddressPostalExtended->PostCodeIdentifier,
        'company_city' => $legalUnit['AddressOfficial']->AddressPostalExtended->DistrictName,
      ];
    }
    else {
      return $result;
    }
  }

  /**
   * @inheritDoc
   */
  public function lookup($cvr) {
    $result = $this->getInfo($cvr);

    $cvrResult = new CvrLookupResult();
    if ($result['status']) {
      $cvrResult->setSuccessful();
      $cvrResult->setCvr($cvr);

      $cvrResult->setName($result['company_name']);
      $cvrResult->setStreet($result['company_street']);
      $cvrResult->setHouseNr($result['company_house_nr']);
      $cvrResult->setFloor($result['company_floor']);
      $cvrResult->setPostalCode($result['company_zipcode']);

      $city = $result['company_zipcode'] . ' '  . $result['company_city'];
      $cvrResult->setCity($city);

      $address = $result['company_street'] . ' ' . $result['company_house_nr'] . ' ' . $result['company_floor'];
      $cvrResult->setAddress($address);
    }
    else {
      $cvrResult->setSuccessful(FALSE);
      $cvrResult->setErrorMessage($result['error']);
    }
  }

}
