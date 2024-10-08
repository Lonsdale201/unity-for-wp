<?php
/**
 * Plugin Name: Unity WebGL Integration
 * Description: Integrates Unity WebGL content into WordPress using a shortcode.
 * Version: 1.1
 * Author: Soczó Kristóf
 * Text Domain: unity-webgl-integration
 * Licence: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'UNITY_WEBGL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNITY_WEBGL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5p0\PucFactory;

spl_autoload_register( function ( $class_name ) {
    if ( strpos( $class_name, 'Unity_' ) !== false ) {
        $file = UNITY_WEBGL_PLUGIN_DIR . 'includes/' . $class_name . '.php';
        if ( file_exists( $file ) ) {
            include_once $file;
        }
    }
} );

require_once UNITY_WEBGL_PLUGIN_DIR . 'includes/ModelsUploader.php';

add_action( 'plugins_loaded', function() {
    global $wp_version;
    if ( version_compare( $wp_version, '5.0', '<' ) ) {
        wp_die( 'This plugin requires WordPress version 5.0 or higher.' );
    }

    Unity_Shortcode::get_instance();
    Unity_Settings::get_instance();
    ModelsUploader::init();

    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://plugin-uodater.alex.hellodevs.dev/plugins/unity-for-wp.json',
        __FILE__,
        'unity-for-wp'
    );


} );

register_activation_hook( __FILE__, 'unity_webgl_plugin_activate' );
register_deactivation_hook( __FILE__, 'unity_webgl_plugin_deactivate' );

function unity_webgl_plugin_activate() {
    if ( ! function_exists( 'wp_enqueue_script' ) ) {
        wp_die( 'This plugin requires WordPress scripts to function correctly.' );
    }
}

function unity_webgl_plugin_deactivate() {
    // nothing
}
