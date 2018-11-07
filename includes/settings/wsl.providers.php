<?php
/*!
* WordPress Social Login
*
* http://miled.github.io/wordpress-social-login/ | https://github.com/miled/wordpress-social-login
*  (c) 2011-2014 Mohamed Mrassi and contributors | http://wordpress.org/plugins/wordpress-social-login/
*/

/**
* List of supported providers by Hybridauth Library 
*
* If you need even more of the Hybridauth additional providers, then you need to download additional providers package 
* at https://github.com/hybridauth/hybridauth/releases and then copy needed additional providers to the library.
*
* For instance, to get XING provider working you need to copy 'hybridauth-identica/Providers/XING.php' 
* to 'plugins/wordpress-social-login/hybridauth/Hybrid/Providers/XING.php' and then add it to 
* $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG :
*
*   ARRAY( 
*      'provider_id'         : Alphanumeric(a-zA-Z0-9) code/name of a provider
*      'provider_name'       : Real provider name.
*      'require_client_id'   : If a provider uses OAuth 2. Defaults to false.
*      'callback'            : If the provide require to set a callback url. Defaults to false.
*      'new_app_link'        : If the provide require to create a new application on his developer site.
*      'default_api_scope'   : Default scope requested
*      'default_network'     : If true, it will shows up by default on Admin > WordPress Social Login > Networks
*      'cat'                 : Provider category. (for future use)
*   ),
*
* After that you just need to configure your application ID, private and secret keys at the plugin
* configuration pages (wp-admin/options-general.php?page=wordpress-social-login).
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

$WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG = ARRAY(
	ARRAY(
		"provider_id"       => "Google",
		"provider_name"     => "Google",
		"callback"          => true,
		"require_client_id" => true,
		"new_app_link"      => "https://console.developers.google.com", 
// 		"default_api_scope" => "profile https://www.googleapis.com/auth/plus.profile.emails.read", 
		"default_api_scope" => "profile https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/plus.profile.emails.read", 
		
		"default_network"   => true,
		"cat"               => "socialnetworks",
	),
);

// --------------------------------------------------------------------
