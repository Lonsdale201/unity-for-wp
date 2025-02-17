<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Unity_Shortcode
 *
 * Handles the display of [unity] shortcode, including the registration of JS scripts,
 * which dynamically load the required files from the selected build subfolder.
 * Supports the "autostart" attribute, which can be set to "true" or "false".
 */

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
        add_shortcode( 'unity', [ $this, 'render_unity_webgl' ] );
    }

    /**
     * Processes the [unity] shortcode.
     *
     * Supported attributes:
     * - id: The desired build ID.
     * - autostart: "true" (default) or "false". If false, a Start Unity button (with placeholder) is shown.
     * - width: (Optional) Overrides the global canvas width.
     * - height: (Optional) Overrides the global canvas height.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_unity_webgl( $atts ) {
        // Set default attributes.
        $atts = shortcode_atts( array(
            'id' => '',
            'autostart' => 'true',
            'width' => '',   // Optional width override (e.g., "40vh", "100px")
            'height' => ''   // Optional height override
        ), $atts, 'unity' );
        
        // Convert autostart attribute to boolean.
        $autostart = ( strtolower( $atts['autostart'] ) === 'true' );
        
        // Get builds from the build manager.
        $build_manager = new Unity_Build_Manager();
        $builds = $build_manager->get_builds();
        
        if ( empty( $builds ) ) {
            return '<p>No Unity build uploaded. Please upload a build.</p>';
        }
        
        // Select the build based on the provided ID or default to the last build.
        $selected_build = null;
        if ( ! empty( $atts['id'] ) ) {
            foreach ( $builds as $build ) {
                if ( $build['id'] == intval( $atts['id'] ) ) {
                    $selected_build = $build;
                    break;
                }
            }
            if ( ! $selected_build ) {
                return '<p>The specified Unity build could not be found.</p>';
            }
        } else {
            $selected_build = end( $builds );
        }
        
        // Set the build directory and URL.
        $this->build_dir = $selected_build['dir'];
        $this->build_url = $this->ensure_https( $selected_build['url'] );
        
        // Retrieve global canvas settings (including our new placeholder_image and button_label).
        $options = get_option( 'canvas_settings', [
            'canvas_width'     => '100%',
            'canvas_height'    => '100%',
            'aspect_ratio'     => '16 / 9',
            'compression_type' => 'uncompressed',
            'button_label'     => 'Start Unity',
            'placeholder_image'=> ''
        ] );
        $compression_type = isset( $options['compression_type'] ) ? $options['compression_type'] : 'uncompressed';
        $button_label = isset( $options['button_label'] ) ? $options['button_label'] : 'Start Unity';
        $placeholder_image = isset( $options['placeholder_image'] ) ? $options['placeholder_image'] : '';
        
        $canvas_width = ! empty( $atts['width'] ) ? esc_attr( $atts['width'] ) : ( isset( $options['canvas_width'] ) ? esc_attr( $options['canvas_width'] ) : '100%' );
        $canvas_height = ! empty( $atts['height'] ) ? esc_attr( $atts['height'] ) : ( isset( $options['canvas_height'] ) ? esc_attr( $options['canvas_height'] ) : '100%' );

        $aspect_ratio = isset( $options['aspect_ratio'] ) ? esc_attr( $options['aspect_ratio'] ) : '16 / 9';
        
        // Register and enqueue necessary scripts.
        wp_register_script( 'unity-webgl-init', UNITY_WEBGL_PLUGIN_URL . 'assets/unity-webgl-init.js', [], null, true );
        wp_enqueue_script( 'unity-webgl-init' );
        
        // Retrieve the required Unity build files from the build folder.
        $files = $this->get_build_files( $compression_type );
        if ( empty( $files['loader'] ) || empty( $files['data'] ) || empty( $files['framework'] ) || empty( $files['wasm'] ) ) {
            return '<p>Required Unity build files are missing in the selected build folder!</p>';
        }
        
        // Generate unique IDs based on the build ID.
        $build_id = isset( $selected_build['id'] ) ? intval( $selected_build['id'] ) : uniqid();
        $container_id = 'unityContainer-' . $build_id;
        $canvas_id = 'unityCanvas-' . $build_id;
        $config_id = 'unityConfig-' . $build_id;
        
        // Assemble the configuration array.
        $unity_config = [
            'buildUrl'           => $this->build_url,
            'loaderUrl'          => $files['loader'],
            'dataUrl'            => $files['data'],
            'frameworkUrl'       => $files['framework'],
            'codeUrl'            => $files['wasm'],
            'streamingAssetsUrl' => 'StreamingAssets',
            'companyName'        => 'DefaultCompany',
            'productName'        => 'My project',
            'productVersion'     => '0.1',
            'autostart'          => $autostart
        ];
        
        // Generate the HTML output.
        ob_start();
        ?>
     <div id="<?php echo esc_attr( $container_id ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" class="unitycontainer" style="position: relative; width: <?php echo $canvas_width; ?>; height: <?php echo $canvas_height; ?>;">
            <canvas id="<?php echo esc_attr( $canvas_id ); ?>" style="width: 100%; height: 100%;"></canvas>
            <?php if ( ! $autostart ) : ?>
                <?php if ( ! empty( $placeholder_image ) ) : ?>
                    <!-- Placeholder image that covers the entire container -->
                    <img id="unityPlaceholder-<?php echo $build_id; ?>" src="<?php echo esc_url( $placeholder_image ); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; z-index: 5;" />
                <?php endif; ?>
                <!-- Start Unity button -->
                <button id="startUnityButton-<?php echo $build_id; ?>" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10;">
                    <?php echo esc_html( $button_label ); ?>
                </button>
            <?php endif; ?>
        </div>
        <script type="application/json" id="<?php echo esc_attr( $config_id ); ?>">
            <?php echo wp_json_encode( $unity_config ); ?>
        </script>
        <?php
        return ob_get_clean();

    }

    /**
     * Ensures the given URL uses HTTPS.
     *
     * @param string $url
     * @return string
     */
    private function ensure_https( $url ) {
        if ( strpos( $url, 'http://' ) !== false ) {
            return str_replace( 'http://', 'https://', $url );
        }
        return $url;
    }

    /**
     * Retrieves the necessary Unity build file URLs from the build folder.
     *
     * @param string $compression_type ('uncompressed', 'gzip', 'brotli').
     * @return array
     */
    private function get_build_files( $compression_type ) {
        $files = [
            'loader'    => '',
            'data'      => '',
            'framework' => '',
            'wasm'      => '',
        ];
    
        // If there is a "Build" subfolder, use that folder.
        $build_files_folder = $this->build_dir;
        if ( is_dir( $this->build_dir . '/Build' ) ) {
            $build_files_folder = $this->build_dir . '/Build';
        }
    
        $all_files = glob( $build_files_folder . '/*' );
    
        // Adjust the base URL if using the Build subfolder.
        $base_url = $this->build_url;
        if ( $build_files_folder !== $this->build_dir ) {
            $base_url .= '/Build';
        }
    
        foreach ( $all_files as $file ) {
            $filename = basename( $file );
            $encoded_filename = rawurlencode( $filename );
            $url = $base_url . '/' . $encoded_filename;
            $url = $this->ensure_https( $url );
    
            // Use regex to identify the required files (optionally with .gz or .br suffix).
            if ( preg_match( '/\.loader\.js(\.gz|\.br)?$/', $filename ) ) {
                $files['loader'] = $url;
            } elseif ( preg_match( '/\.data(\.gz|\.br)?$/', $filename ) ) {
                $files['data'] = $url;
            } elseif ( preg_match( '/\.framework\.js(\.gz|\.br)?$/', $filename ) ) {
                $files['framework'] = $url;
            } elseif ( preg_match( '/\.wasm(\.gz|\.br)?$/', $filename ) ) {
                $files['wasm'] = $url;
            }
        }
    
        return $files;
    }
}
