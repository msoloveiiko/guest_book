<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class GuestBookForm.
 *
 * @package Drupal\GuestBookForm
 */
class GuestBookForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guest_book_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['system_messages'] = [
      '#markup' => '<div id="form-valid-message"></div>',
    ];
    $form['name'] = [
      '#title' => $this->t('Name:'),
      '#type' => 'textfield',
      '#placeholder' => ['From 2 to 100 letters'],
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email:'),
      '#placeholder' => ['user-_@company.'],
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'validateEmailAjax'],
        'event' => 'input',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
      '#prefix' => '<span class="email-valid-message"></span>',
    ];
    $form['mobile_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Mobile <br> number:'),
      '#placeholder' => ['xxxnnnnnnn'],
      '#required' => TRUE,
    ];
    $form['review'] = [
      '#type' => 'textarea',
      '#placeholder' => ['Message(required)'],
      '#required' => TRUE,
    ];
    $form['avatar'] = [
      '#title' => $this->t('Avatar'),
      '#description' => $this->t('Allowed photo format png jpg jpeg/ no more than 2MB'),
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://',
    ];
    $form['picture'] = [
      '#title' => $this->t('Picture'),
      '#description' => $this->t('Allowed photo format png jpg jpeg/ no more than 5MB'),
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => ('Submit'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmitCallback'],
      ],
    ];
    return $form;
  }

  /**
   * Ajax callback for submit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   JSON response object for AJAX requests.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new MessageCommand($this->t("Fill in the required fields with valid values."), "#form-valid-message", ["type" => "error"]));
    }
    elseif ($form_state->getValue('avatar') == NULL && $form_state->getValue('picture') == NULL) {
      $conn = Database::getConnection();
      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['mobile'] = $form_state->getValue('mobile_number');
      $fields['message'] = $form_state->getValue('review');
      $fields['avatar'] = '/modules/custom/guest_book/img/user_site.png';
      $current_date = \Drupal::time()->getCurrentTime();
      $today_date = \Drupal::service('date.formatter')->format($current_date, 'custom', 'd/M/Y H:i:s');
      $fields['date'] = $today_date;
      $conn->insert('guest_book')->fields($fields)->execute();
      $response->addCommand(new MessageCommand($this->t('Added.'), '#form-valid-message', ['type' => 'status']));
    }
    elseif ($form_state->getValue('avatar') == NULL) {
      $conn = Database::getConnection();

      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['mobile'] = $form_state->getValue('mobile_number');
      $fields['message'] = $form_state->getValue('review');
      $fields['avatar'] = '/modules/custom/guest_book/img/user_site.png';
      $fid = $form_state->getValue('picture');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $fields['photo'] = $url;
      $current_date = \Drupal::time()->getCurrentTime();
      $today_date = \Drupal::service('date.formatter')->format($current_date, 'custom', 'd/M/Y H:i:s');
      $fields['date'] = $today_date;
      $conn->insert('guest_book')->fields($fields)->execute();
      $response->addCommand(new MessageCommand($this->t('Added.'), '#form-valid-message', ['type' => 'status']));
    }
    elseif ($form_state->getValue('picture') == NULL) {
      $conn = Database::getConnection();

      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['mobile'] = $form_state->getValue('mobile_number');
      $fields['message'] = $form_state->getValue('review');
      $fid = $form_state->getValue('avatar');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $fields['avatar'] = $url;
      $current_date = \Drupal::time()->getCurrentTime();
      $today_date = \Drupal::service('date.formatter')->format($current_date, 'custom', 'd/M/Y H:i:s');
      $fields['date'] = $today_date;
      $conn->insert('guest_book')->fields($fields)->execute();
      $response->addCommand(new MessageCommand($this->t('Added.'), '#form-valid-message', ['type' => 'status']));
    }
    else {
      $conn = Database::getConnection();

      $fields['name'] = $form_state->getValue('name');
      $fields['email'] = $form_state->getValue('email');
      $fields['mobile'] = $form_state->getValue('mobile_number');
      $fields['message'] = $form_state->getValue('review');
      $fid = $form_state->getValue('avatar');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $fields['avatar'] = $url;
      $fid = $form_state->getValue('picture');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $fields['photo'] = $url;
      $current_date = \Drupal::time()->getCurrentTime();
      $today_date = \Drupal::service('date.formatter')->format($current_date, 'custom', 'd/M/Y H:i:s');
      $fields['date'] = $today_date;
      $conn->insert('guest_book')->fields($fields)->execute();
      $response->addCommand(new MessageCommand($this->t('Added.'), '#form-valid-message', ['type' => 'status']));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('name')
      || empty($form_state->getValue('name'))
    ) {
      $form_state->setErrorByName('name', $this->t('Enter name.'));
    }
    elseif (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', $this->t('Name is too short.'));
    }
    elseif (strlen($form_state->getValue('name')) > 100) {
      $form_state->setErrorByName('name', $this->t('Name is too long.'));
    }
    elseif (!$form_state->getValue('email')
      || empty($form_state->getValue('email'))
    ) {
      $form_state->setErrorByName('email', $this->t('Enter email.'));
    }
    elseif (!preg_match('/^[a-z_-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('Email not valid.'));
    }
    elseif (!$form_state->getValue('mobile_number')
      || empty($form_state->getValue('mobile_number'))
    ) {
      $form_state->setErrorByName('email', $this->t('Enter mobile number.'));
    }
    elseif (strlen($form_state->getValue('mobile_number')) < 10 || strlen($form_state->getValue('mobile_number')) > 10) {
      $form_state->setErrorByName('email', $this->t('The mobile phone must contain 10-14 digits.'));
    }
    elseif (!preg_match('/^[0-9]{10}$/', $form_state->getValue('mobile_number'))) {
      $form_state->setErrorByName('email', $this->t('Mobile number not valid.'));
    }
    elseif (!$form_state->getValue('review')
      || empty($form_state->getValue('review'))
    ) {
      $form_state->setErrorByName('email', $this->t('Enter message.'));
    }
  }

  /**
   * Ajax callback to validate the email field.
   */
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (preg_match('/^[a-z_-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $form_state->getValue('email'))) {
      $css = ['border' => '3px solid green'];
      $response->addCommand(new CssCommand('#edit-email', $css));
      $response->addCommand(new HtmlCommand('.email-valid-message', $this->t('Email ok.')));
    }
    else {
      $css = ['border' => '3px solid red'];
      $response->addCommand(new CssCommand('#edit-email', $css));
      $response->addCommand(new HtmlCommand('.email-valid-message', $this->t('Email not valid.')));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
