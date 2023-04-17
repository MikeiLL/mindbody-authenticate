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

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 */
function enqueue_scripts() {

  wp_register_script( 'mz_user_tools', PLUGIN_NAME_URL . 'user-tools.js', array('jquery', 'mz_display_schedule_script'), PLUGIN_VERSION, true );
  wp_enqueue_script( 'mz_user_tools' );

  $oauth_options = get_option('mzmbo_oauth_options');

  $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

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

  $params = array(
    'ajaxurl'                => admin_url( 'admin-ajax.php', $protocol ),
    'user_tools_nonce' => wp_create_nonce( 'mz_user_tools' ),
    'with'                   => __( 'with', 'mz-mindbody-api' ),
    'account'                => \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mindbody_siteID'],
    'login'                  => $translated_strings['login'],
    'signup'                 => $translated_strings['sign_up'],
    'confirm_signup'          => $translated_strings['confirm_signup'],
    'logout'                 => $translated_strings['logout'],
    'signup_heading'         => $translated_strings['signup_heading'],
    'mbo_oauth_url'          => "https://signin.mindbodyonline.com/connect/authorize?" . http_build_query($mbo_oauth_url_body),
  );
  wp_localize_script( 'mz_user_tools', 'user_tools', $params );
}

 ?>
