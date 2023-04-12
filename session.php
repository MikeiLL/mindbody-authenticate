<?php
/**
 * Mindbody Authentication
 *
 * This file contains $_SESSION interface for MZ Mindbody Authentication.
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_action( 'init', __NAMESPACE__ . '\\start_session', -2 );

function start_session() {
  if (session_status() !== PHP_SESSION_ACTIVE ) {
      session_start();
  }
}
 ?>
