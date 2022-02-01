<?php

namespace Drupal\contact_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactController extends ControllerBase {

  /**
   * @var Connection $connection
   */
  protected $connection;

  /**
   * Class constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function content()
  {
    $query = $this->connection->select('feedback', 'fdb');
    $query->fields('fdb', ['name', 'subject', 'message']);
    $result = $query->execute()->fetchAll();

    return [
      '#theme' => 'contact',
      '#result' => $result,
    ];
  }

}
