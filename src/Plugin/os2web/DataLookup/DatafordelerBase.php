<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os2web_datalookup\Exception\RuntimeException;
use Drupal\os2web_audit\Service\Logger;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Defines base plugin class for Datafordeler lookup plugins.
 */
abstract class DatafordelerBase extends DataLookupBase {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected Client $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Logger $auditLogger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $auditLogger);
    $this->init();
  }

  /**
   * {@inheritdoc}
   */
  private function init(): void {
    $this->isReady = FALSE;

    $configuration = $this->getConfiguration();

    if ($webserviceUrl = $configuration['webserviceurl_live']) {
      $options = [
        'base_uri' => $webserviceUrl,
        'headers' => [
          'accept' => 'application/json',
        ],
      ];

      if (isset($configuration['cert_path_live']) || isset($configuration['key'])) {
        $options['cert'] = $this->createLocalCertPath();

        $this->httpClient = new Client($options);
        $this->isReady = TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): string {
    if (isset($this->httpClient)) {
      return $this->t('Plugin is ready to work')->render();
    }
    else {
      return $this->t('Configuration is not completed')->render();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['webserviceurl_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webservice URL (LIVE)'),
      '#description' => $this->t('Live URL against which to make the request, e.g. https://s5-certservices.datafordeler.dk/CVR/HentCVRData/1/REST/'),
      '#default_value' => $this->configuration['webserviceurl_live'],
    ];

    $form['certificate'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Certificate'),

      'certificate_provider' => [
        '#type' => 'select',
        '#title' => $this->t('Provider'),
        '#options' => [
          self::PROVIDER_TYPE_FORM => $this->t('Form'),
          self::PROVIDER_TYPE_KEY => $this->t('Key'),
        ],
        '#default_value' => $this->configuration['certificate_provider'] ?? self::PROVIDER_TYPE_FORM,
      ],

      'certificate_key' => [
        '#type' => 'key_select',
        '#key_filters' => [
          'type' => 'os2web_certificate',
        ],
        '#title' => $this->t('Key'),
        '#default_value' => $this->configuration['certificate_key'] ?? NULL,
        '#states' => [
          'required' => [':input[name="certificate_provider"]' => ['value' => self::PROVIDER_TYPE_KEY]],
          'visible' => [':input[name="certificate_provider"]' => ['value' => self::PROVIDER_TYPE_KEY]],
        ],
      ],

    'cert_path_live' => [
      '#type' => 'textfield',
      '#title' => $this->t('Certificate (LIVE)'),
      '#description' => $this->t('Path to the certificate'),
      '#default_value' => $this->configuration['cert_path_live'],
      '#states' => [
        'required' => [':input[name="certificate_provider"]' => ['value' => self::PROVIDER_TYPE_FORM]],
        'visible' => [':input[name="certificate_provider"]' => ['value' => self::PROVIDER_TYPE_FORM]],
      ],
    ],

    'cert_passphrase_live' => [
      '#type' => 'password',
      '#title' => $this->t('Certificate passphrase (LIVE)'),
      '#description' => $this->t('leave empty if not used'),
      '#default_value' => $this->configuration['cert_passphrase_live'],
      '#states' => [
        'visible' => [':input[name="certificate_provider"]' => ['value' => self::PROVIDER_TYPE_FORM]],
      ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue('cert_passphrase_live') == '') {
      $form_state->unsetValue('cert_passphrase_live');
    }

    $keys = array_keys($this->defaultConfiguration());
    $configuration = $this->getConfiguration();
    foreach ($keys as $key) {
      $configuration[$key] = $form_state->getValue($key);
    }
    $this->setConfiguration($configuration);
  }

  /**
   * Get response.
   */
  protected function getResponse(string $uri, array $options): ResponseInterface
  {
    try {
      $localCertPath = $this->writeCertificateToFile();

      return $this->httpClient->get($uri, $options);
    } finally {
      // Remove temporary certificate file.
      if (file_exists($localCertPath)) {
        unlink($localCertPath);
      }
    }
  }

  /**
   * Get certificate.
   */
  protected function getCertificate(): string {
    $provider = $this->configuration['certificate_provider'] ?? NULL;
    if (self::PROVIDER_TYPE_KEY === $provider) {
      $keyId = $this->configuration['certificate_key'] ?? '';
      $key = $this->keyRepository->getKey($keyId);
      if (NULL === $key) {
        throw new RuntimeException(sprintf('Cannot get key %s', $keyId));
      }

      return $key->getKeyValue();
    }

    $filename = $this->configuration['cert_path_live'];

    return file_get_contents($filename);
  }
}
