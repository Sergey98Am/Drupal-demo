<?php
/**
 * @file
 * Contains \Drupal\article\Plugin\Block\ContactFormBlock.
 */

namespace Drupal\contact_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Media block.
 *
 * @Block(
 *   id = "contact_form_block",
 *   admin_label = @Translation("Contact Form Block"),
 *   category = @Translation("Custom Contact Form Block example")
 * )
 */
class ContactFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public $form_builder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->form_builder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = $this->form_builder->getForm('Drupal\contact_form\Form\ContactForm');

    return $form;
  }
}
