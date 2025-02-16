<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Unity_Settings
 *
 * Kezeli az admin felületet, beleértve a build beállítások módosítását, a ZIP build feltöltést,
 * az Uploaded Builds fülön történő build listázást, törlést, illetve az újrafeltöltést (reupload).
 */
class Unity_Settings {
	private static $instance = null;

	/**
	 *
	 * @return Unity_Settings
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_post_upload_unity_build', [ $this, 'handle_file_uploads' ] );
		add_action( 'admin_post_delete_unity_build', [ $this, 'handle_delete_unity_build' ] );
		add_action( 'admin_post_reupload_unity_build', [ $this, 'handle_reupload_unity_build' ] ); 
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
				'canvas_width'     => '100%',
				'canvas_height'    => '100%',
				'aspect_ratio'     => '16 / 9',
				'enable_3d_uploads'=> 0,
				'placeholder_image'=> ''
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

		add_settings_field(
			'button_label',
			'Unity Start Button Label',
			[ $this, 'render_button_label_field' ],
			'unity-webgl-settings',
			'unity_webgl_settings_section'
		);

		add_settings_field(
			'placeholder_image',
			'Placeholder Image URL',
			[ $this, 'render_placeholder_image_field' ],
			'unity-webgl-settings',
			'unity_webgl_settings_section'
		);
	}


	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1>Unity WebGL Settings</h1>
			<?php if ( isset( $_GET['status'] ) && $_GET['status'] == 'success' ): ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo isset( $_GET['count'] ) ? esc_html( $_GET['count'] ) . ' file(s) uploaded successfully.' : 'File(s) uploaded successfully.'; ?></p>
				</div>
			<?php elseif ( isset( $_GET['status'] ) && $_GET['status'] == 'error' && isset( $_GET['message'] ) ): ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( $_GET['message'] ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Tab nav -->
			<h2 class="nav-tab-wrapper">
				<a href="#build-settings" class="nav-tab nav-tab-active">Build Settings</a>
				<a href="#uploaded-builds" class="nav-tab">Uploaded Builds</a>
			</h2>

			<div id="build-settings" class="tab-content" style="display: block;">
				<!-- Build Settings form -->
				<form method="post" action="options.php">
					<?php
					settings_fields( 'unity_webgl_settings' );
					do_settings_sections( 'unity-webgl-settings' );
					submit_button();
					?>
				</form>

				<!-- Unity Build ZIP uploader form -->
				<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin-post.php' ); ?>">
					<input type="hidden" name="action" value="upload_unity_build" />
					<?php $this->render_build_upload_field(); ?>
					<?php submit_button( 'Upload Unity Build Files' ); ?>
				</form>
			</div>

			<div id="uploaded-builds" class="tab-content" style="display: none;">
				<!-- Uploaded Builds list -->
				<h2>Uploaded Builds</h2>
				<?php
				$build_manager = new Unity_Build_Manager();
				$builds = $build_manager->get_builds();
				if ( empty( $builds ) ) {
					echo '<p>No builds found.</p>';
				} else {
					?>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>Folder Name</th>
								<th>Date Uploaded</th>
								<th>Shortcode</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $builds as $build ) : ?>
								<tr>
									<td><?php echo esc_html( $build['id'] ); ?></td>
									<td><?php echo esc_html( basename( $build['dir'] ) ); ?></td>
									<td><?php echo esc_html( date( 'Y-m-d H:i:s', $build['modified'] ) ); ?></td>
									<td>[unity id="<?php echo esc_attr( $build['id'] ); ?>"]</td>
									<td>
										<?php
										$delete_url = wp_nonce_url(
											admin_url( 'admin-post.php?action=delete_unity_build&id=' . intval( $build['id'] ) ),
											'delete_unity_build_' . intval( $build['id'] )
										);
										?>
										<a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Are you sure you want to delete this build?');">Delete</a>
										|
										<!-- Reupload link -->
										<a href="#" class="reupload-link" data-build-id="<?php echo intval( $build['id'] ); ?>">Reupload</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php
				}
				?>
			</div>
		</div>

		<style>
			#reuploadModal {
				display: none;
				position: fixed;
				z-index: 10000;
				left: 50%;
				top: 50%;
				transform: translate(-50%, -50%);
				background: #fff;
				border: 1px solid #ccc;
				padding: 20px;
				box-shadow: 0 2px 8px rgba(0,0,0,0.3);
				max-width: 400px;
				width: 90%;
			}
			#modalOverlay {
				position: fixed;
				z-index: 9999;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				background: rgba(0,0,0,0.5);
				display: none;
			}
			#reuploadModal h2 {
				margin-top: 0;
			}
			#reuploadModal .modal-buttons {
				margin-top: 15px;
				text-align: right;
			}
		</style>

		<div id="modalOverlay"></div>
		<div id="reuploadModal">
			<h2>Reupload Build</h2>
			<form id="reuploadForm" method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<input type="hidden" name="action" value="reupload_unity_build" />
				<input type="hidden" name="build_id" id="modalBuildId" value="" />
				<p>
					<label for="reuploadFile">Select a ZIP file containing your Unity build files:</label><br />
					<input type="file" name="unity_build_files" id="reuploadFile" />
				</p>
				<div class="modal-buttons">
					<button type="submit" class="button button-primary">Reupload Build</button>
					<button type="button" id="closeReuploadModal" class="button">Cancel</button>
				</div>
			</form>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var tabs = document.querySelectorAll('.nav-tab');
			var contents = document.querySelectorAll('.tab-content');
			tabs.forEach(function (tab) {
				tab.addEventListener('click', function (e) {
					e.preventDefault();
					tabs.forEach(function (t) { t.classList.remove('nav-tab-active'); });
					contents.forEach(function (content) { content.style.display = 'none'; });
					tab.classList.add('nav-tab-active');
					var target = tab.getAttribute('href');
					var content = document.querySelector(target);
					if ( content ) {
						content.style.display = 'block';
					}
				});
			});
			var reuploadLinks = document.querySelectorAll('.reupload-link');
			var modal = document.getElementById('reuploadModal');
			var overlay = document.getElementById('modalOverlay');
			var modalBuildId = document.getElementById('modalBuildId');
			var closeBtn = document.getElementById('closeReuploadModal');

			reuploadLinks.forEach(function(link) {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					var buildId = this.getAttribute('data-build-id');
					modalBuildId.value = buildId;
					modal.style.display = 'block';
					overlay.style.display = 'block';
				});
			});

			closeBtn.addEventListener('click', function() {
				modal.style.display = 'none';
				overlay.style.display = 'none';
			});

			overlay.addEventListener('click', function() {
				modal.style.display = 'none';
				overlay.style.display = 'none';
			});
		});
		</script>
		<?php
	}

	public function render_compression_type_field() {
		$options          = get_option( 'canvas_settings' );
		$compression_type = isset( $options['compression_type'] ) ? $options['compression_type'] : 'uncompressed';
		?>
		<select name="canvas_settings[compression_type]">
			<option value="uncompressed" <?php selected( $compression_type, 'uncompressed' ); ?>>Uncompressed</option>
			<option value="gzip" <?php selected( $compression_type, 'gzip' ); ?>>GZIP</option>
			<option value="brotli" <?php selected( $compression_type, 'brotli' ); ?>>Brotli</option>
		</select>
		<p class="description">Global compression setting (applies to all builds). You must set this before the Unity build process in Player settings / Publishing settings / Compression format</p>
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
		$width   = isset( $options['canvas_width'] ) ? $options['canvas_width'] : '100%';
		?>
		<input type="text" name="canvas_settings[canvas_width]" value="<?php echo esc_attr( $width ); ?>" />
		<p class="description">Global canvas width setting, overridable in shortcodes. Use the <code>width=""</code> parameter to overwrite the global settings</p>
		<?php
	}

	public function render_canvas_height_field() {
		$options = get_option( 'canvas_settings' );
		$height  = isset( $options['canvas_height'] ) ? $options['canvas_height'] : '100%';
		?>
		<input type="text" name="canvas_settings[canvas_height]" value="<?php echo esc_attr( $height ); ?>" />
		<p class="description">Global canvas height setting, overridable in shortcodes. Use the <code>height=""</code> parameter to overwrite the global settings</p>
		<?php
	}

	public function render_aspect_ratio_field() {
		$options      = get_option( 'canvas_settings' );
		$aspect_ratio = isset( $options['aspect_ratio'] ) ? $options['aspect_ratio'] : '16 / 9';
		?>
		<input type="text" name="canvas_settings[aspect_ratio]" value="<?php echo esc_attr( $aspect_ratio ); ?>" />
		<?php
	}

	public function render_build_upload_field() {
		?>
		<input type="file" name="unity_build_files" />
		<p>Select a ZIP file containing your Unity build files (e.g., .html, .css, .ico, .data, .loader.js, .wasm, .framework.js).</p>
		<?php
	}

	public function render_button_label_field() {
		$options = get_option( 'canvas_settings' );
		$button_label = isset( $options['button_label'] ) ? $options['button_label'] : 'Start Unity';
		?>
		<input type="text" name="canvas_settings[button_label]" value="<?php echo esc_attr( $button_label ); ?>" />
		<p class="description">This is the button label text that appears when autostart is not enabled. Use the <code>autostart="true/false"</code> parameter</p>
		<?php
	}

	public function render_placeholder_image_field() {
		$options = get_option( 'canvas_settings' );
		$placeholder_image = isset( $options['placeholder_image'] ) ? $options['placeholder_image'] : '';
		?>
		<input type="text" name="canvas_settings[placeholder_image]" value="<?php echo esc_url( $placeholder_image ); ?>" />
		<p class="description">Enter the URL of the placeholder image that will fill the entire canvas if autostart is not enabled.</p>
		<?php
	}

	/**
	 * Handle the build ZIP file.
	 */
	public function handle_file_uploads() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		
		if ( ! isset( $_FILES['unity_build_files'] ) || empty( $_FILES['unity_build_files']['name'] ) ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'No file uploaded.' ) ) );
			exit;
		}

		$uploaded_file = $_FILES['unity_build_files'];
		$file_name     = $uploaded_file['name'];
		$file_tmp      = $uploaded_file['tmp_name'];
		$file_ext      = pathinfo( $file_name, PATHINFO_EXTENSION );

		if ( strtolower( $file_ext ) !== 'zip' ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Only ZIP files are allowed.' ) ) );
			exit;
		}

		$build_root = UNITY_WEBGL_PLUGIN_DIR . 'unitybuild';
		if ( ! file_exists( $build_root ) ) {
			wp_mkdir_p( $build_root );
		}

		$build_manager   = new Unity_Build_Manager();
		$next_build_id   = $build_manager->get_next_build_id();
		$new_build_folder = $build_root . '/ubuild_' . $next_build_id;

		if ( ! wp_mkdir_p( $new_build_folder ) ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to create build directory.' ) ) );
			exit;
		}

		$zip_file_dest = $new_build_folder . '/' . basename( $file_name );
		if ( move_uploaded_file( $file_tmp, $zip_file_dest ) ) {
			$zip = new ZipArchive;
			if ( $zip->open( $zip_file_dest ) === TRUE ) {
				$zip->extractTo( $new_build_folder );
				$zip->close();
				unlink( $zip_file_dest );

				$extracted_files_count = count( glob( $new_build_folder . '/*' ) );
				wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=success&count=' . $extracted_files_count ) );
				exit;
			} else {
				wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to unzip the file.' ) ) );
				exit;
			}
		} else {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to upload the ZIP file.' ) ) );
			exit;
		}
	}

	/**
	 * Handle build delete
	 */
	public function handle_delete_unity_build() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		$build_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		if ( ! $build_id ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Invalid build ID.' ) ) );
			exit;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_unity_build_' . $build_id ) ) {
			wp_die( 'Nonce verification failed.' );
		}

		$build_manager = new Unity_Build_Manager();
		if ( $build_manager->delete_build( $build_id ) ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=success&message=' . urlencode( 'Build deleted successfully.' ) ) );
			exit;
		} else {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to delete build.' ) ) );
			exit;
		}
	}

	/**
	 * Handle the build reupload
	 */
	public function handle_reupload_unity_build() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}
		

		if ( ! isset( $_FILES['unity_build_files'] ) || empty( $_FILES['unity_build_files']['name'] ) ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'No file uploaded for reupload.' ) ) );
			exit;
		}

		$build_id = isset( $_POST['build_id'] ) ? intval( $_POST['build_id'] ) : 0;
		if ( ! $build_id ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Invalid build ID for reupload.' ) ) );
			exit;
		}

		$build_folder = UNITY_WEBGL_PLUGIN_DIR . 'unitybuild/ubuild_' . $build_id;
		if ( ! is_dir( $build_folder ) ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Build folder does not exist.' ) ) );
			exit;
		}

		$this->delete_directory_contents( $build_folder );

		$uploaded_file = $_FILES['unity_build_files'];
		$file_name     = $uploaded_file['name'];
		$file_tmp      = $uploaded_file['tmp_name'];
		$file_ext      = pathinfo( $file_name, PATHINFO_EXTENSION );

		if ( strtolower( $file_ext ) !== 'zip' ) {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Only ZIP files are allowed for reupload.' ) ) );
			exit;
		}

		$zip_file_dest = $build_folder . '/' . basename( $file_name );
		if ( move_uploaded_file( $file_tmp, $zip_file_dest ) ) {
			$zip = new ZipArchive;
			if ( $zip->open( $zip_file_dest ) === TRUE ) {
				$zip->extractTo( $build_folder );
				$zip->close();
				unlink( $zip_file_dest );

				$extracted_files_count = count( glob( $build_folder . '/*' ) );
				wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=success&message=' . urlencode( 'Build reuploaded successfully with ' . $extracted_files_count . ' files.' ) ) );
				exit;
			} else {
				wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to unzip the file during reupload.' ) ) );
				exit;
			}
		} else {
			wp_redirect( admin_url( 'options-general.php?page=unity-webgl-settings&status=error&message=' . urlencode( 'Failed to upload the ZIP file during reupload.' ) ) );
			exit;
		}
	}

	/**
	 * Delete the contents of a library, but keep the library
	 *
	 * @param string $dir The path to the directory.
	 * @return void
	 */
	private function delete_directory_contents( $dir ) {
		$build_manager = new Unity_Build_Manager();
		$items = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $items as $item ) {
			$path = $dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$build_manager->delete_directory( $path );
			} else {
				unlink( $path );
			}
		}
	}
}