<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DeleteGuestForm.
 *
 * @package Drupal\guest_book\Form
 */
class DeleteGuestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_guest_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'form-delete';

    $form['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $this->t('Do you want delete item?'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#attributes' => [
        'class' => [
          'form-submit-delete',
        ],
      ],
    ];
    $form['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'class' => [
          'form-cancel',
        ],
      ],
    ];
    $form['id-item'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => [
          'form-id-item',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database()->delete('guest_book');
    $idValue = $form_state->getValue('id-item');
    $query->condition('id', $idValue);
    $query->execute();
    \Drupal::messenger()->addStatus($this->t('Deleted item.'));
  }

}
