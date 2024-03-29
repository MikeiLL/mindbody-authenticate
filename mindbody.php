<?php
/**
 * Mindbody Api Calls
 *
 * This file contains api calls to MZ Mindbody
 *
 * @package MZMBOAUTH
 */

namespace MZoo\MzMboAuth;

use MZ;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class MzMboApiCalls {

  /**
   * Mindbody Credentials Options
   */
  public $mindbody_credentials;

  /**
   * Stored Oauth Token
   */
  public $stored_token;

  /**
   * Customer Has Studio Account
   */
  public $customer_has_studio_account;

  /**
   * Constructor
   *
   * @since 1.0.0
   * @access public
   */

  public function __construct() {
    $this->mindbody_credentials = get_option( 'mzmbo_oauth_options' );
  }
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
	public function get_oauth_token() {
    /*
    If a client that is logging in doesn't have an OAuth login but a local login,
    then they will be asked to verify their email. Once they verify the email it
    will then create that OAuth login for them.
    */
		$nonce = wp_create_nonce( 'mz_mbo_authenticate_with_api' );
		$id_token = $_POST['id_token'];
		$request_body = array(
			'method'        		=> 'POST',
			'timeout'       		=> 55,
			'httpversion'   		=> '1.0',
			'blocking'      		=> true,
			'headers'       		=> '',
			'body'          		=> [
				'client_id'     => $this->mindbody_credentials['mz_mindbody_client_id'],
				'grant_type'	  => 'authorization_code',
				'scope'         => 'email profile openid offline_access Mindbody.Api.Public.v6 PG.ConsumerActivity.Api.Read',
				'client_secret'	=> $this->mindbody_credentials['mz_mindbody_client_secret'],
				'code'			    => $_POST['code'],
				'redirect_uri'	=> home_url() . '/mzmbo/authenticate',
				'nonce'			    => $nonce
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
        $this->save_oauth_token($response_body->access_token);
				return $response_body->access_token;
			}
		}
	}

  /**
	 * Check token with MBO API
	 *
	 * Retrieve the users universal id from MBO API.
	 *
	 * @since 1.0.0
   * @param string $token
   * @return object|false $response_body
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
					'API-Key' 			=> \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mbo_api_key'],
					'Authorization' => 'Bearer ' . $token
				],
				'body'          		=> '',
				'redirection' 			=> 0,
				'cookies'       => array()
			)
		);
		$response_body = json_decode($response['body']);
    if (empty($response_body->id)) {
      return false;
    }
    $this->save_universal_id($response_body->id);
    $siteID = (int) \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mindbody_siteID'];
    $this->customer_has_studio_account = false;
    foreach($response_body->businessProfiles as $studio){
      if ( $siteID === $studio->businessId ) {
        $_SESSION['MindbodyAuth']['MBO_USER_Site_ID'] = $studio->profileId;
        $this->customer_has_studio_account = true;
      }
    }
    return $response_body;
	}

  /**
   * Request Studio Registration
   *
   *  DEPRECATED
   * @since 1.0.0
   * @param object $response_body
   */
  public function request_studio_registration($response_body){
    echo '<script>window.close();</script>';

    return;

    $universal_fields = ['firstName', 'lastName', 'email'];

		$client = new \MZoo\MzMindbody\Client\RetrieveClient();
		$fields = $client->get_signup_form_fields();
		echo "<dialog id=studio_registration_form>";
		echo "<h3>" . __("Looks like you aren't registered with our studio.", "mz-mindbody-api") . "</h3>";
		echo "<form method=POST>";
		echo "<ul>";
		foreach($fields as $f){
			echo '<li>';
				echo $f . ' <input name="' . $f . '" REQUIRED>';
			echo '</li>';
		}
		echo "</ul>";
		echo '<input type=hidden name="mz_mbo_action" value="true">';
		echo '<input type=SUBMIT value="' . __("Register Now", "mz-mindbody-api") . '">';
		echo "</form></dialog>";
    echo "<h3>Looks like you aren't registered with our studio.</h3>";
							echo "<form method=POST>";
							echo "  <ul>";
							foreach($fields as $f){
								$userField = lcfirst($f);
								echo '  <li>';
								if (property_exists($response_body, $userField)){
									echo $f . ' <input name="' . $f . '" value="' . $response_body->$userField. '">';
								} else {
									echo $f . ' <input name="' . $f . '">';
								}

								echo '  </li>';
							}
							echo "  </ul>";
							echo '  <input type=SUBMIT value="Register Now">';
							echo "</form>";
  }

	/**
	 * Save Oauth Token
	 *
	 * Store Oauth token in $_SESSION..
	 *
	 * @since 1.0.0
	 * @param string $token Oauth Token from MBO API.
	 *
	 */
  public function save_oauth_token($token) {
		$current = new \DateTime();

		$this->stored_token = array(
			'stored_time' => $current->format( 'Y-m-d H:i:s' ),
			'AccessToken' => $token,
		);

    $_SESSION['MindbodyAuth'] = empty($_SESSION['MindbodyAuth']) ? array() : $_SESSION['MindbodyAuth'];

		$_SESSION['MindbodyAuth']['MBO_Public_Oauth_Token'] = $this->stored_token;
	}

  	/**
	 * Save Universal ID
	 *
	 * Store universal id in $_SESSION.
	 *
	 * @since 1.0.0
	 * @param string $id Universal ID from MBO API.
	 *
	 */
  public function save_universal_id($universal_id) {
		$_SESSION['MindbodyAuth']['MBO_Universal_ID'] = $universal_id;
	}

  /**
   * Register User with Studio
   *
   * IMPORTANT API NOTES:
   * The documentation on that endpoint is not totally accurate. The "businessId" should be used in the body of the request
   * instead of the Header. Leaving that out of the request body will return an error about the business ID.
   * Another thing that we found during testing is that passing information from the Identity account, email, first name, last
   * name, also seems to return an error. That information should populate automatically, so the request would only need the
   * required fields that the business asks for, such as "address_line_1", "state", "city", and "postal_code". The property
   * "names" will be underscored like that and be all lower case.
   *
   * @since 1.0.0
   * @param array $params from user form to submit to Mindbody.
   */
  public function register_user_with_studio( $params ) {
    $contactProps = [];
    foreach($params as $k=>$v) {
      $contactProps[] = ['name' => lcfirst($k), 'value' => $v];
    }
    $request_body = array(
      'method'        		=> 'POST',
      'timeout'       		=> 55,
      'httpversion'   		=> '1.0',
      'blocking'      		=> true,
      'headers'       		=> [
        'API-Key' 				=> \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mbo_api_key'],
        'Authorization'		=> 'Bearer ' . $_SESSION['MindbodyAuth']['MBO_Public_Oauth_Token']['AccessToken'],
        'Content-Type'		=> 'application/json',
      ],
      'body'							=> json_encode([
          "userId" => $_SESSION['MindbodyAuth']['MBO_Universal_ID'],
          'BusinessId'      => \MZoo\MzMindbody\Core\MzMindbodyApi::$basic_options['mz_mindbody_siteID'],
          // Can we count on form containing all required fields?
          "contactProperties" => $contactProps
          ]),
      'redirection' 			=> 0,
      'cookies'						=> array()
    );

    /* I believe the following list shows all possibilities for the formatting of such fields through the OAuth API:

      external_id
      first_name
      last_name
      email
      middle_name
      address_line_1
      address_line_2
      city
      state
      country
      postal_code
      home_phone_number
      work_phone_number
      mobile_phone_number
      birth_date
      referred_by
      emergency_contact_name
      emergency_contact_email
      emergency_contact_phone
      emergency_contact_relationship
      gender

      –Raymond
      */

    // This will create a Studio Specific Account for user based on MBO Universal Account
    // https://api.mindbodyonline.com/platform/index.html
    $response = wp_remote_request(
      "https://api.mindbodyonline.com/platform/contacts/v1/profiles",
      $request_body
    );

    if (is_wp_error($response)) {
      return array( 'error' => $response->get_error_message() );
    }
    switch ($response['response']['code']) {
      case 200:
        // Success
        return ["success" => "User successfully registered with studio."];
        break;
      case 409:
        // Duplicate
        return ["error" => "User already registered with studio."];
        break;
      default:
        // Error
        return ["error" => $response['body']];
        break;
    }

  }
}

 ?>
