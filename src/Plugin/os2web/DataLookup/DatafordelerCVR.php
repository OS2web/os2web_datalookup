<?php
namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a plugin for DatafordelerCVR.
 *
 * @DataLookup(
 *   id = "datafordeler_cvr",
 *   label = @Translation("Datafordeler CVR"),
 * )
 */
class DatafordelerCVR extends DataLookupBase implements DataLookupInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'service_status' => null,
      'service_user' => null,
      'service_pass' => null,
      'connection_timeout' => '3',
      'request_timeout' => '3',
      'test_cvr' => '32342280',
      'test_result' => null
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state)
  {
    $form = array(
      'service_status' => array(
        '#type' => 'checkbox',
        '#title' => $this->t('Aktive'),
        '#default_value' => $this->configuration['service_status'],
        '#description' => t('if activated the other cvr service will be deactivated. ( Serviceplatformen CVR )')
      ),
      'service_user' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Tjenste bruger'),
        '#default_value' => $this->configuration['service_user']
      ),
      'service_pass' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Tjenste password'),
        '#default_value' => $this->configuration['service_pass']
      ),
      'connection_timeout' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Forbindelses timeout i sekunder'),
        '#default_value' => $this->configuration['connection_timeout']
      ),
      'request_timeout' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Forespørgelses timeout i sekunder'),
        '#default_value' => $this->configuration['request_timeout']
      ),
      'test_cvr' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Test CVR nr.'),
        '#default_value' => $this->configuration['test_cvr']
      ),
      'test_result' => array(
        '#type' => 'textarea',
        '#rows' => 10,
        '#title' => $this->t('Test result.'),
        '#default_value' => '' // $this->testRequest()
      )
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    $keys = array_keys($this->configuration);
    $configuration = $this->getConfiguration();
    foreach ($keys as $key) {
      $configuration[$key] = $form_state->getValue($key);
    }

    $this->setConfiguration($configuration);

    if (!empty($form_state->getValue('test_cvr'))) {
      // $this->testRequest();
    }
  }


  public function testRequest() {

    $result = NULL;
    $configuration = (object) $this->getConfiguration();
    if (!empty($configuration->test_cvr)) {
      $options = [
        'timeout' => 5
      ];

      $Client = \Drupal::httpClient($options);

      $parameters = [
        'username' => $configuration->service_user,
        'password' => $configuration->service_pass,
        'pCVRNummer' => $configuration->test_cvr
      ];

      try {
        $Client->post('https://test03-s5-services.datafordeler.dk/CVR/HentCVRData/1/rest/hentVirksomhedMedCVRNummer', $parameters);

      } catch (\RuntimeException $e) {
        print_r($e->getCode());
      }

    }

    return $result;
  }


  public function getStatus() {
    return 'maybe ;-)';
  }


}