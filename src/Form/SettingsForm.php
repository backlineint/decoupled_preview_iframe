<?php

namespace Drupal\decoupled_preview_iframe\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Configure Decoupled Preview Iframe Settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Construct a new GraphQL Compose settings form.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'decoupled_preview_iframe_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['decoupled_preview_iframe.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $content_types = $this->getContenTypes();

    $form['node_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Content Types:'),
      '#name' => 'node_types',
      '#description' => $this->t('Enable preview for the selected node types.'),
      '#description_display' => 'before',
    ];

    foreach ($content_types as [
      'label' => $label,
      'field_name' => $field_name,
      'config_name' => $config_name,
    ]) {
      $form['node_types'][$field_name] = [
        '#type' => 'checkbox',
        '#title' => $label,
        '#default_value' => boolval($this->config('decoupled_preview_iframe.settings')->get($config_name)),
        '#group' => 'node_types',
      ];
    }

    $form['preview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Front-end site:'),
      '#name' => 'preview',
    ];

    $form['preview']['preview_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Preview URL'),
      '#default_value' => $this->config('decoupled_preview_iframe.settings')->get('preview_url'),
      '#group' => 'preview',
    ];

    $form['route_sync'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Route Syncing:'),
      '#description' => $this->t('Sync route changes inside the iframe preview with your Drupal site.'),
      '#description_display' => 'before',
      '#name' => 'route_sync',
    ];

    $route_sync_type = $this->config('decoupled_preview_iframe.settings')->get('route_sync.type');
    $form['route_sync']['route_sync_type'] = [
      '#type' => 'textfield',
      '#name' => 'route_sync_type',
      '#title' => $this->t('Route Sync Type'),
      '#default_value' => $route_sync_type != "" ? $route_sync_type : 'DECOUPLED_PREVIEW_IFRAME_ROUTE_SYNC',
      '#group' => 'route_sync',
      '#description' => $this->t('Default: DECOUPLED_PREVIEW_IFRAME_ROUTE_SYNC' .
        ', Remix: REMIX_DRUPAL_ROUTE_SYNC' .
        ', Next.js: NEXT_DRUPAL_ROUTE_SYNC'),
    ];

    $form['draft'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Draft preview:'),
      '#name' => 'draft',
      '#description' => $this->t('Allow to select a provider to provide access to Node Draft data.'),
      '#description_display' => 'before',
    ];

    $draft_providers = $this->getDraftProviders();
    $form['draft']['draft_provider'] = [
      '#type' => 'select',
      '#name' => 'draft_provider',
      '#title' => 'Select Draft provider',
      '#options' => $draft_providers,
      '#default_value' => $this->config('decoupled_preview_iframe.settings')->get('draft.provider'),
      '#group' => 'draft',
      '#description' => $this->t('For GraphQL Compose: Install graphql_compose_preview module to support Draft Preview.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns the draft providers.
   *
   * @return array
   *   An array of draft providers.
   */
  public function getDraftProviders() {
    $draft_providers = [
      'none' => $this->t('None'),
    ];
    $draft_providers_modules = [
      'graphql_compose_preview',
    ];
    foreach ($draft_providers_modules as $module) {
      if (\Drupal::moduleHandler()->moduleExists($module)) {
        $draft_providers[$module] = \Drupal::moduleHandler()->getName($module);
      }
    }

    return $draft_providers;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // echo var_dump($form_state->getValues());
    // die(); 

    $content_types = $this->getContenTypes();
    foreach ($content_types as [
      'field_name' => $field_name,
      'config_name' => $config_name,
    ]) {
      $this->config('decoupled_preview_iframe.settings')
        ->set($config_name, boolval($form_state->getValue($field_name)))
        ->save();
    }

    $this->config('decoupled_preview_iframe.settings')
      ->set('route_sync.type', $form_state->getValue('route_sync_type'))
      ->save();

    $this->config('decoupled_preview_iframe.settings')
      ->set('preview_url', $form_state->getValue('preview_url'))
      ->save();

    $this->config('decoupled_preview_iframe.settings')
      ->set('draft.provider', $form_state->getValue('draft_provider'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  private function getContenTypes() {
    $node_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $content_types = [];
    foreach ($node_types as $node_type) {
      $field_name = 'node_type_' . $node_type->id();
      $config_name = 'node_types.' . $node_type->id();
      $content_types[$node_type->id()] = [
        'id' => $node_type->id(),
        'label' => $node_type->label(),
        'field_name' => $field_name,
        'config_name' => $config_name,
      ];
    }

    return $content_types;
  }

}
