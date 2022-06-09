<?php

namespace Drupal\guest_book\Controller;

/**
 * @file
 * Contains \Drupal\guest_book\Controller\GuestBookController.
 *
 * @return
 */
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the guestbook module.
 */
class GuestBookController extends ControllerBase {

  /**
   * Returns a page.
   *
   * @return array
   *   A renderable array.
   */
  public function content() {
    $guestBookForm = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\GuestBookForm');
    $deleteGuestForm = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\DeleteGuestForm');
    $editGuestForm = \Drupal::formBuilder()->getForm('Drupal\guest_book\Form\EditGuestForm');
    $block_manager = \Drupal::service('plugin.manager.block');
    $config = [];
    $guest_book_item_block = $block_manager->createInstance('guest_book_items', $config);
    return [
      '#theme' => 'guest_book_template',
      '#form' => $guestBookForm,
      '#guest_book' => $guest_book_item_block->build(),
      '#DeleteGuestForm' => $deleteGuestForm,
      '#EditGuestForm' => $editGuestForm,
    ];
  }

}
