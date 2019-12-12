<?php

namespace Drupal\os2web_datalookup\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * DataLookupManager plugin manager.
 *
 * @see \Drupal\os2web_datalookup\Annotation\DataLookup
 * @see \Drupal\os2web_datalookup\Plugin\DataLookupInterface
 * @see plugin_api
 */
class DataLookupManager extends DefaultPluginManager {

  /**
   * Constructs an DataLookupManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/os2web/DataLookup',
      $namespaces,
      $module_handler,
      'Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupInterface',
      'Drupal\os2web_datalookup\Annotation\DataLookup');

    $this->alterInfo('os2web_datalookup_info');
    $this->setCacheBackend($cache_backend, 'os2web_datalookup');
  }

}
