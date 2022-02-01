<?php

namespace Drupal\contact_form\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactForm extends FormBase {

  /**
   * @var Connection $connection
   */
  protected $connection;
  protected $messenger;

  /**
   * Class constructor.
   */
  public function __construct(Connection $connection, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'contact_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),
      '#required' => TRUE,
    );

    $form['subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject:'),
      '#required' => TRUE,
    );

    $form['message'] = array(
      '#type' => 'textarea',
      '#title' => t('Message:'),
      '#required' => TRUE,
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->connection->insert('feedback')->fields(
      array(
        'name' => $form_state->getValue('name'),
        'subject' => $form_state->getValue('subject'),
        'message' => $form_state->getValue('message'),
      )
    )->execute();

    $form_state->setRedirect('<front>');
    $this->messenger->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
  }
}
