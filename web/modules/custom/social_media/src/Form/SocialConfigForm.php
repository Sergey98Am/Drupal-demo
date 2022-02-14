<?php

/**
 * @file
 * Contains \Drupal\simple\Form\SocialConfigForm.
 */

namespace Drupal\social_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SocialConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'social_media.settings';

  protected $formNameDB = 'sites_global';
  protected $fieldsetName = 'global_fieldset';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $sites = $form_state->get($this->formNameDB);
    $social_fieldset = $this->config(static::SETTINGS)
      ->get($this->fieldsetName);
    $count_social_fieldset = count($social_fieldset) - 1;

    if ($sites === NULL) {
      if (empty($social_fieldset)) {
        $form_state->set($this->formNameDB, 1);
        $sites = 1;
      }
      else {
        $form_state->set($this->formNameDB, $count_social_fieldset);
        $sites = $count_social_fieldset;
      }
    }

    $form['#tree'] = TRUE;
    $form[$this->fieldsetName] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sites'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($site = 0; $site < $sites; $site++) {
      $form[$this->fieldsetName][$site] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Site ') . ' ' . ($site + 1),
      ];

      $form[$this->fieldsetName][$site]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#required' => TRUE,
        '#default_value' => $social_fieldset[$site]['name'] ?? '',
      ];

      $form[$this->fieldsetName][$site]['link'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Link'),
        '#required' => TRUE,
        '#element_validate' => [[$this, 'validateLinkField']],
        '#default_value' => $social_fieldset[$site]['link'] ?? '',
      ];
    }

    $form[$this->fieldsetName]['actions'] = [
      '#type' => 'actions',
    ];
    $form[$this->fieldsetName]['actions']['add_site'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#limit_validation_errors' => [],
      '#submit' => [[$this, 'addOneSite']],
      '#ajax' => [
        'callback' => [$this, 'siteCallback'],
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];

    if ($sites > 1) {
      $form[$this->fieldsetName]['actions']['remove_site'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#limit_validation_errors' => [],
        '#submit' => [[$this, 'removeOneSite']],
        '#ajax' => [
          'callback' => [$this, 'siteCallback'],
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    $this->config('social_media.settings')
      ->set($this->fieldsetName, $values[$this->fieldsetName])
      ->save();
  }

  /**
   * Form Validation
   */
  public function validateLinkField($element, &$form_state, $form) {
    if (!filter_var($element['#value'], FILTER_VALIDATE_URL)) {
      $form_state->setError($element, t('Please enter a valid Website URL'));
    }
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function siteCallback(array &$form, FormStateInterface $form_state) {
    return $form[$this->fieldsetName];
  }

  /**
   * Submit handler for the "add-one-site" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get($this->formNameDB);
    $add_button = $name_field + 1;
    $form_state->set($this->formNameDB, $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove-one-site" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get($this->formNameDB);
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set($this->formNameDB, $remove_button);
    }
    $form_state->setRebuild();
  }

}
