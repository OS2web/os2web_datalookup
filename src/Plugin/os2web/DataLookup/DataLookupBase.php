<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\os2web_audit\Service\Logger;
use Drupal\key\KeyRepositoryInterface;
use Drupal\os2web_datalookup\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for image effects.
 *
 * @see \Drupal\image\Annotation\ImageEffect
 * @see \Drupal\image\ImageEffectInterface
 * @see \Drupal\image\ConfigurableImageEffectInterface
 * @see \Drupal\image\ConfigurableImageEffectBase
 * @see \Drupal\image\ImageEffectManager
 * @see plugin_api
 */
abstract class DataLookupBase extends PluginBase implements DataLookupInterface, ContainerFactoryPluginInterface {
  protected const string PROVIDER_TYPE_FORM = 'form';
  protected const string PROVIDER_TYPE_KEY = 'key';

  /**
   * Local certificate path.
   *
   * Used to temporarily store a certificate in a file just before calling a
   * webservice.
   * For security purposes, the file will be removed right after the webservice
   * call completes (even unsuccessfully).
   *
   * @var string
   */
  private string $localCertPath;

  /**
   * Plugin readiness flag.
   *
   * @var bool
   */
  protected bool $isReady = TRUE;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected Logger $auditLogger,
    protected KeyRepositoryInterface $keyRepository,
    protected FileSystem $fileSystem,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
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
      $auditLogger,
      $keyRepository,
      $fileSystem
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration): static {
    $this->configuration = $configuration + $this->defaultConfiguration();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'certificate_provider' => '',
      'certificate_key' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Making validate optional.
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus(): string {
    return 'N/A';
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): string {
    return $this->pluginDefinition['group'];
  }

  /**
   * {@inheritdoc}
   */
  public function isReady(): bool {
    return $this->isReady;
  }

  /**
   * Get certificate.
   */
  protected function getCertificate(): string {
    return '';
  }

  /**
   * Create a temporary file path for a certificate.
   *
   * Note: We do not want the create a file. Just get a temporary file name.
   *
   * @return string
   *   The local certificate path.
   */
  protected function createLocalCertPath(): string {
    $this->localCertPath = $this->fileSystem->getTempDirectory() . '/' . uniqid('os2web_datalookup_local_cert_');

    return $this->localCertPath;
  }

  /**
   * Write certificate to temporary certificate file.
   *
   * @return string
   *   The local certificate path.
   */
  protected function writeCertificateToFile(): string {
    // Write certificate to local_cert location.
    $certificate = $this->getCertificate();
    $localCertPath = $this->localCertPath;
    $result = $this->fileSystem->saveData($certificate, $localCertPath, FileExists::Replace);
    if (!$result) {
      return new RuntimeException(sprintf('Error writing certificate to temporary file %s', $localCertPath));
    }

    return $result;
  }

}
