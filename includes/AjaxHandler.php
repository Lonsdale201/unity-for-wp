<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AjaxHandler {

    // Konstruktor: regisztrálja az AJAX callback-et.
    public function __construct() {
        add_action( 'wp_ajax_update_unity_build_name', [ $this, 'update_unity_build_name_callback' ] );
    }

    /**
     * Updates the project name (build name) for a given build ID.
     */
    public function update_unity_build_name_callback() {
        error_log( 'update_unity_build_name_callback triggered' );
        // Check permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
            wp_die();
        }
        
        // Check nonce.
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'update_unity_build_name_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
            wp_die();
        }
        
        $build_id = isset( $_POST['build_id'] ) ? intval( $_POST['build_id'] ) : 0;
        $build_name = isset( $_POST['build_name'] ) ? sanitize_text_field( $_POST['build_name'] ) : '';
        
        if ( ! $build_id ) {
            wp_send_json_error( 'Invalid build ID' );
            wp_die();
        }
        
        // Get current build names option (if not exists, returns an empty array).
        $build_names = get_option( 'unity_build_names', [] );
        
        // Update the build name for this build ID.
        $build_names[ $build_id ] = $build_name;
        update_option( 'unity_build_names', $build_names );
        
        wp_send_json_success( [ 'build_id' => $build_id, 'build_name' => $build_name ] );
        wp_die();
    }
}
