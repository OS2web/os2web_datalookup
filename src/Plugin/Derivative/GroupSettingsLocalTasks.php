<?php

namespace Drupal\os2web_datalookup\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\os2web_datalookup\Plugin\DataLookupManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic group settings local tasks.
 */
class GroupSettingsLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Data lookup manager.
   *
   * @var \Drupal\os2web_datalookup\Plugin\DataLookupManager
   */
  protected DataLookupManager $dataLookupManager;

  /**
   * Constructs a new GroupSettingsLocalTasks object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $dataLookupManager
   *   Data lookup manager.
   */
  public function __construct(PluginManagerInterface $dataLookupManager) {
    $this->dataLookupManager = $dataLookupManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): static {
    return new static(
      $container->get('plugin.manager.os2web_datalookup')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    // Implement dynamic logic to provide values for the same keys as in
    // example.links.task.yml.
    $groups = $this->dataLookupManager->getDatalookupGroups();

    foreach ($groups as $group) {
      $this->derivatives[$group] = $base_plugin_definition;
      $this->derivatives[$group]['title'] = $group;
      $this->derivatives[$group]['route_name'] = "os2web_datalookup.groups.$group";
      $this->derivatives[$group]['base_route'] = "os2web_datalookup.status_list";
    }

    return $this->derivatives;
  }

}
