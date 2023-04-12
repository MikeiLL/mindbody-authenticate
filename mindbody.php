<?php
/**
 * Mindbody Api Calls
 *
 * This file contains api calls to MZ Mindbody
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


	/**
	 * Get User Token
	 *
	 * Get Oauth token from MBO API.
     *
     * Documentation not linked in MBO API docs, but found here:
     * https://developers.mindbodyonline.com/PlatformDocumentation#post-token
	 *
	 * @since 1.0.0
	 * @access public
	 * @return TODO
	 *
	 */
	function get_oauth_token() {
		$nonce = wp_create_nonce( 'mz_mbo_authenticate_with_api' );
		$id_token = $_POST['id_token'];
		$request_body = array(
			'method'        		=> 'POST',
			'timeout'       		=> 55,
			'httpversion'   		=> '1.0',
			'blocking'      		=> true,
			'headers'       		=> '',
			'body'          		=> [
				'client_id'     => MZ\Core\MzMindbodyApi::$oauth_options['mz_mindbody_client_id'],
				'grant_type'	=> 'authorization_code',
				'scope'         => 'email profile openid offline_access Mindbody.Api.Public.v6 PG.ConsumerActivity.Api.Read',
				'client_secret'	=> MZ\Core\MzMindbodyApi::$oauth_options['mz_mindbody_client_secret'],
				'code'			=> $_POST['code'],
				'redirect_uri'	=> home_url(),
				'nonce'			=> $nonce
			],
			'redirection' 			=> 0,
			'cookies'       => array()
		);
		$response = wp_remote_request(
			"https://signin.mindbodyonline.com/connect/token",
			$request_body
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
			$response_body = json_decode($response['body']);
			if (empty($response_body->access_token)) {
				return false;
			} else {
				return $response_body->access_token;
			}
		}
	}

  /**
	 * Check token with MBO API
	 *
	 * Retrieve the users universal id from MBO API.
	 *
	 * @since 2.9.9
	 */
	function get_universal_id($token) {
		$response = wp_remote_request(
			"https://api.mindbodyonline.com/platform/accounts/v1/me",
			array(
				'method'        		=> 'GET',
				'timeout'       		=> 55,
				'httpversion'   		=> '1.0',
				'blocking'      		=> true,
				'headers'       		=> [
					'API-Key' 			=> MZ\Core\MzMindbodyApi::$basic_options['mz_mbo_api_key'],
					'Authorization' => 'Bearer ' . $token
				],
				'body'          		=> '',
				'redirection' 			=> 0,
				'cookies'       => array()
			)
		);
		$response_body = json_decode($response['body']);
		if (!empty($response_body->id)){
			$this->save_id_and_token($response_body->id, $token);
			$siteID = (int) MZ\Core\MzMindbodyApi::$basic_options['mz_mindbody_siteID'];
			$has_account = false;
			foreach($response_body->businessProfiles as $studio){
				if ( $siteID === $studio->businessId ) {
					$_SESSION['MindbodyAuth']['MBO_USER_Site_ID'] = $studio->profileId;
					$has_account = true;
				}
			}
			if (true || false === $has_account) {
				// Need to register for this site.
				echo "You need to register for this site. \n";
				echo "get_universal_id 1<pre>";
				var_dump($_SESSION);
				echo "</pre>";
				echo $_SESSION['MindbodyAuth']['MBO_USER_Site_ID'];
				echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("need_to_register"));</script>';
			} else {
				echo $_SESSION['MindbodyAuth']['MBO_USER_Site_ID'];
				echo "Got the MBO_USER_Site_ID. Now we can do stuff.";
				echo '<script>if (window.opener) window.opener.dispatchEvent(new Event("authenticated"));</script>';
			}
			//echo '<script>window.close();</script>';
		} else {
			// This should never happen, but just in case:
			echo "No Universal ID";
		}
	}

	/**
	 * Save Universal ID and Token
	 *
	 * Store Oauth token and universal id in $Session.
	 *
	 * @since 2.9.9
	 * @param string $id Universal ID from MBO API.
	 * @param string $token Oauth Token from MBO API.
	 *
	 */
	 function save_id_and_token($universal_id, $token) {
		$current = new \DateTime();
		$current->format( 'Y-m-d H:i:s' );

		$stored_token = array(
			'stored_time' => $current,
			'AccessToken' => $token,
		);

		$_SESSION['MindbodyAuth']['MBO_Public_Oauth_Token'] = $stored_token;
		$_SESSION['MindbodyAuth']['MBO_Universal_ID'] = $universal_id;
	}

 ?>
