<?php

namespace Drupal\os2web_datalookup\Plugin\os2web\DataLookup;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a plugin for ServiceplatformenCPR.
 *
 * @DataLookup(
 *   id = "serviceplatformen_cpr",
 *   label = @Translation("Serviceplatformen CPR"),
 * )
 */
class ServiceplatformenCPR extends ServiceplatformenBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge(parent::defaultConfiguration(), [
      'test_mode_fixed_cpr' => '',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['mode_fieldset']['test_mode_fixed_cpr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fixed test CPR'),
      '#default_value' => $this->configuration['test_mode_fixed_cpr'],
      '#description' => $this->t('Is set, fixed CPR will be used for all requests to the serviceplatformen instead of the provided CPR.'),
      '#states' => [
        // Hide the settings when the cancel notify checkbox is disabled.
        'visible' => [
          'input[name="mode_selector"]' => ['value' => 1],
        ],
      ],
    ];
    $form['test_fieldset']['test_cpr'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test cpr nr.'),
    ];
    return $form;
  }
}
