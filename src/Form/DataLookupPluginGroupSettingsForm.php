<?php

namespace Drupal\os2web_datalookup\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\os2web_datalookup\Plugin\DataLookupManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form or configuring AuthProvider plugin.
 */
class DataLookupPluginGroupSettingsForm extends ConfigFormBase {

  /**
   * Name of the config.
   *
   * @var string
   */
  public static string $configName = 'os2web_datalookup.groups.settings';

  /**
   * Data lookup manager.
   *
   * @var \Drupal\os2web_datalookup\Plugin\DataLookupManager
   */
  protected DataLookupManager $dataLookupManager;

  /**
   * Constructs a new DataLookupPluginGroupSettingsForm object.
   *
   * @param \Drupal\os2web_datalookup\Plugin\DataLookupManager $dataLookupManager
   *   Data lookup manager.
   */
  public function __construct(PluginManagerInterface $dataLookupManager) {
    $this->dataLookupManager = $dataLookupManager;
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2web_datalookop_group_settings_form_' . $this->getGroupIdFromRequest();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [DataLookupPluginGroupSettingsForm::$configName];
  }

  /**
   * Returns the value of the param _plugin_id for the current request.
   *
   * @see \Drupal\os2web_nemlogin\Routing\AuthProviderRoutes
   */
  protected function getGroupIdFromRequest() {
    $request = $this->getRequest();
    return $request->get('_group_id');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $group_id = $this->getGroupIdFromRequest();

    $plugin_definitions = $this->dataLookupManager->getDefinitionsByGroup($group_id);

    $header = [
      'title' => $this
        ->t('Title'),
      'status' => $this
        ->t('Status'),
      'action' => $this
        ->t('Actions'),
    ];

    $options = [];
    foreach ($plugin_definitions as $id => $plugin_definition) {
      /** @var \Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupInterface $plugin */
      $plugin = $this->dataLookupManager->createInstance($id);

      $status = $plugin->getStatus();

      $options[$plugin_definition['id']] = [
        'title' => $plugin_definition['label'],
        'status' => ($plugin->isReady() ? $this->t('READY') : $this->t('ERROR')) . ': ' . $status,
        'group' => $plugin_definition['group'],
        'action' => Link::createFromRoute($this->t('Settings'), "os2web_datalookup.$id"),
      ];
    }

    $form['default_plugin'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#multiple' => FALSE,
      '#default_value' => $this->dataLookupManager->getGroupDefaultPlugin($group_id) ?? NULL,
      '#empty' => $this
        ->t('No plugins found'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $group_id = $this->getGroupIdFromRequest();

    $config = $this->config(DataLookupPluginGroupSettingsForm::$configName);

    $config->set("$group_id.default_plugin", $form_state->getValue('default_plugin'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
