<?php

namespace Drupal\os2web_datalookup\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\os2web_datalookup\Form\DataLookupPluginGroupSettingsForm;
use Drupal\os2web_datalookup\Form\DataLookupPluginSettingsForm;

/**
 * DataLookupManager plugin manager.
 *
 * @see \Drupal\os2web_datalookup\Annotation\DataLookup
 * @see \Drupal\os2web_datalookup\Plugin\DataLookupInterface
 * @see plugin_api
 */
class DataLookupManager extends DefaultPluginManager {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a DataLookupManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct(
      'Plugin/os2web/DataLookup',
      $namespaces,
      $module_handler,
      'Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupInterface',
      'Drupal\os2web_datalookup\Annotation\DataLookup');

    $this->alterInfo('os2web_datalookup_info');
    $this->setCacheBackend($cache_backend, 'os2web_datalookup');
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []): object {
    // Hard switch to another plugin fallback - see
    // https://os2web.atlassian.net/browse/OS2FORMS-359.
    if ($plugin_id == 'serviceplatformen_cpr') {
      // phpcs:ignore
      \Drupal::logger('os2web_datalookup')->warning('"Serviceplatformen CPR (SF6008)" is obsolete and will be phased out. Automatically switched to "Serviceplatformen CPR - extended (SF1520)"');
      $plugin_id = 'serviceplatformen_cpr_extended';
    }

    if (empty($configuration)) {
      $configuration = $this->configFactory->get(DataLookupPluginSettingsForm::getConfigName() . '.' . $plugin_id)->get();
    }

    return parent::createInstance($plugin_id, $configuration);
  }

  /**
   * Creates a pre-configured instance of a plugin, default for provided group.
   *
   * @param string $group_id
   *   The ID of the group being instantiated.
   *
   * @return object
   *   A fully configured plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the instance cannot be created, such as if the ID is invalid.
   */
  public function createDefaultInstanceByGroup(string $group_id): object {
    $plugin_id = $this->getGroupDefaultPlugin($group_id);
    return $this->createInstance($plugin_id);
  }

  /**
   * Gets the definition of all plugins for this type based on group.
   *
   * @param string $group_id
   *   Group id.
   *
   * @return array
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   */
  public function getDefinitionsByGroup(string $group_id): array {
    $definitions = [];
    foreach ($this->getDefinitions() as $id => $plugin_definition) {
      if ($plugin_definition['group'] == $group_id) {
        $definitions[$id] = $plugin_definition;
      }
    }

    return $definitions;
  }

  /**
   * Returns groups of all existing Datalookup plugins.
   *
   * @return array
   *   Array of groups, keyed by group_id.
   */
  public function getDatalookupGroups(): array {
    $definitions = $this->getDefinitions();
    $groups = [];
    foreach ($definitions as $definition) {
      if (!empty($definition['group'])) {
        $groups[$definition['group']] = $definition['group'];
      }
    }

    return $groups;
  }

  /**
   * Returns the group default plugin.
   *
   * @param string $group_id
   *   Group ID.
   *
   * @return string|null
   *   Plugin ID or NULL is not set.
   */
  public function getGroupDefaultPlugin(string $group_id): ?string {
    // phpcs:ignore
    $config = \Drupal::config(DataLookupPluginGroupSettingsForm::$configName);

    return $config->get("$group_id.default_plugin");
  }

}
