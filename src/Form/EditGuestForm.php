<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class EditGuestForm.
 *
 * @package Drupal\guest_book\Form
 */
class EditGuestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_guest_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $query = \Drupal::database()->select('guest_book', 'g');
    $query->fields('g', ['name', 'email', 'mobile', 'message',
      'avatar', 'photo',
    ]);
    $query->condition('id', $form_state->getValue('id-item-edit'));
    $query->execute()->fetchAll();
    $form['#attributes']['class'][] = 'guest-form-edit';

    $form['system_messages'] = [
      '#markup' => '<div id="edit-form-messages"></div>',
    ];

    $form['edit-name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change name:'),
      '#placeholder' => $this->t('From 2 to 32 letters'),
      '#attributes' => [
        'class' => [
          'name-form-edit',
        ],
      ],
    ];

    $form['edit-email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change email:'),
      '#placeholder' => ('user-_@company.'),
      '#attributes' => [
        'class' => [
          'email-form-edit',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'editValidateEmailAjax'],
        'event' => 'input',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
      '#prefix' => '<span class="edit-email-valid-message"></span>',
    ];

    $form['edit-number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Change mobile number:'),
      '#attributes' => [
        'class' => [
          'number-form-edit',
        ],
      ],
    ];
    $form['edit-message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Change message:'),
      '#attributes' => [
        'class' => [
          'message-form-edit',
        ],
      ],
    ];

    $form['edit-avatar'] = [
      '#title' => $this->t('Change avatar:'),
      '#description' => $this->t('Allowed photo format png jpg jpeg/ no more than 2MB'),
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://',
      '#attributes' => [
        'class' => [
          'avatar-form-edit',
        ],
      ],
    ];

    $form['edit-image'] = [
      '#title' => $this->t('Change avatar:'),
      '#description' => $this->t('Allowed photo format png jpg jpeg/ no more than 2MB'),
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
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => [
          'form-submit-edit',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'editAjaxSubmitCallback'],
      ],
    ];

    $form['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'form-cancel-edit',
        ],
      ],
    ];

    $form['id-item-edit'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => [
          'form-id-item-edit',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Ajax callback to validate the email field.
   */
  public function editValidateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (preg_match('/^[a-z_-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $form_state->getValue('edit-email'))) {
      $css = ['border' => '3px solid green'];
      $response->addCommand(new CssCommand('#edit-email--2', $css));
      $response->addCommand(new HtmlCommand('.edit-email-valid-message', $this->t('Email ok.')));
    }
    else {
      $css = ['border' => '3px solid red'];
      $response->addCommand(new CssCommand('#edit-email--2', $css));
      $response->addCommand(new HtmlCommand('.edit-email-valid-message', $this->t('Email not valid.')));
    }
    return $response;
  }

  /**
   * @throws \Exception
   */
  public function editAjaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (strlen($form_state->getValue('edit-name')) < 2) {
      $response->addCommand(new MessageCommand($this->t('Name is too short.'), '#edit-form-messages', ["type" => "error"]));
    }
    elseif (strlen($form_state->getValue('edit-name')) > 100) {
      $response->addCommand(new MessageCommand($this->t('Name is too long.'), '#edit-form-messages', ["type" => "error"]));
    }
    elseif (!preg_match('/^[a-z_-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $form_state->getValue('edit-email'))) {
      $response->addCommand(new MessageCommand($this->t('Email not valid.'), '#edit-form-messages', ["type" => "error"]));
    }
    elseif (!$form_state->getValue('edit-number')
      || empty($form_state->getValue('edit-number'))
    ) {
      $response->addCommand(new MessageCommand($this->t('Enter mobile number.'), '#edit-form-messages', ['type' => 'error']));
    }
    elseif (strlen($form_state->getValue('edit-number')) < 10 || strlen($form_state->getValue('edit-number')) > 10) {
      $response->addCommand(new MessageCommand($this->t('The mobile phone must contain 10-14 digits.'), '#edit-form-messages', ['type' => 'error']));
    }
    elseif (!preg_match('/^[0-9]{10}$/', $form_state->getValue('edit-number'))) {
      $response->addCommand(new MessageCommand($this->t('Mobile number not valid.'), '#edit-form-messages', ['type' => 'error']));
    }
    elseif (!$form_state->getValue('edit-message')
      || empty($form_state->getValue('edit-message'))
    ) {
      $response->addCommand(new MessageCommand($this->t('Enter message.'), '#edit-form-messages', ['type' => 'error']));
    }
    elseif ($form_state->getValue('edit-image') == NULL && $form_state->getValue('edit-avatar') == NULL) {
      $query = \Drupal::database()->update('guest_book');
      $query->fields([
        'name' => $form_state->getValue('edit-name'),
        'email' => $form_state->getValue('edit-email'),
        'mobile' => $form_state->getValue('edit-number'),
        'message' => $form_state->getValue('edit-message'),
      ]);
      $query->condition('id', $form_state->getValue('id-item-edit'));
      $query->execute();
      $response->addCommand(new MessageCommand($this->t('Information was changed.'), '#edit-form-messages', ["type" => "status"]));
    }
    elseif ($form_state->getValue('edit-avatar') == NULL) {
      $query = \Drupal::database()->update('guest_book');
      $fid = $form_state->getValue('edit-image');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $query->fields([
        'name' => $form_state->getValue('edit-name'),
        'email' => $form_state->getValue('edit-email'),
        'mobile' => $form_state->getValue('edit-number'),
        'message' => $form_state->getValue('edit-message'),
        'photo' => $url,
      ]);
      $query->condition('id', $form_state->getValue('id-item-edit'));
      $query->execute();
      $response->addCommand(new MessageCommand($this->t('Information was changed.'), '#edit-form-messages', ["type" => "status"]));
    }
    elseif ($form_state->getValue('edit-image') == NULL) {
      $query = \Drupal::database()->update('guest_book');
      $fid = $form_state->getValue('edit-avatar');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $query->fields([
        'name' => $form_state->getValue('edit-name'),
        'email' => $form_state->getValue('edit-email'),
        'mobile' => $form_state->getValue('edit-number'),
        'message' => $form_state->getValue('edit-message'),
        'avatar' => $url,
      ]);
      $query->condition('id', $form_state->getValue('id-item-edit'));
      $query->execute();
      $response->addCommand(new MessageCommand($this->t('Information was changed.'), '#edit-form-messages', ["type" => "status"]));
    }
    else {
      $query = \Drupal::database()->update('guest_book');
      $fid = $form_state->getValue('edit-avatar', 'edit-image');
      $file = File::load($fid[0]);
      $file->setPermanent();
      $file->save();
      $uri = $file->getFileUri();
      $url = file_create_url($uri);
      $query->fields([
        'name' => $form_state->getValue('edit-name'),
        'email' => $form_state->getValue('edit-email'),
        'mobile' => $form_state->getValue('edit-number'),
        'message' => $form_state->getValue('edit-message'),
        'avatar' => $url,
        'photo' => $url,
      ]);
      $query->condition('id', $form_state->getValue('id-item-edit'));
      $query->execute();
      $response->addCommand(new MessageCommand($this->t('Information was changed.'), '#edit-form-messages', ["type" => "status"]));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
