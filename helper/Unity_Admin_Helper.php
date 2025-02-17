<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Unity_Admin_Helper {

    /**
     * Rendereli a modális ablak HTML-jét.
     *
     * @return void
     */
    public static function render_reupload_modal() {
        ?>
        <div id="modalOverlay" style="display:none;position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.5);"></div>
        <div id="reuploadModal" style="display:none; position: fixed; z-index: 10000; left:50%; top:50%; transform: translate(-50%, -50%); background:#fff; border:1px solid #ccc; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.3); max-width:400px; width:90%;">
            <h2><?php esc_html_e( 'Reupload Build', 'your-textdomain' ); ?></h2>
            <form id="reuploadForm" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="reupload_unity_build" />
                <input type="hidden" name="build_id" id="modalBuildId" value="" />
                <p>
                    <label for="reuploadFile"><?php esc_html_e( 'Select a ZIP file containing your Unity build files:', 'your-textdomain' ); ?></label><br />
                    <input type="file" name="unity_build_files" id="reuploadFile" />
                </p>
                <div class="modal-buttons" style="margin-top:15px; text-align:right;">
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Reupload Build', 'your-textdomain' ); ?></button>
                    <button type="button" id="closeReuploadModal" class="button"><?php esc_html_e( 'Cancel', 'your-textdomain' ); ?></button>
                </div>
            </form>
        </div>
        <?php
    }
}
