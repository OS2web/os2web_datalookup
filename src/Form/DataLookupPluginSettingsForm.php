<?php

namespace Drupal\os2web_datalookup\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form or configuring DataLookup plugins.
 */
class DataLookupPluginSettingsForm extends PluginSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config_manager, PluginManagerInterface $manager) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('plugin.manager.os2web_datalookup')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getConfigName(): string {
    return 'os2web_datalookup';
  }

}
