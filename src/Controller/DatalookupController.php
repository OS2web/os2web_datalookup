<?php

namespace Drupal\os2web_datalookup\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data lookup controller.
 *
 * @package Drupal\os2web_datalookup\Controller
 */
class DatalookupController extends ControllerBase {

  /**
   * The manager to be used for instantiating plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected PluginManagerInterface $manager;

  /**
   * Default constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manger.
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.os2web_datalookup')
    );
  }

  /**
   * Status list callback.
   *
   * @return array<string, mixed>
   *   An render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function statusList(): array {
    $headers = [
      'title' => $this
        ->t('Title'),
      'status' => $this
        ->t('Status'),
      'group' => $this
        ->t('Group'),
      'action' => $this
        ->t('Actions'),
    ];

    $rows = [];
    foreach ($this->manager->getDefinitions() as $id => $plugin_definition) {
      /** @var \Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupInterface $plugin */
      $plugin = $this->manager->createInstance($id);
      $status = $plugin->getStatus();
      $rows[$id] = [
        'title' => $plugin_definition['label'],
        'status' => ($plugin->isReady() ? $this->t('READY') : $this->t('ERROR')) . ': ' . $status,
        'group' => $plugin_definition['group'],
        'action' => Link::createFromRoute($this->t('Settings'), "os2web_datalookup.$id"),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
    ];
  }

}
