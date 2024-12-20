<?php

namespace Drupal\os2web_datalookup\Routing;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides dynamic routes for AuthProvider.
 */
class DataLookupRoutes implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The manager to be used for instantiating plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected PluginManagerInterface $manager;

  /**
   * Constructs a new AuthProvider route subscriber.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The AuthProviderManager.
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.os2web_datalookup')
    );
  }

  /**
   * Provides route definition for AuthProvider plugins settings form.
   *
   * @return array<string, mixed>
   *   Array with route definitions.
   */
  public function routes(): array {
    $pluginDefinitions = $this->manager->getDefinitions();
    $routes = [];
    $groups = [];

    foreach ($pluginDefinitions as $id => $plugin) {
      $routes["os2web_datalookup.$id"] = new Route(
        "/admin/config/system/os2web-datalookup/" . str_replace('_', '-', $plugin['id']),
        [
          '_form' => '\Drupal\os2web_datalookup\Form\DataLookupPluginSettingsForm',
          '_title' => $this->t("Configure :label", [':label' => $plugin['label']->__toString()])->__toString(),
          '_plugin_id' => $id,
        ],
        [
          '_permission' => 'administer os2web datalookup configuration',
        ]
      );

      // Collecting the groups.
      $groups[$plugin['group']] = $plugin['group'];
    }

    // Creating routes for group configuration.
    foreach ($groups as $group) {
      $routes["os2web_datalookup.groups.$group"] = new Route(
        "/admin/config/system/os2web-datalookup/" . str_replace('_', '-', $group),
        [
          '_form' => DataLookupPluginGroupSettingsForm::class,
          '_title' => $this->t("Configure group :label", [':label' => $group])->__toString(),
          '_group_id' => $group,
        ],
        [
          '_permission' => 'administer os2web datalookup configuration',
        ]
      );
    }

    return $routes;
  }

}
