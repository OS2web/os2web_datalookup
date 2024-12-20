<?php

namespace Drupal\os2web_datalookup\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a AuthProvider annotation object.
 *
 * Plugin Namespace: Plugin/os2web/DataLookup.
 *
 * @see hook_os2web_nemlogin_auth_provider_info_alter()
 * @see \Drupal\os2web_datalookup\Plugin\DataLookupManager
 * @see plugin_api
 *
 * @Annotation
 */
class DataLookup extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public string $id;

  /**
   * The human-readable name of the consent storage.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $label;

  /**
   * A brief description of the consent storage.
   *
   * This will be shown when adding or configuring this consent storage.
   *
   * @var \Drupal\Core\Annotation\Translation|string
   *
   * @ingroup plugin_translatable
   */
  public Translation|string $description = '';

  /**
   * Group of the plugin lookup plugins.
   *
   * @var string
   */
  public string $group = '';

}
