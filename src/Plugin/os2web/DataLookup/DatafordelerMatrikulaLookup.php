<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\key\KeyRepositoryInterface;
use Drupal\os2web_audit\Service\Logger;
use Drupal\os2web_datalookup\LookupResult\MatrikulaLookupResult;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a plugin for Datafordeler Matrikula Lookup.
 *
 * @DataLookup(
 *   id = "datafordeler_matrikula_lookup",
 *   label = @Translation("Datafordeler Matrikula Lookup"),
 * )
 */
class DatafordelerMatrikulaLookup extends DataLookupBase implements DatafordelerMatrikulaLookupInterface, ContainerFactoryPluginInterface {

  /**
   * Datafordeler DAR GraphQL endpoint.
   */
  private const DAR_GRAPHQL_ENDPOINT = 'https://graphql.datafordeler.dk/DAR/v3';

  /**
   * Datafordeler MAT GraphQL endpoint.
   */
  private const MAT_GRAPHQL_ENDPOINT = 'https://graphql.datafordeler.dk/flexibleCurrent/v3';

  /**
   * Logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

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
    LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $auditLogger, $keyRepository, $fileSystem);
    $this->logger = $logger;
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
      $container->get('logger.factory')->get('os2web_datalookup')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'api_key' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $this->configuration['api_key'],
      '#required' => TRUE,
      '#description' => $this->t('Enter the Datafordeler GraphQL API key.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $configuration = $this->getConfiguration();
    $configuration['api_key'] = $form_state->getValue('api_key');
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getMatrikulaId(string $addressAccessId): ?string {
    $query = <<<'GRAPHQL'
query FindHusnummer($addressAccessId: String!, $registreringstid: DafDateTime!, $virkningstid: DafDateTime!) {
  DAR_Husnummer(
    where: {id_lokalId: {eq: $addressAccessId}}
    registreringstid: $registreringstid
    virkningstid: $virkningstid
  ) {
    nodes {
      jordstykke
    }
  }
}
GRAPHQL;

    // Use the same instant for registration and effect to select current data.
    $timestamp = $this->getCurrentTimestamp();
    $response = $this->executeQuery(
      self::DAR_GRAPHQL_ENDPOINT,
      'FindHusnummer',
      $query,
      [
        'addressAccessId' => $addressAccessId,
        'registreringstid' => $timestamp,
        'virkningstid' => $timestamp,
      ],
    );

    $jordstykke = $response === NULL ? NULL : NestedArray::getValue(
      $response,
      ['data', 'DAR_Husnummer', 'nodes', 0, 'jordstykke'],
    );
    if (is_scalar($jordstykke) && trim((string) $jordstykke) !== '') {
      return (string) $jordstykke;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMatrikulaEntries(string $matrikulaId): array {
    $query = <<<'GRAPHQL'
query FindSamletFastEjendom($matrikulaId: String!, $virkningstid: DafDateTime!, $after: String) {
  MAT_Jordstykke(
    where: {id_lokalId: {eq: $matrikulaId}}
    virkningstid: $virkningstid
    first: 1
  ) {
    nodes {
      jordstykkeSamlesISamletFastEjendom(first: 1) {
        nodes {
          jordstykkeSamlesISamletFastEjendom(first: 50, after: $after) {
            nodes {
              id_lokalId
              matrikelnummer
              jordstykkeLiggerIEjerlav {
                ejerlavskode
                ejerlavsnavn
              }
            }
            pageInfo {
              hasNextPage
              endCursor
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

    $matrikulaEntries = [];
    // Keep the effect time fixed while retrieving every page of parcels.
    $timestamp = $this->getCurrentTimestamp();
    $after = NULL;
    $seenCursors = [];
    $seenParcels = [];

    do {
      $response = $this->executeQuery(
        self::MAT_GRAPHQL_ENDPOINT,
        'FindSamletFastEjendom',
        $query,
        [
          'matrikulaId' => $matrikulaId,
          'virkningstid' => $timestamp,
          'after' => $after,
        ],
      );
      if ($response === NULL) {
        break;
      }

      $connection = NestedArray::getValue($response, [
        'data',
        'MAT_Jordstykke',
        'nodes',
        0,
        'jordstykkeSamlesISamletFastEjendom',
        'nodes',
        0,
        'jordstykkeSamlesISamletFastEjendom',
      ]);
      if (!is_array($connection) || !isset($connection['nodes']) || !is_array($connection['nodes'])) {
        break;
      }

      foreach ($connection['nodes'] as $jordstykke) {
        if (!is_array($jordstykke)) {
          continue;
        }

        $parcelId = $jordstykke['id_lokalId'] ?? NULL;
        if (is_scalar($parcelId) && isset($seenParcels[(string) $parcelId])) {
          continue;
        }
        if (is_scalar($parcelId)) {
          $seenParcels[(string) $parcelId] = TRUE;
        }

        $ejerlav = $jordstykke['jordstykkeLiggerIEjerlav'] ?? [];
        $ejerlav = is_array($ejerlav) ? $ejerlav : [];
        $matrikula = new MatrikulaLookupResult();
        $matrikula->setOwnerLicenseCode($ejerlav['ejerlavskode'] ?? '');
        $matrikula->setOwnershipName($ejerlav['ejerlavsnavn'] ?? '');
        $matrikula->setMatrikulaNumber($jordstykke['matrikelnummer'] ?? '');
        $matrikulaEntries[] = $matrikula;
      }

      $pageInfo = $connection['pageInfo'] ?? [];
      if (!is_array($pageInfo) || empty($pageInfo['hasNextPage'])) {
        break;
      }

      $nextCursor = $pageInfo['endCursor'] ?? NULL;
      if (!is_string($nextCursor) || $nextCursor === '' || isset($seenCursors[$nextCursor])) {
        $this->logger->warning('Datafordeler GraphQL operation FindSamletFastEjendom returned a stalled cursor.');
        break;
      }
      $seenCursors[$nextCursor] = TRUE;
      $after = $nextCursor;
    } while (TRUE);

    return $matrikulaEntries;
  }

  /**
   * Executes a GraphQL lookup request against Datafordeler.
   *
   * Sends the query and variables using the configured API key and validates
   * the response. Request failures are logged without exposing the API key.
   *
   * @param string $webserviceUrl
   *   The Datafordeler GraphQL endpoint.
   * @param string $operation
   *   The operation name used in log messages.
   * @param string $query
   *   The GraphQL query to execute.
   * @param array $variables
   *   The variables passed to the query.
   *
   * @return array|null
   *   The decoded response, or NULL if the request or response is invalid.
   */
  protected function executeQuery(string $webserviceUrl, string $operation, string $query, array $variables): ?array {
    $apiKey = trim($this->configuration['api_key'] ?? '');
    if ($apiKey === '') {
      $this->logger->warning('Datafordeler GraphQL operation @operation could not run because its API key is not configured.', [
        '@operation' => $operation,
      ]);

      return NULL;
    }

    try {
      $response = $this->httpClient->request('POST', $webserviceUrl, [
        'query' => [
          'apiKey' => $apiKey,
        ],
        'headers' => [
          'Accept' => 'application/graphql-response+json',
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'query' => $query,
          'variables' => $variables,
        ],
      ]);
    }
    catch (GuzzleException $e) {
      $this->logger->warning('Datafordeler GraphQL operation @operation failed.', [
        '@operation' => $operation,
      ]);

      return NULL;
    }

    $result = json_decode((string) $response->getBody(), TRUE);
    if (!is_array($result) || !empty($result['errors'])) {
      $this->logger->warning('Datafordeler GraphQL operation @operation returned an invalid response.', [
        '@operation' => $operation,
      ]);

      return NULL;
    }

    return $result;
  }

  /**
   * Gets the current UTC instant for Datafordeler temporal filters.
   *
   * @return string
   *   The current timestamp in ISO 8601 format.
   */
  protected function getCurrentTimestamp(): string {
    return gmdate('Y-m-d\TH:i:s\Z');
  }

}
