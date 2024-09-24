<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ModelsUploader {

    public static function init() {
        add_filter('upload_mimes', [ __CLASS__, 'allow_3d_model_uploads_for_admins' ]);
        add_filter( 'map_meta_cap', [ __CLASS__, 'allow_unfiltered_uploads_for_admin' ], 10, 4 );
    }

    public static function allow_3d_model_uploads_for_admins($mime_types) {
        if ( current_user_can( 'administrator' ) ) {
            $options = get_option( 'canvas_settings' );
            $enable_3d_uploads = isset( $options['enable_3d_uploads'] ) ? (bool) $options['enable_3d_uploads'] : false;

            if ($enable_3d_uploads) {
                // Allowed file formats
                $mime_types['obj'] = 'model/obj';
                $mime_types['gltf'] = 'model/gltf+json';
                $mime_types['glb'] = 'model/gltf-binary';
                $mime_types['mtl'] = 'model/mtl';
            }
        }
        return $mime_types;
    }

    public static function allow_unfiltered_uploads_for_admin( $caps, $cap, $user_id, $args ) {
        if ( 'unfiltered_upload' === $cap ) {
            $user = get_userdata( $user_id );
            if ( in_array( 'administrator', (array) $user->roles ) ) {
                $caps = array( 'unfiltered_upload' );
            }
        }
        return $caps;
    }
}
