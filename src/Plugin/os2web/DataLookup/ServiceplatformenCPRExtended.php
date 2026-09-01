<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\os2web_datalookup\LookupResult\CprLookupResult;

/**
 * Defines a plugin for ServiceplatformenCPRExtended.
 *
 * @DataLookup(
 *   id = "serviceplatformen_cpr_extended",
 *   label = @Translation("Serviceplatformen CPR - extended (SF1520)"),
 *   group = "cpr_lookup"
 * )
 */
class ServiceplatformenCPRExtended extends ServiceplatformenBase implements DataLookupCprInterface {

  /**
   * Status = DEAD.
   */
  const CPR_STATUS_DEAD = 90;

  /**
   * Denmark country code.
   */
  const DENMARK_COUNTRY_CODE = 5100;

  /**
   * Guardian other 1.
   */
  const GUARDIAN_OTHER_1 = 5;

  /**
   * Guardian other 2.
   */
  const GUARDIAN_OTHER_2 = 6;

  /**
   * Guardian mother 3.
   */
  const GUARDIAN_MOTHER = 3;

  /**
   * Guardian father 4.
   */
  const GUARDIAN_FATHER = 4;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return array_merge(parent::defaultConfiguration(), [
      'test_mode_fixed_cpr' => '',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['mode_fieldset']['test_mode_fixed_cpr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fixed test CPR'),
      '#default_value' => $this->configuration['test_mode_fixed_cpr'],
      '#description' => $this->t('Fixed CPR that will be used for all requests to the serviceplatformen instead of the provided CPR.'),
      '#states' => [
        // Show the element only when running in test mode.
        'visible' => [
          'input[name="mode_selector"]' => ['value' => 1],
        ],
      ],
    ];
    $form['test_cpr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test CPR nr.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    if (!empty($form_state->getValue('test_cpr'))) {
      $cpr = $form_state->getValue('test_cpr');
    }

    if (!empty($cpr)) {
      $cprResult = $this->lookup($cpr);
      $response = (array) $cprResult;

      \Drupal::messenger()->addMessage(
        Markup::create('<pre>' . print_r($response, 1) . '</pre>'),
        $cprResult->isSuccessful() ? MessengerInterface::TYPE_STATUS : MessengerInterface::TYPE_WARNING
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function lookup(string $cpr, $fetchChildren = TRUE, $allowCprTestModeReplace = TRUE): CprLookupResult {
    if ($this->configuration['mode_selector'] == 1 && $this->configuration['test_mode_fixed_cpr']) {
      if ($allowCprTestModeReplace) {
        $cpr = $this->configuration['test_mode_fixed_cpr'];
        \Drupal::messenger()->addMessage(
          $this->t("Test mode enabled, all CPR lookup requests are made against CPR: %cpr", ['%cpr' => $cpr]),
          MessengerInterface::TYPE_STATUS
        );
      }
    }

    $request = $this->prepareRequest();
    $request['PNR'] = str_replace('-', '', $cpr);

    $result = $this->query('PersonLookup', $request);

    $cprResult = new CprLookupResult();
    // If all goes well, we return an address array.
    if ($result['status']) {
      $cprResult->setSuccessful();

      $persondata = $result['persondata'];
      $cprResult->setCpr($persondata->personnummer);

      if ($persondata->status) {
        if ($persondata->status->status == self::CPR_STATUS_DEAD) {
          $cprResult->setAlive(FALSE);
        }
        else {
          $cprResult->setAlive(TRUE);
        }
      }

      if ($persondata->statsborgerskab) {
        $cprResult->setCitizenshipCountryCode($persondata->statsborgerskab->landekode);

        if ($persondata->statsborgerskab->landekode == self::DENMARK_COUNTRY_CODE) {
          $cprResult->setCitizen(TRUE);
        }
        else {
          $cprResult->setCitizen(FALSE);
        }

        $citizenshipDate = \DateTime::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $persondata->statsborgerskab->statsborgerskabDato->dato);
        $cprResult->setCitizenshipDate($citizenshipDate);
      }

      if ($persondata->navn) {
        $cprResult->setName($persondata->navn->personadresseringsnavn ?? '');
      }

      if (isset($persondata->foedselsdato)) {
        $birthDate = \DateTime::createFromFormat("Y-m-dP", $persondata->foedselsdato->dato);
        $cprResult->setBirthDate($birthDate);
      }

      if ($persondata->tilmeldtDigitalpost) {
        $cprResult->setDigitalPostSubscribed(TRUE);
      }

      if ($persondata->adressebeskyttelse) {
        $cprResult->setNameAddressProtected($persondata->adressebeskyttelse->beskyttet ?? FALSE);
      }

      $address = $result['adresse'];
      if ($address->aktuelAdresse) {
        $cprResult->setStreet($address->aktuelAdresse->vejnavn ?? '');
        $cprResult->setHouseNr(isset($address->aktuelAdresse->husnummer) ? ltrim($address->aktuelAdresse->husnummer, '0') : '');
        $cprResult->setFloor($address->aktuelAdresse->etage ?? '');
        $cprResult->setApartmentNr(isset($address->aktuelAdresse->sidedoer) ? ltrim($address->aktuelAdresse->sidedoer, '0') : '');
        $cprResult->setPostalCode($address->aktuelAdresse->postnummer ?? '');
        $cprResult->setCity($address->aktuelAdresse->postdistrikt ?? '');
        $cprResult->setMunicipalityCode($address->aktuelAdresse->kommunekode ?? '');

        // Composing full address in one line.
        $address = $cprResult->getStreet();
        if ($cprResult->getHouseNr()) {
          $address .= ' ' . $cprResult->getHouseNr();
        }
        if ($cprResult->getFloor()) {
          $address .= ' ' . $cprResult->getFloor();
        }
        if ($cprResult->getApartmentNr()) {
          $address .= ' ' . $cprResult->getApartmentNr();
        }
        if ($cprResult->getPostalCode() && $cprResult->getCity()) {
          $address .= ', ' . $cprResult->getPostalCode() . ' ' . $cprResult->getCity();
        }

        $cprResult->setAddress($address ?? '');
      }

      $relationship = $result['relationer'];

      // Setting children.
      $children = [];
      if ($fetchChildren && isset($relationship->barn)) {
        if (!is_array($relationship->barn)) {
          $relationship->barn = [$relationship->barn];
        }

        foreach ($relationship->barn as $relationshipChild) {
          $childCprResult = $this->lookup($relationshipChild->personnummer, FALSE, FALSE);

          if ($childCprResult->isSuccessful() && $childCprResult->hasGuardian($cpr)) {
            $child['name'] = !empty($childCprResult->getName()) ? $childCprResult->getName() : $childCprResult->getCpr();

            $child = [
              'cpr' => $relationshipChild->personnummer,
              'name' => $child['name'],
              'nameAddressProtected' => $childCprResult->isNameAddressProtected(),
            ];

            $children[] = $child;
          }
        }
      }
      $cprResult->setChildren($children);

      // Setting guardians.
      $guardians = [];

      if (isset($relationship->foraeldremyndighed)) {
        if (!(is_array($relationship->foraeldremyndighed))) {
          $relationship->foraeldremyndighed = [$relationship->foraeldremyndighed];
        }

        foreach ($relationship->foraeldremyndighed as $relationshipGuardian) {
          if ($relationshipGuardian->foraeldremyndighedtype == self::GUARDIAN_MOTHER) {
            $guardian = [
              'type' => self::GUARDIAN_MOTHER,
              'cpr' => $relationship->mor->personnummer ?? NULL,
            ];

            $guardians[] = $guardian;
          }
          elseif ($relationshipGuardian->foraeldremyndighedtype == self::GUARDIAN_FATHER) {
            $guardian = [
              'type' => self::GUARDIAN_FATHER,
              'cpr' => $relationship->far->personnummer ?? NULL,
            ];

            $guardians[] = $guardian;
          }
          elseif ($relationshipGuardian->foraeldremyndighedtype == self::GUARDIAN_OTHER_1 || $relationshipGuardian->foraeldremyndighedtype == self::GUARDIAN_OTHER_2) {
            $guardian = [
              'type' => $relationshipGuardian->foraeldremyndighedtype,
              'cpr' => $relationshipGuardian->relationPersonnummer,
            ];

            $guardians[] = $guardian;
          }
        }
      }
      $cprResult->setGuardians($guardians);

      // Leaving empty, no information in webservice.
      $cprResult->setCoName('');
    }
    else {
      $cprResult->setSuccessful(FALSE);
      $cprResult->setErrorMessage($result['error']);
    }

    return $cprResult;
  }

}
