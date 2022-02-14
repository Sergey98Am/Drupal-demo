<?php

namespace Drupal\social_media\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Media block.
 *
 * @Block(
 *   id = "block_social_media",
 *   admin_label = @Translation("Social Media"),
 * )
 */
class SocialMedia extends BlockBase implements ContainerFactoryPluginInterface {

  public $social_media = [];

  public $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MessengerInterface $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger')
    );
  }

  protected function socialMedia($title, $url) {
    $link = [
      "#type" => "link",
      '#title' => $title,
      "#url" => Url::fromUri($url),
    ];

    $this->social_media[] = $link;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $sites = $form_state->get('sites');
    $social_fieldset = $this->configuration['social_fieldset'];
    $count_social_fieldset = count($social_fieldset) - 1;

    if ($sites === NULL) {
      if (empty($social_fieldset)) {
        $form_state->set('sites', 1);
        $sites = 1;
      } else {
        $form_state->set('sites', $count_social_fieldset);
        $sites = $count_social_fieldset;
      }
    }

    $form['#tree'] = TRUE;
    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Sites'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($site = 0; $site < $sites; $site++) {
      $form['fieldset'][$site] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Site ') . ' ' . ($site + 1),
      ];

      $form['fieldset'][$site]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#required' => true,
        '#default_value' => $social_fieldset[$site]['name'] ?? '',
      ];

      $form['fieldset'][$site]['link'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Link'),
        '#required' => true,
        '#element_validate' => [[$this, 'validateLinkField']],
        '#default_value' => $social_fieldset[$site]['link'] ?? '',
      ];
    }

    $form['fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['fieldset']['actions']['add_site'] = [
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
      $form['fieldset']['actions']['remove_site'] = [
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
  public function validateLinkField($element, &$form_state, $form)
  {
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
    return $form['settings']['fieldset'];
  }

  /**
   * Submit handler for the "add-one-site" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('sites');
    $add_button = $name_field + 1;
    $form_state->set('sites', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove-one-site" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeOneSite(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('sites');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('sites', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['social_fieldset'] = $values['fieldset'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_fieldset = $this->configuration['social_fieldset'];
    foreach ($social_fieldset as $key => $item) {
      if ($key !== 'actions') {
        $name = $item['name'];
        $url = $item['link'];
        $this->socialMedia($name, $url);
      }
    }

    return [
      '#theme' => 'social_media',
      '#social_links' => $this->social_media,
    ];
  }

}
