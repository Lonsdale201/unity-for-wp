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
    register_setting( 'unity_webgl_settings', 'canvas_settings', [
        'default' => [
            'compression_type' => 'uncompressed',
            'canvas_width' => '100%',
            'canvas_height' => '100%',
            'aspect_ratio' => '16 / 9',
            'enable_3d_uploads' => 0, 
        ],
    ] );

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

    add_settings_field(
        'enable_3d_uploads',
        'Enable 3D Models Upload',
        [ $this, 'render_enable_3d_uploads_field' ],
        'unity-webgl-settings',
        'unity_webgl_settings_section'
    );

    add_settings_field(
        'canvas_width',
        'Canvas Width (e.g., 100%, 800px)',
        [ $this, 'render_canvas_width_field' ],
        'unity-webgl-settings',
        'unity_webgl_settings_section'
    );

    add_settings_field(
        'canvas_height',
        'Canvas Height (e.g., 100%, 600px)',
        [ $this, 'render_canvas_height_field' ],
        'unity-webgl-settings',
        'unity_webgl_settings_section'
    );

    add_settings_field(
        'aspect_ratio',
        'Aspect Ratio (e.g., 16 / 9)',
        [ $this, 'render_aspect_ratio_field' ],
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
        $options = get_option( 'canvas_settings' );
        $compression_type = isset( $options['compression_type'] ) ? $options['compression_type'] : 'uncompressed';
        ?>
        <select name="canvas_settings[compression_type]">
            <option value="uncompressed" <?php selected( $compression_type, 'uncompressed' ); ?>>Uncompressed</option>
            <option value="gzip" <?php selected( $compression_type, 'gzip' ); ?>>GZIP</option>
            <option value="brotli" <?php selected( $compression_type, 'brotli' ); ?>>Brotli</option>
        </select>
        <?php
    }

    public function render_enable_3d_uploads_field() {
        $options = get_option( 'canvas_settings' );
        $enabled = isset( $options['enable_3d_uploads'] ) ? (bool) $options['enable_3d_uploads'] : false;
        ?>
        <input type="checkbox" name="canvas_settings[enable_3d_uploads]" value="1" <?php checked( $enabled, true ); ?> />
        <label for="canvas_settings[enable_3d_uploads]">Enable 3D Models Upload to Media Library</label>
        <?php
    }


    public function render_canvas_width_field() {
        $options = get_option( 'canvas_settings' );
        $width = isset( $options['canvas_width'] ) ? $options['canvas_width'] : '100%';
        ?>
        <input type="text" name="canvas_settings[canvas_width]" value="<?php echo esc_attr( $width ); ?>" />
        <?php
    }

    public function render_canvas_height_field() {
        $options = get_option( 'canvas_settings' );
        $height = isset( $options['canvas_height'] ) ? $options['canvas_height'] : '100%';
        ?>
        <input type="text" name="canvas_settings[canvas_height]" value="<?php echo esc_attr( $height ); ?>" />
        <?php
    }

    public function render_aspect_ratio_field() {
        $options = get_option( 'canvas_settings' );
        $aspect_ratio = isset( $options['aspect_ratio'] ) ? $options['aspect_ratio'] : '16 / 9';
        ?>
        <input type="text" name="canvas_settings[aspect_ratio]" value="<?php echo esc_attr( $aspect_ratio ); ?>" />
        <?php
    }
}
