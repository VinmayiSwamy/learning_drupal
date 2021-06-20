<?php
namespace Drupal\custom_module\Controller;
class CustomController {
  public function custom() {
    return array(
      '#markup' => 'Welcome to our Website.'
    );
  }
}