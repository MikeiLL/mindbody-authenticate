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
            if ($mzmbo->customer_has_studio_account) {
              echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("authenticated"));</script>';
            } else {
              echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("need_to_register"));</script>';
            }
            echo '<script>window.close();</script>';
          }
  } else if (!empty($_POST['mz_mbo_action']) && // Make sure it's us.
        !empty($_POST['Email']) &&
        !empty($_POST['FirstName']) &&
        !empty($_POST['LastName']) ) {
        // Looks like we want to register this user
        // This we must delay until $_SESSION is set.
        // Imitating the way EDD does it around line 100 in their actions.php file.

        // add_action('template_redirect', array($this, 'register_user_with_studio'));
				// Looks like we want to register this user
				echo "<h2>REGISTERING YOU WITH STUDIO.</h2>";
        $mzmbo->register_user_with_studio();
			}
}



 ?>
