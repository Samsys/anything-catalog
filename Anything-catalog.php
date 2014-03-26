<?php
/**
 * Anything WordPress Catalog
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Anything_Catalog
 * @author    Ricardo Correia <me@rcorreia.com>, ...
 * @license   GPL-2.0+
 * @link      http://Anything.pt
 * @copyright 2014 - @rfvcorreia, @samsyspt
 *
 * @wordpress-plugin
 * Plugin Name:       Anything Catalog
 * Plugin URI:        http://Anything.pt
 * Description:       A catalog for WordPress
 * Version:           1.0.0
 * Author:            Ricardo Correia, Anything
 * Author URI:        http://profiles.wordpress.org/ricardocorreia
 * Text Domain:       Anything-catalog
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/rfvcorreia/Anything-catalog
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-Anything-catalog.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/widgets/class-Anything-catalog-widgets.php' );
/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
register_activation_hook( __FILE__, array( 'Anything_Catalog', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Anything_Catalog', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'Anything_Catalog', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-Anything-catalog-admin.php' );
	add_action( 'plugins_loaded', array( 'Anything_Catalog_Admin', 'get_instance' ) );

}
