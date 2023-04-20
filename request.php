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
  add_action( 'init', __NAMESPACE__ . '\\check_post_requests', 1 );
}

/**
 * Check for POST requests and direct to appropriate method.
 *
 * @since 1.0.0
 */
function check_post_requests() {
  if (empty($_POST)) {
    return;
  }
		// Returns from Oauth request
  if (!empty($_POST['id_token']) &&
        !empty($_POST['scope']) &&
        !empty($_POST['code']) &&
        !empty($_POST['session_state'])){

          $mzmbo = new MzMboApiCalls();

          $access_token = $mzmbo->get_oauth_token();

          if (false !== $access_token) {
            // Clear Post token so this only runs once.
            $_POST['id_token'] = "";
            $response = $mzmbo->get_universal_id($access_token);
            if (!empty($mzmbo->customer_has_studio_account)) {
              echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("authenticated"));</script>';
            } else {
              echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("need_to_register"));</script>';
            }
            echo '<script>window.close();</script>';
          }
  }
}



 ?>
