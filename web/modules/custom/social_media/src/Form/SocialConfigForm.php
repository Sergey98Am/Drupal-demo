<?php

/**
 * @file
 * Contains \Drupal\simple\Form\SocialConfigForm.
 */

namespace Drupal\social_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SocialConfigForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return [
      'social_media.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $sites = $form_state->get('sites_global');
    $social_fieldset = $this->config('social_media.settings')
      ->get('global_fieldset');
    $count_social_fieldset = count($social_fieldset) - 1;

    if ($sites === NULL) {
      if (empty($social_fieldset)) {
        $form_state->set('sites_global', 1);
        $sites = 1;
      }
      else {
        $form_state->set('sites_global', $count_social_fieldset);
        $sites = $count_social_fieldset;
      }
    }

    $form['#tree'] = TRUE;
    $form['global_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sites'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($site = 0; $site < $sites; $site++) {
      $form['global_fieldset'][$site] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Site ') . ' ' . ($site + 1),
      ];

      $form['global_fieldset'][$site]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#required' => TRUE,
        '#default_value' => $social_fieldset[$site]['name'] ?? '',
      ];

      $form['global_fieldset'][$site]['link'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Link'),
        '#required' => TRUE,
        '#element_validate' => [[$this, 'validateLinkField']],
        '#default_value' => $social_fieldset[$site]['link'] ?? '',
      ];
    }

    $form['global_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['global_fieldset']['actions']['add_site'] = [
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
      $form['global_fieldset']['actions']['remove_site'] = [
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

  /**
   * Form Validation
   */
  public function validateLinkField($element, &$form_state, $form) {
    if (!filter_var($element['#value'], FILTER_VALIDATE_URL)) {
      $form_state->setError($element, t('Please enter a valid Website URL'));
    }
  }

  //  public function validateForm(array &$form, FormStateInterface $form_state) {
  //    $values = $form_state->getValues();
  //    foreach ($values['global_fieldset'] as $key => $value) {
  //      $link = $form_state->getValue(['global_fieldset', $key, 'link']);
  //
  //      if (!filter_var($link,FILTER_VALIDATE_URL)) {
  ////        ksm($value['link']);
  //        $form_state->setErrorByName('link', $this->t('Votre prénom est obligatoire.'));
  //      }
  //    }
  //  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    $this->config('social_media.settings')
      ->set('global_fieldset', $values['global_fieldset'])
      ->save();
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function siteCallback(array &$form, FormStateInterface $form_state) {
    return $form['global_fieldset'];
  }

  /**
   * Submit handler for the "add-one-site" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('sites_global');
    $add_button = $name_field + 1;
    $form_state->set('sites_global', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove-one-site" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('sites_global');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('sites_global', $remove_button);
    }
    $form_state->setRebuild();
  }

}
