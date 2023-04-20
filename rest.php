<?php
/**
 * Mindbody Authentication
 *
 * This file contains rest endpoint functions for MZ Mindbody
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', __NAMESPACE__ . '\add_endpoints' );

/**
 * Examples
 *
 * @since 1.0.0
 *
 * @return void
 */
function add_endpoints() {
  add_simple_route();
  register_user_with_studio();
}

/**
 * Register User With Studio
 *
 * Make an instance of this class somewhere, then
 * call this method and test on the command line with
 * `curl http://example.com/wp-json/mindbody-auth/v1/registeruser`
 */
function register_user_with_studio() {
  // An example with 0 parameters.
  register_rest_route(
    'mindbody-auth/v1',
    '/registeruser',
    array(
      'methods'             => \WP_REST_Server::CREATABLE,
      'callback'            => __NAMESPACE__ . '\add_user_to_studio',
      'permission_callback' => '__return_true'
    )
  );
}

add_filter( 'rest_authentication_errors', function( $result ) {
  // If a previous authentication check was applied,
  // pass that result along without modification.
  if ( true === $result || is_wp_error( $result ) ) {
    return $result;
  }

  // Shortcut the builtin WP Authorization as per
  // https://developer.wordpress.org/rest-api/frequently-asked-questions/#require-authentication-for-all-requests
  // and https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
  // If people send unauthorized requests, empty $_SESSIONs will be generated
  return true;
});

/**
 * Add User to Studio
 *
 * @since 1.0.0
 *
 * @param array $request Values.
 *
 * @return array
 */
function add_user_to_studio( $request ) {
  $params = $request->get_body_params();
  $mbo = new MzMboApiCalls();
  $mbo->register_user_with_studio($params);
  return array( 'result' => 'Salaam. I can hear you.' );
}

/**
 * Simple Route Example
 *
 * Make an instance of this class somewhere, then
 * call this method and test on the command line with
 * `curl http://example.com/wp-json/mindbody-auth/v1/simple`
 */
function add_simple_route() {
  // An example with 0 parameters.
  register_rest_route(
    'mindbody-auth/v1',
    '/simple',
    array(
      'methods'             => 'GET',
      'callback'            => __NAMESPACE__ . '\simple_route_example'
    )
  );
}

/**
 * Simple Route Example
 *
 * @since 1.0.0
 *
 * @param array $data Values.
 *
 * @return array
 */
function simple_route_example() {
  return array( 'result' => 'Salaam. I can hear you.' );
}

 ?>
