<?php
/**
 * Mindbody Authentication
 *
 * This file contains request handling for MZ Mindbody
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// TODO do this the WordPress way
if ($_SERVER["REQUEST_URI"] == "/mzmbo/authenticate") {
  check_post_requests();
}

function check_post_requests() {
  if (empty($_POST)) {
    return;
  }
}
 ?>
