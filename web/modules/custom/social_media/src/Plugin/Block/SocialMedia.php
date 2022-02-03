<?php

namespace Drupal\social_media\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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

  public $module_handler;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->module_handler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  public function socialMedia($site_name, $image_name, $title, $url) {

    // module path
    $module_path = $this->module_handler->getModule('social_media')->getPath();
    // image path
    $image_path = '/images/';

    $link = [
      $site_name => [
        "#type" => "link",
        '#title' => [
          '#theme' => 'image',
          '#uri' => $module_path . $image_path . $image_name,
          '#alt' => $title,
        ],
        "#url" => Url::fromUri($url),
      ],
    ];

    $this->social_media[] = $link;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->socialMedia('facebook', 'facebook.png', 'Facebook', 'http://www.facebook.com/')
      ->socialMedia('twitter', 'twitter.png', 'Twitter', 'http://www.twitter.com/')
      ->socialMedia('instagram', 'instagram.png', 'Instagram', 'http://www.instagram.com/');

    return [
      '#theme' => 'social_media',
      '#social_links' => $this->social_media,
    ];
  }

}
