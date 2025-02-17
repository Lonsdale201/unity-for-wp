<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Unity_Build_Manager
 *
 * Manages the upload, listing and deletion of multiple builds.
 */
class Unity_Build_Manager {
    /**
     * @var string The root directory of Unity builds.
     */
    private $builds_root;

    public function __construct() {
        $this->builds_root = UNITY_WEBGL_PLUGIN_DIR . 'unitybuild';
    }

    /**
     * Returns all uploaded build folders.
     *
     * @return array List of builds with all elements: 'id', 'dir', 'url', 'modified'
     */
    public function get_builds() {
        $builds = [];
        if ( ! is_dir( $this->builds_root ) ) {
            return $builds;
        }
        // Lekérjük a reupload és upload dátumokat
        $reupload_dates = get_option( 'unity_build_reupload_dates', [] );
        $upload_dates   = get_option( 'unity_build_upload_dates', [] );
    
        $dirs = scandir( $this->builds_root );
        foreach ( $dirs as $dir ) {
            if ( $dir === '.' || $dir === '..' ) {
                continue;
            }
            $path = $this->builds_root . '/' . $dir;
            if ( is_dir( $path ) && preg_match( '/^ubuild_(\d+)$/', $dir, $matches ) ) {
                $build_id = intval( $matches[1] );
                $modified = filemtime( $path );
                $builds[] = [
                    'id'            => $build_id,
                    'dir'           => $path,
                    'url'           => UNITY_WEBGL_PLUGIN_URL . 'unitybuild/' . $dir,
                    'upload_date'   => isset( $upload_dates[ $build_id ] ) ? $upload_dates[ $build_id ] : $modified,
                    'reupload_date' => isset( $reupload_dates[ $build_id ] ) ? $reupload_dates[ $build_id ] : 0,
                ];
            }
        }
        usort( $builds, function( $a, $b ) {
            return $a['id'] - $b['id'];
        } );
        return $builds;
    }
    
    

    /**
     * Returns the next available build ID.
     *
     * @return int Next build ID.
     */
    public function get_next_build_id() {
        $builds = $this->get_builds();
        $max_id = 0;
        foreach ( $builds as $build ) {
            if ( $build['id'] > $max_id ) {
                $max_id = $build['id'];
            }
        }
        return $max_id + 1;
    }

    /**
     * Deletes a build based on the specified ID.
     *
     * @param int $build_id The ID of the build to delete.
     * @return bool True if successful.
     */
    public function delete_build( $build_id ) {
        $build_folder = $this->builds_root . '/ubuild_' . intval( $build_id );
        if ( ! is_dir( $build_folder ) ) {
            return false;
        }
        return $this->delete_directory( $build_folder );
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir The directory to delete.
     * @return bool
     */
    public function delete_directory( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return false;
        }
        $items = array_diff( scandir( $dir ), array( '.', '..' ) );
        foreach ( $items as $item ) {
            $path = $dir . '/' . $item;
            if ( is_dir( $path ) ) {
                $this->delete_directory( $path );
            } else {
                unlink( $path );
            }
        }
        return rmdir( $dir );
    }
}