<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Unity_Settings {

private static $instance = null;

public static function get_instance() {
    if ( is_null( self::$instance ) ) {
        self::$instance = new self();
    }
    return self::$instance;
}

private function __construct() {
    add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
    add_action( 'admin_init', [ $this, 'register_settings' ] );
}

public function add_settings_page() {
    add_options_page(
        'Unity WebGL Settings',
        'Unity WebGL',
        'manage_options',
        'unity-webgl-settings',
        [ $this, 'render_settings_page' ]
    );
}

public function register_settings() {
    register_setting( 'unity_webgl_settings', 'compression_type' );

    add_settings_section(
        'unity_webgl_settings_section',
        'Unity WebGL Settings',
        null,
        'unity-webgl-settings'
    );

    add_settings_field(
        'compression_type',
        'Select Compression Type',
        [ $this, 'render_compression_type_field' ],
        'unity-webgl-settings',
        'unity_webgl_settings_section'
    );
}

public function render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Unity WebGL Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'unity_webgl_settings' );
            do_settings_sections( 'unity-webgl-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

public function render_compression_type_field() {
    $compression_type = get_option( 'compression_type', 'uncompressed' );
    ?>
    <select name="compression_type">
        <option value="uncompressed" <?php selected( $compression_type, 'uncompressed' ); ?>>Uncompressed</option>
        <option value="gzip" <?php selected( $compression_type, 'gzip' ); ?>>GZIP</option>
        <option value="brotli" <?php selected( $compression_type, 'brotli' ); ?>>Brotli</option>
    </select>
    <?php
}
}
