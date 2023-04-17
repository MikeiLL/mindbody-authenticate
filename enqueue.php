<?php
/**
 * Enqueue scripts and styles.
 *
 * This file contains request handling for MZ Mindbody
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;


// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
add_filter('script_loader_tag', __NAMESPACE__ . '\add_type_attribute' , 10, 3);

// Load our script as a module, so we can use import.
function add_type_attribute($tag, $handle, $src) {
  // if not our script, do nothing and return original $tag
  if ( 'mz_user_tools' !== $handle ) {
      return $tag;
  }
  // change the script tag by adding type="module" and return it.
  $tag = '<script type="module" id="' . $handle . '-js" src="' . esc_url( $src ) . '"></script>';
  return $tag;
}

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 */
function enqueue_scripts() {

  wp_register_script( 'mz_user_tools', PLUGIN_NAME_URL . 'user-tools.js', array('jquery', 'mz_display_schedule_script'), PLUGIN_VERSION, true );
  wp_enqueue_script( 'mz_user_tools' );

  $oauth_options = get_option('mzmbo_oauth_options');

  $translated_strings = \MZoo\MzMindbody\MZMBO()->i18n->get();

  $mbo_oauth_url_body = [
    'response_mode' => 'form_post',
    'response_type' => 'code id_token',
    "scope" => "email openid profile Platform.Contacts.Api.Write Platform.Contacts.Api.Read Platform.Accounts.Api.Read Mindbody.Api.Public.v6 Platform.ProductInventory.Api.Read Platform.ProductInventory.Api.Write",
    'client_id'              => $oauth_options['mz_mindbody_client_id'],
    'redirect_uri'           => home_url() . '/mzmbo/authenticate',
    'nonce'                  => wp_create_nonce( 'mz_mbo_authenticate_with_api' ),
    'subscriberId'	         => \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mindbody_siteID']
  ];

  $client = new \MZoo\MzMindbody\Client\RetrieveClient();
  $required_client_fields = $client->get_signup_form_fields();

  $params = array(
    'user_tools_nonce' => wp_create_nonce( 'mz_user_tools' ),
    'loggedMBO'        => false ? 'false' : 'true',
    'required_fields'  => json_encode($required_client_fields),
    'mbo_oauth_url'    => "https://signin.mindbodyonline.com/connect/authorize?" . http_build_query($mbo_oauth_url_body),
  );
  wp_localize_script( 'mz_user_tools', 'user_tools', $params );
}

 ?>
