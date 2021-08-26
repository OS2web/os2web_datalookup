<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;

/**
 * Defines a plugin for ServiceplatformenPNumber.
 *
 * @DataLookup(
 *   id = "serviceplatformen_p_number",
 *   label = @Translation("Serviceplatformen P-number"),
 * )
 */
class ServiceplatformenPNumber extends ServiceplatformenBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['test_p_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test P-number'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!empty($form_state->getValue('test_p_number'))) {
      $response = $this->getInfo($form_state->getValue('test_p_number'));
      \Drupal::messenger()->addMessage(
        Markup::create('<pre>' . print_r($response, 1) . '</pre>'),
        $response['status'] ? MessengerInterface::TYPE_STATUS : MessengerInterface::TYPE_WARNING
      );
    }
  }

  /**
   * Implementation of getLegalUnit call.
   *
   * @param string $pNumber
   *   Requested P-Number.
   *
   * @return array
   *   [status] => TRUE/FALSE
   *   [cvr] => CVR code
   *   [p_number] => P-number
   *   [company_name] => Name of the organization
   *   [company_street] => Street name
   *   [company_house_nr] => House nr
   *   [company_floor] => Floor nr
   *   [company_zipcode] => ZIP code
   *   [company_city] => City
   */
  public function getProductionUnit($pNumber) {
    $request = $this->prepareRequest();
    $request['GetProductionUnitRequest'] = [
      'level' => 1,
      'UserId' => NULL,
      'Password' => NULL,
      'ProductionUnitIdentifier' => $pNumber,
    ];
    return $this->query('getProductionUnit', $request);
  }

  /**
   * Translates the fetch P-number information to a nice looking array.
   *
   * @param string $pNumber
   *   Requested P-number.
   *
   * @return array
   *   [status] => TRUE/FALSE
   *   [cvr] => CVR code,
   *   [p_number] => CVR code,
   *   [company_name] => Name of the organization,
   *   [company_street] => Street name,
   *   [company_house_nr] => House nr,
   *   [company_floor] => Floor nr,
   *   [company_zipcode] => ZIP code
   *   [company_city] => City,
   */
  public function getInfo($pNumber) {
    $result = $this->getProductionUnit($pNumber);
    if ($result['status']) {
      $productionUnit = (array) $result['GetProductionUnitResponse']->ProductionUnit;
      return [
        'status' => TRUE,
        'cvr' => $productionUnit['LegalUnitAffiliation']->LegalUnitIdentifier,
        'p_number' => $productionUnit['ProductionUnitIdentifier'],
        'company_name' => $productionUnit['ProductionUnitName']->name,
        'company_street' => $productionUnit['AddressLocation']->AddressPostalExtended->StreetName,
        'company_house_nr' => $productionUnit['AddressLocation']->AddressPostalExtended->StreetBuildingIdentifier,
        'company_floor' => $productionUnit['AddressLocation']->AddressPostalExtended->FloorIdentifier,
        'company_zipcode' => $productionUnit['AddressLocation']->AddressPostalExtended->PostCodeIdentifier,
        'company_city' => $productionUnit['AddressLocation']->AddressPostalExtended->DistrictName,
      ];
    }
    else {
      return $result;
    }
  }

}
