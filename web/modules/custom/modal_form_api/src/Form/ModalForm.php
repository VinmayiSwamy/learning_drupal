<?php

namespace Drupal\modal_form_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * ModalForm class.
 */
class ModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'modal_form_example_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $conn = Database::getConnection();
     $record = array();
    if (isset($_GET['num'])) {
        $query = $conn->select('modal_form_data', 'm')
            ->condition('id', $_GET['num'])
            ->fields('m');
        $record = $query->execute()->fetchAssoc();
    }

    $form['#prefix'] = '<div id="modal_form_api">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name:'),
        '#required' => TRUE,
        '#default_value' => (isset($record['name']) && $_GET['num']) ? $record['name']:'',
      );
  
      $form['email'] = array(
        '#type' => 'email',
        '#title' => t('Email:'),
        '#required' => TRUE,
        '#default_value' => (isset($record['email']) && $_GET['num']) ? $record['email']:'',
      );
  
      $form['message'] = array(
        '#type' => 'textarea',
        '#title' => t('Message:'),
        '#rows' => 6,
        '#cols' => 2,
        '#required' => TRUE,
        '#default_value' => (isset($record['message']) && $_GET['num']) ? $record['message']:'',
      );

    // A required checkbox field.
    $form['our_checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I Agree: modal forms are awesome!'),
      '#required' => TRUE,
    ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit modal form'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $conn = Database::getConnection();
    $record = array();
   if (isset($_GET['num'])) {
       $query = $conn->select('modal_form_data', 'm')
           ->condition('id', $_GET['num'])
           ->fields('m');
       $record = $query->execute()->fetchAssoc();
   }

    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $message = $form_state->getValue('message');
     
    $insert = array('id' => Null, 'name' => $name, 'email' => $email, 'message' => $message);
    db_insert('modal_form_data')
    ->fields($insert)
    ->execute();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_form_api', $form));
    }
    else {      
      $success = 'The modal form has been submitted and saved in database. <br/><br/>'. 'Submitted data is: <br/> Name: ' .$name. '<br/>Email: ' .$email. '<br/>Message: ' .$message;

      $response->addCommand(new OpenModalDialogCommand("Success!", $success , ['width' => 800]));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}
