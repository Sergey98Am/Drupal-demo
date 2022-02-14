<?php

namespace Drupal\social_media\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Media block.
 *
 * @Block(
 *   id = "block_social_media_global",
 *   admin_label = @Translation("Social Media Global"),
 * )
 */
class SocialMediaGlobal extends BlockBase implements ContainerFactoryPluginInterface {

  public $social_media_global = [];

  public $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

  public function socialMediaGlobal($title, $url) {
    $link = [
      "#type" => "link",
      '#title' => $title,
      "#url" => Url::fromUri($url),
    ];

    $this->social_media_global[] = $link;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_fieldset = $this->configFactory->get('social_media.settings')->get('global_fieldset');
    foreach ($social_fieldset as $key => $item) {
      if ($key !== 'actions') {
        $name = $item['name'];
        $url = $item['link'];
        $this->socialMediaGlobal($name, $url);
      }
    }

    return [
      '#theme' => 'social_media_global',
      '#social_links' => $this->social_media_global,
    ];
  }

}
