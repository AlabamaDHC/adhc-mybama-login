<?php
/*
Plugin Name: ADHC MyBama Login
Plugin URI: 
Description: Allow students to login using their MyBama login.
Version: 1.0.9
Author: Tyler Grace (ADHC)
Author URI: 
Domain Path: /languages
GitHub Plugin URI: ADHC/adhc-mybama-login
*/

/*
*
*  Hi and thanks for taking the time to check out WSL code.
*
*  Please, don't hesitate to:
*
*   - Report bugs and issues.
*   - Contribute: code, reviews, ideas and design.
*   - Point out stupidity, smells and inconsistencies in the code.
*   - Criticize.
*
*  If you want to contribute, please consider these general "guide lines":
*
*   - Small patches will be always welcome. Large changes should be discussed ahead of time.
*   - That said, don't hesitate to delete code that doesn't make sense or looks redundant.
*   - Feel free to create new functions and files when needed.
*   - Avoid over-commenting, unless you find it necessary.
*   - Avoid using 'switch' and 'for'. I hate those.
*
*  Coding Style :
*
*   - Readable code.
*   - Clear indentations (tabs: 8-char indents).
*   - Same name convention of WordPress: those long long and self-explanatory functions and variables.
*
*  To keep the code accessible to everyone and easy to maintain, WordPress Social Login is programmed in
*  procedural PHP and will be kept that way.
*
*  If you have fixed, improved or translated something in WSL, Please consider contributing back to the project
*  by submitting a Pull Request at https://github.com/miled/wordpress-social-login
*
*  Grep's user, read below. Keywords stuffing:<add_action|do_action|add_filter|apply_filters>
*  If you are here just looking for the hooks, then refer to the online Developer API. If it wasn't possible to
*  achieve some required functionality in a proper way through the already available and documented WSL hooks,
*  please ask for support before resorting to hacks. WSL internals are not to be used.
*  http://miled.github.io/wordpress-social-login/documentation.html
*
*  If you want to translate this plugin into your language (or to improve the current translations), you can
*  join in the ongoing effort at https://www.transifex.com/projects/p/wordpress-social-login/
*
*  Peace.
*
*/

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

@session_start();

global $WORDPRESS_SOCIAL_LOGIN_VERSION;
global $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG;
global $WORDPRESS_SOCIAL_LOGIN_COMPONENTS;
global $WORDPRESS_SOCIAL_LOGIN_ADMIN_TABS;

$WORDPRESS_SOCIAL_LOGIN_VERSION = "2.2.3";

$_SESSION["wsl::plugin"] = "WordPress Social Login " . $WORDPRESS_SOCIAL_LOGIN_VERSION;

// --------------------------------------------------------------------

/**
* This file might be used to :
*     1. Redefine WSL constants, so you can move WSL folder around.
*     2. Define WSL Pluggable PHP Functions. See http://miled.github.io/wordpress-social-login/developer-api-functions.html
*     5. Implement your WSL hooks.
*/
if( file_exists( WP_PLUGIN_DIR . '/wp-social-login-custom.php' ) )
{
	include_once( WP_PLUGIN_DIR . '/wp-social-login-custom.php' );
}

// --------------------------------------------------------------------

/**
* Define WSL constants, if not already defined
*/
defined( 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH' ) 
	|| define( 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH', WP_PLUGIN_DIR . '/adhc-mybama-login' );

defined( 'WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL' ) 
	|| define( 'WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL', plugins_url() . '/adhc-mybama-login' );

defined( 'WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL' ) 
	|| define( 'WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL', WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . '/hybridauth/' );

// --------------------------------------------------------------------

/**
* Check for Wordpress 3.0
*/
function wsl_activate()
{
	if( ! function_exists( 'register_post_status' ) )
	{
		deactivate_plugins( basename( dirname( __FILE__ ) ) . '/' . basename (__FILE__) );

		wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", 'adhc-mybama-login' ) );
	}
}

register_activation_hook( __FILE__, 'wsl_activate' );

// --------------------------------------------------------------------

/**
* Attempt to install/migrate/repair WSL upon activation
*
* Create wsl tables
* Migrate old versions
* Register default components
*/
function wsl_install()
{
	wsl_database_install();

	wsl_update_compatibilities();

	wsl_register_components();
}

register_activation_hook( __FILE__, 'wsl_install' );

// --------------------------------------------------------------------

/**
* Add a settings to plugin_action_links
*/
function wsl_add_plugin_action_links( $links, $file )
{
	static $this_plugin;

	if( ! $this_plugin )
	{
		$this_plugin = plugin_basename( __FILE__ );
	}

	if( $file == $this_plugin )
	{
		$wsl_links  = '<a href="options-general.php?page=adhc-mybama-login">' . __( "Settings" ) . '</a>';

		array_unshift( $links, $wsl_links );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'wsl_add_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------

/**
* Add faq and user guide links to plugin_row_meta
*/
function wsl_add_plugin_row_meta( $links, $file )
{
	static $this_plugin;

	if( ! $this_plugin )
	{
		$this_plugin = plugin_basename( __FILE__ );
	}

	if( $file == $this_plugin )
	{
		$wsl_links = array(
			'<a href="http://miled.github.io/wordpress-social-login/">'             . _wsl__( "Docs"             , 'wordpress-social-login' ) . '</a>',
			'<a href="http://miled.github.io/wordpress-social-login/support.html">' . _wsl__( "Support"          , 'wordpress-social-login' ) . '</a>',
			'<a href="https://github.com/miled/wordpress-social-login">'            . _wsl__( "Fork me on Github", 'wordpress-social-login' ) . '</a>',
		);

		return array_merge( $links, $wsl_links );
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'wsl_add_plugin_row_meta', 10, 2 );

// --------------------------------------------------------------------

/**
* Loads the plugin's translated strings.
*
* http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
*/
if( ! function_exists( 'wsl_load_plugin_textdomain' ) )
{
	function wsl_load_plugin_textdomain()
	{
		load_plugin_textdomain( 'wordpress-social-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
}

add_action( 'plugins_loaded', 'wsl_load_plugin_textdomain' );

// --------------------------------------------------------------------

/**
* _e() wrapper
*/
function _wsl_e( $text, $domain )
{
	echo __( $text, $domain );
}

// --------------------------------------------------------------------

/**
* __() wrapper
*/
function _wsl__( $text, $domain )
{
	return __( $text, $domain );
}

// -------------------------------------------------------------------- 

/* includes */

# WSL Setup & Settings
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/settings/wsl.providers.php'            ); // List of supported providers (mostly provided by hybridauth library) 
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/settings/wsl.database.php'             ); // Install/Uninstall WSL database tables
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/settings/wsl.initialization.php'       ); // Check WSL requirements and register WSL settings
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/settings/wsl.compatibilities.php'      ); // Check and upgrade WSL database/settings (for older versions)

# Services & Utilities
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.authentication.php'       ); // Authenticate users via social networks. <- that's the most important script
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.mail.notification.php'    ); // Emails and notifications
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.user.avatar.php'          ); // Display users avatar
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.user.data.php'            ); // User data functions (database related)
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.utilities.php'            ); // Unclassified functions & utilities
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/services/wsl.watchdog.php'             ); // WSL logging agent

# WSL Widgets & Front-end interfaces 
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/widgets/wsl.auth.widgets.php'          ); // Authentication widget generators (where WSL widget/icons are displayed)
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/widgets/wsl.complete.registration.php' ); // Force users to complete their profile after they register
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/widgets/wsl.users.gateway.php'         ); // Planned for WSL 2.3. Accounts linking + Profile Completion
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/widgets/wsl.error.pages.php'           ); // Generate WSL notices end errors pages
require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/widgets/wsl.loading.screens.php'       ); // Generate WSL loading screens

# WSL Admin interfaces
if( is_admin() )
{
	require_once( WORDPRESS_SOCIAL_LOGIN_ABS_PATH . '/includes/admin/wsl.admin.ui.php'        ); // The entry point to WSL Admin interfaces 
}

// --------------------------------------------------------------------



function adhc_mybama_login_base_config()
{ 
    update_option('wsl_settings_authentication_widget_css', '');
    // update_option('wsl_settings_bouncer_new_users_restrict_domain_list', 'crimson.ua.edu');
    // update_option('wsl_settings_bouncer_new_users_restrict_email_list ', 'thgrace@crimson.ua.edu');
    update_option('wsl_settings_welcome_panel_enabled ', '0');
    // update_option('wsl_settings_force_redirect_url ', '1');
    update_option('wsl_settings_connect_with_label ', 'Student login via myBama');
    update_option('wsl_settings_users_avatars', '1');
    // update_option('wsl_settings_use_popup', '1');
    update_option('wsl_settings_widget_display', '3');
    // update_option('wsl_settings_bouncer_new_users_restrict_email_enabled', '1');
    update_option('wsl_settings_bouncer_new_users_restrict_profile_enabled', '2');
    update_option('wsl_settings_Facebook_enabled', '0');
    update_option('wsl_settings_Google_enabled', '1');
    update_option('wsl_settings_Twitter_enabled', '0');
    update_option('wsl_components_core_enabled', '1');
    update_option('wsl_components_networks_enabled', '1');
    update_option('wsl_components_login-widget_enabled', '1');
    update_option('wsl_components_bouncer_enabled', '1');
    update_option('wsl_components_users_enabled', '1');
    update_option('wsl_settings_social_icon_set', 'none');
    update_option('wsl_settings_users_notification', '0');

    /* update_option('', ''); */
    
}

add_action('admin_enqueue_scripts', 'adhc_mybama_login_base_config');
