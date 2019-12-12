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
class ServiceplatformenCPR extends DataLookupBase implements DataLookupInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['title'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $configuration['title'] = $form_state->getValue('title');
    $this->setConfiguration($configuration);
  }

}
