<?php
/**
 * Mindbody Authentication
 *
 * This file contains all the actions and functions to create the admin dashboard sections.
 * for the Access and MBO v5 tabs in MZ MBO Settings page.
 *
 *
 * @package MZMBOAUTH
 */
namespace MZoo\MzMboAuth;

use MZoo\MzMindbody as MZ;
$wposa_obj = MZ\Core\MzMindbodyApi::$settings_page::$wposa_obj;
$wposa_obj->add_section(
    array(
        'id'    => 'oauth_options',
        'title' => __( 'Oauth', 'mz-mbo-auth' ),
    )
);

$wposa_obj->add_field(
  'oauth_options',
  array(
    'id'      => 'mz_mindbody_client_secret',
    'type'    => 'text',
    'name'    => __( 'Client Secret', 'mz-mbo-auth' ),
    'desc'    => '(Request Oauth on MBO developer account.)',
    'default' => __( '', 'mz-mbo-auth' ),
    'placeholder' => ""
  )
);

// Field: Client ID.
$wposa_obj->add_field(
  'oauth_options',
  array(
    'id'      => 'mz_mindbody_client_id',
    'type'    => 'text',
    'name'    => __( 'Client ID', 'mz-mbo-auth' ),
    'desc'    => '(Request Oauth on MBO developer account.)',
    'default' => __( '', 'mz-mbo-auth' ),
    'placeholder' => "xxxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx" // UUID format
  )
);


// Field: Instructions.
$wposa_obj->add_field(
  'oauth_options',
  array(
    'id'      => 'oauth_instructions',
    'type'    => 'html',
    'name'    => __( 'Instructions', 'mz-mbo-auth' ),
    'desc'    => oauth_instructions(),
  )
);

function oauth_instructions() {
  $html =  '<p>' . __( 'You will now need to create a Mindbody Oauth Client', 'mz-mbo-auth' ) . '</p>';
  $html .= '<p>' . __( 'To do this, log into your Mindbody account and go to the Developer tab. Click on the "Create New Client" button.', 'mz-mbo-auth' ) . '</p>';
  $html .= '<p>' . sprintf( __('Callback return url for this site appears to be %1$s.', 'mz-mbo-auth'), home_url()) . '</p>';
  // https://developers.mindbodyonline.com/Account/Credentials
  return $html;
}

?>
