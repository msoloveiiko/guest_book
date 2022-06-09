<?php

namespace Drupal\guest_book\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a guest_book_items.
 *
 * @Block(
 *   id = "guest_book_items",
 *   admin_label = @Translation("GuestBook items")
 * )
 */
class guest_book_items extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $db = \Drupal::database();
    $query = $db->select('guest_book', 'g');
    $query->fields('g', ['id', 'name', 'email', 'mobile',
      'message', 'avatar', 'photo', 'date',
    ]);
    $query->orderBy('date', 'DESC');
    $result = $query->execute()->fetchAll();
    return [
      '#theme' => 'guest-book-items',
      '#items' => $result,
    ];
  }

}
