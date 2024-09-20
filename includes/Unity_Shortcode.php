<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Unity_Shortcode {

    private static $instance = null;
    private $build_dir;
    private $build_url;

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->build_dir = UNITY_WEBGL_PLUGIN_DIR . 'unitybuild/Build'; 
        $this->build_url = $this->ensure_https( UNITY_WEBGL_PLUGIN_URL . 'unitybuild/Build' ); 

        add_shortcode( 'unity', [ $this, 'render_unity_webgl' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function render_unity_webgl() {
        wp_enqueue_script( 'unity-webgl-init' );
        wp_enqueue_script( 'colorchange-sample' ); 

        ob_start();
        ?>
        <div id="unityContainer" style="width: auto; height: auto">
            <canvas id="unityCanvas" style="width: 100%; height: 100%;"></canvas>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        $compression_type = get_option( 'compression_type', 'uncompressed' ); // 'uncompressed', 'gzip', 'brotli'
        $files = $this->get_build_files( $compression_type );

        if ( empty( $files['loader'] ) || empty( $files['data'] ) || empty( $files['framework'] ) || empty( $files['wasm'] ) ) {
            wp_die( 'Required Unity Build files not found!' );
        }

        wp_register_script( 'unity-webgl-init', UNITY_WEBGL_PLUGIN_URL . 'assets/unity-webgl-init.js', [], null, true );
        wp_localize_script( 'unity-webgl-init', 'unityWebGLConfig', [
            'buildUrl' => $this->build_url,
            'loaderUrl' => $files['loader'],
            'dataUrl' => $files['data'],
            'frameworkUrl' => $files['framework'],
            'codeUrl' => $files['wasm'],
            'streamingAssetsUrl' => 'StreamingAssets',
            'companyName' => 'DefaultCompany',
            'productName' => 'My project',
            'productVersion' => '0.1',
        ]);
        wp_register_script( 'colorchange-sample', UNITY_WEBGL_PLUGIN_URL . 'assets/samples/colorchange.js', [], null, true );
    }
    
    private function ensure_https( $url ) {
        if ( strpos( $url, 'http://' ) !== false ) {
            return str_replace( 'http://', 'https://', $url );
        }
        return $url;
    }

    private function get_build_files( $compression_type ) {
        $files = [
            'loader' => '',
            'data' => '',
            'framework' => '',
            'wasm' => ''
        ];

        $compression_extension = '';
        if ( $compression_type == 'gzip' ) {
            $compression_extension = '.gz';
        } elseif ( $compression_type == 'brotli' ) {
            $compression_extension = '.br';
        }

        $all_files = glob( $this->build_dir . '/*' );

        error_log("Files found in directory: " . print_r($all_files, true));

        foreach ( $all_files as $file ) {
            $filename = basename($file);
            $encoded_filename = rawurlencode($filename);
            $url = $this->build_url . '/' . $encoded_filename; 
            $url = $this->ensure_https( $url );

            if ( strpos( $filename, '.loader.js' ) !== false ) {
                $files['loader'] = $url;
                continue;
            }


            if ( $compression_extension && strpos( $filename, $compression_extension ) === false ) {
                continue; 
            }

            if ( strpos( $filename, '.data' ) !== false ) {
                $files['data'] = $url;
            } elseif ( strpos( $filename, '.framework.js' ) !== false ) {
                $files['framework'] = $url;
            } elseif ( strpos( $filename, '.wasm' ) !== false ) {
                $files['wasm'] = $url;
            }
        }


        return $files;
    }
}
