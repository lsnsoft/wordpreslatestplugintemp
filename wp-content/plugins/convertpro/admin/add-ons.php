<?php
/**
 * Addons File.
 *
 * @package ConvertPro
 */

if ( isset( $_REQUEST['cpro_admin_page_menu_nonce'] ) && ! wp_verify_nonce( $_REQUEST['cpro_admin_page_menu_nonce'], 'cpro_admin_page_menu' ) ) {
	die( 'No direct script access allowed!' );
}

wp_register_style( 'bsf-core-admin', bsf_core_url( '/assets/css/style.css' ), array(), BSF_UPDATER_VERSION );
wp_enqueue_style( 'bsf-core-admin' );
$product_id = 'convertpro';

$cp_action = ( isset( $_GET['action'] ) && 'install' === $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
if ( 'install' === $cp_action ) {
	$request_product_id = ( isset( $_GET['id'] ) ) ? sanitize_text_field( $_GET['id'] ) : '';
	if ( '' !== $request_product_id ) {
		?>
			<div class="clear"></div>
			<div class="wrap">
			<h2><?php esc_html_e( 'Installing Addon', 'convertpro' ); ?></h2>
			<?php
				$installed = install_bsf_product( $request_product_id );
			?>
			<?php if ( isset( $installed['status'] ) && true === $installed['status'] ) : ?>
					<?php $current_name = strtolower( bsf_get_current_name( $installed['init'], $installed['type'] ) ); ?>
					<?php
					$current_name = preg_replace( '![^a-z0-9]+!i', '-', $current_name );
					$cpro_url     = ( is_multisite() ) ? network_admin_url( 'plugins.php#' . esc_attr( $current_name ) ) : admin_url( 'plugins.php#' . esc_attr( $current_name ) );
					?>
					<a href="<?php echo esc_url( $cpro_url ); ?>"><?php esc_html_e( 'Manage plugin here', 'convertpro' ); ?></a>
				<?php endif; ?>
				</div>
			<?php
			require_once ABSPATH . 'wp-admin/admin-footer.php';
			exit;
	}
}
	global $bsf_theme_template;
if ( is_multisite() ) {
	$template = $bsf_theme_template;
} else {
	$template = get_template();
}

$current_page      = '';
$redirect_url      = network_admin_url( 'admin.php?page=' . $current_page );
$product_status    = check_bsf_product_status( $product_id );
$current_url       = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$current_r_url     = $current_url . '#addons';
$reset_bundled_url = add_query_arg(
	array(
		'remove-bundled-products' => '',
		'redirect'                => $current_r_url,
	),
	$current_url
);
?>
<div class="clear"></div>
<div class="bsf-sp-screen <?php echo 'extension-installer-' . esc_attr( $product_id ); ?>">

	<div class="cp-addon-wrap">
		<h3 class="bf-ext-sub-title cp-gen-set-title"><?php echo esc_html__( 'Available Addons', 'convertpro' ); ?></h3>

	<?php
	$nonce                       = wp_create_nonce( 'bsf_install_extension_nonce' );
	$brainstrom_bundled_products = ( get_option( 'brainstrom_bundled_products' ) ) ? (array) get_option( 'brainstrom_bundled_products' ) : array();

	if ( isset( $brainstrom_bundled_products[ $product_id ] ) ) {
		$brainstrom_bundled_products = $brainstrom_bundled_products[ $product_id ];
	}

		usort( $brainstrom_bundled_products, 'bsf_sort' );

	if ( ! empty( $brainstrom_bundled_products ) ) :
		$global_plugin_installed = 0;
		$global_plugin_activated = 0;
		$total_bundled_plugins   = count( $brainstrom_bundled_products );
		foreach ( $brainstrom_bundled_products as $key => $bsf_plugin ) {
			if ( ! isset( $bsf_plugin->id ) || '' === $bsf_plugin->id ) {
				continue;
			}
			if ( isset( $request_product_id ) && $request_product_id !== $bsf_plugin->id ) {
				continue;
			}
			$plugin_abs_path = WP_PLUGIN_DIR . '/' . $bsf_plugin->init;
			if ( is_file( $plugin_abs_path ) ) {
				$global_plugin_installed++;

				if ( is_plugin_active( $bsf_plugin->init ) ) {
					$global_plugin_activated++;
				}
			}
		}
		?>
	<input type="hidden" name="bsf_install_nonce" id="bsf_install_nonce_input" value="<?php echo esc_attr( $nonce ); ?>" >
	<ul class="bsf-extensions-list">
		<?php
		foreach ( $brainstrom_bundled_products as $key => $bsf_plugin ) :

			if ( ! isset( $bsf_plugin->id ) || '' === $bsf_plugin->id ) {
				continue;
			}

			if ( isset( $request_product_id ) && $request_product_id !== $bsf_plugin->id ) {
				continue;
			}

			$is_plugin_installed = false;
			$is_plugin_activated = false;

			$plugin_abs_path = WP_PLUGIN_DIR . '/' . $bsf_plugin->init;
			if ( is_file( $plugin_abs_path ) ) {
				$is_plugin_installed = true;

				if ( is_plugin_active( $bsf_plugin->init ) ) {
					$is_plugin_activated = true;
				}
			}

			if ( $is_plugin_installed ) {
				continue;
			}

			if ( $is_plugin_installed && $is_plugin_activated ) {
				$class = 'active-plugin';
			} elseif ( $is_plugin_installed && ! $is_plugin_activated ) {
				$class = 'inactive-plugin';
			} else {
				$class = 'plugin-not-installed';
			}
			?>
		<li id="ext-<?php echo esc_attr( $key ); ?>" class="bsf-extension <?php echo esc_attr( $class ); ?>">
			<span class="cp-ext-inner">
			<?php if ( ! $is_plugin_installed ) : ?>
								<div class="bsf-extension-start-install">
									<div class="bsf-extension-start-install-content">
										<h2><?php echo esc_html__( 'Downloading', 'convertpro' ); ?><div class="bsf-css-loader"></div></h2>
									</div>
								</div>
							<?php endif; ?>
	<div class="top-section">
			<?php if ( ! empty( $bsf_plugin->product_image ) ) : ?>
									<div class="bsf-extension-product-image">
										<div class="bsf-extension-product-image-stick">
											<img src="<?php echo esc_url( $bsf_plugin->product_image ); ?>" class="img" alt="image"/>
										</div>
									</div>
								<?php endif; ?>
		<div class="bsf-extension-info">
			<?php $name = ( isset( $bsf_plugin->short_name ) ) ? $bsf_plugin->short_name : $bsf_plugin->name; ?>
			<h4 class="title"><?php echo esc_html( $name ); ?></h4>
			<p class="desc"><?php echo esc_html( $bsf_plugin->description ); ?><span class="author"><cite>By <?php echo esc_html( $bsf_plugin->author ); ?></cite></span></p>
			<div class="bottom-section">
			<?php
			$button_class = '';
			if ( ! $is_plugin_installed ) {
				if ( ( ! $bsf_plugin->licence_require || 'false' === $bsf_plugin->licence_require ) || 'registered' === $product_status ) {

					$product_link = bsf_exension_installer_url( $product_id );
					$button       = __( 'Install', 'convertpro' );
					$button_class = 'bsf-install-button';
				} elseif ( ( $bsf_plugin->licence_require || 'true' === $bsf_plugin->licence_require ) && 'registered' !== $product_status ) {

					$product_link = bsf_registration_page_url( '', $product_id );
					$button       = __( 'Validate Purchase', 'convertpro' );
					$button_class = 'bsf-validate-licence-button';
				}
			} else {
				$current_name = strtolower( bsf_get_current_name( $bsf_plugin->init, $bsf_plugin->type ) );

				$current_name = preg_replace( '![^a-z0-9]+!i', '-', $current_name );
				if ( is_multisite() ) {
					$product_link = network_admin_url( 'plugins.php#' . $current_name );
				} else {
					$product_link = admin_url( 'plugins.php#' . $current_name );
				}
				$button = __( 'Installed', 'convertpro' );
			}

			?>
			<a target="_blank" rel="noopener noreferrer" class="button button-medium cp-addon-btn extension-button <?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $product_link ); ?>" data-ext="<?php echo esc_attr( $key ); ?>" data-pid="<?php echo esc_attr( $bsf_plugin->id ); ?>" data-bundled="true" data-action="install"><?php echo esc_html( $button ); ?></a>
	</div>
		</div>
	</div>
				</span>
					</li>
				<?php endforeach; ?>
				<?php
				if ( $total_bundled_plugins === $global_plugin_installed ) :
					?>
					<div class="bsf-extensions-no-active">
					</div>
				<?php endif; ?>	
		</ul>

		<!-- Stat - Just Design Purpose -->

		<ul class="bsf-extensions-list">
			<?php
			if ( 0 !== $global_plugin_installed ) :
				foreach ( $brainstrom_bundled_products as $key => $bsf_plugin ) :
					if ( ! isset( $bsf_plugin->id ) || '' === $bsf_plugin->id ) {
						continue;
					}

					if ( isset( $request_product_id ) && $request_product_id !== $bsf_plugin->id ) {
						continue;
					}

						$is_plugin_installed = false;
						$is_plugin_activated = false;

						$plugin_abs_path = WP_PLUGIN_DIR . '/' . $bsf_plugin->init;
					if ( is_file( $plugin_abs_path ) ) {
						$is_plugin_installed = true;

						if ( is_plugin_active( $bsf_plugin->init ) ) {
							$is_plugin_activated = true;
						}
					}

					if ( ! $is_plugin_installed ) {
						continue;
					}

					if ( $is_plugin_installed && $is_plugin_activated ) {
						$class = 'active-plugin';
					} elseif ( $is_plugin_installed && ! $is_plugin_activated ) {
						$class = 'inactive-plugin';
					} else {
						$class = 'plugin-not-installed';
					}
					?>
						<li id="ext-<?php echo esc_attr( $key ); ?>" class="bsf-extension <?php echo esc_attr( $class ); ?>">
						<span class="cp-ext-inner">
							<?php if ( ! $is_plugin_installed ) : ?>
								<div class="bsf-extension-start-install">
									<div class="bsf-extension-start-install-content">
										<h2><?php echo esc_html__( 'Downloading', 'convertpro' ); ?><div class="bsf-css-loader"></div></h2>
									</div>
								</div>
							<?php endif; ?>
							<div class="top-section">
								<?php if ( ! empty( $bsf_plugin->product_image ) ) : ?>
									<div class="bsf-extension-product-image">
										<div class="bsf-extension-product-image-stick">
											<img src="<?php echo esc_url( $bsf_plugin->product_image ); ?>" class="img" alt="image"/>
										</div>
									</div>
								<?php endif; ?>
								<div class="bsf-extension-info">
									<?php $name = ( isset( $bsf_plugin->short_name ) ) ? $bsf_plugin->short_name : $bsf_plugin->name; ?>
									<h4 class="title"><?php echo esc_html( $name ); ?></h4>
									<p class="desc"><?php echo esc_html( $bsf_plugin->description ); ?><span class="author"><cite>By <?php echo esc_html( $bsf_plugin->author ); ?></cite></span></p>
									<div class="bottom-section">
								<?php
									$button_class = '';
								if ( ! $is_plugin_installed ) {
									if ( ( ! $bsf_plugin->licence_require || 'false' === $bsf_plugin->licence_require ) || 'registered' === $product_status ) {
										$product_link = bsf_exension_installer_url( $product_id );
										$button       = __( 'Install', 'convertpro' );
										$button_class = 'bsf-install-button';
									} elseif ( ( $bsf_plugin->licence_require || 'true' === $bsf_plugin->licence_require ) && 'registered' !== $product_status ) {
										$product_link = bsf_registration_page_url( '', $product_id );
										$button       = __( 'Validate Purchase', 'convertpro' );
										$button_class = 'bsf-validate-licence-button';
									}
								} else {
									$current_name = strtolower( bsf_get_current_name( $bsf_plugin->init, $bsf_plugin->type ) );
									$current_name = preg_replace( '![^a-z0-9]+!i', '+', $current_name );
									if ( is_multisite() ) {
										$product_link = network_admin_url( 'plugins.php?s=' . $current_name );
									} else {

										$product_link = admin_url( 'plugins.php?s=' . $current_name );

									}
									$button = __( 'Installed', 'convertpro' );
								}

								?>
								<a target="_blank" rel="noopener noreferrer" class="cp-btn-primary cp-md-btn cp-button-style cp-addon-btn extension-button <?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( $product_link ); ?>" data-ext="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $button ); ?></a>
							</div>
								</div>
							</div>
						</span>
						</li>
					<?php
					endforeach;
				else :
					?>
					<div class="bsf-extensions-no-active"></div>
				<?php endif; ?>
		</ul>

		<!-- End - Just Design Purpose -->
		<?php else : ?>
				<div class="bsf-extensions-no-active">
			<div class="bsf-extensions-title-icon"><span class="dashicons dashicons-download"></span></div>
			<p class="bsf-text-light"><em><?php echo esc_html__( 'No addons available yet!', 'convertpro' ); ?></em></p>

			<div class="bsf-cp-rem-bundle" style="margin-top: 30px;">
				<a class="button-primary" href="<?php echo esc_url( $reset_bundled_url ); ?>">
					<?php esc_html_e( 'Refresh Bundled Addons', 'convertpro' ); ?>
				</a>
			</div>
		</div>

	<?php endif; ?>
	</div> <!-- bend-content-wrap -->
</div> <!-- wrap -->

<?php if ( isset( $_GET['noajax'] ) ) : ?>
	<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('.bsf-install-button').on('click',function(e){
				if((typeof $(this).attr('disabled') !== 'undefined' && $(this).attr('disabled') === 'disabled'))
					return false;
				$('.bsf-install-button').attr('disabled',true);
				var ext = $(this).attr('data-ext');
				var $ext = $('#ext-'+ext);
				$ext.find('.bsf-extension-start-install').addClass('show-install');
			});
		});
	})(jQuery);
	</script>
<?php else : ?>
	<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('.bsf-install-button').on('click',function(e){
				e.preventDefault();

				var is_plugin_installed = is_plugin_activated = false;

				if((typeof $(this).attr('disabled') !== 'undefined' && $(this).attr('disabled') === 'disabled'))
					return false;
				$(this).attr('disabled',true);
				var ext = $(this).attr('data-ext');
				var product_id = $(this).attr('data-pid');
				var action = 'bsf_'+$(this).attr('data-action');
				var security = $( "#bsf_install_nonce_input" ).val();
				var bundled = $(this).attr('data-bundled');
				var $ext = $('#ext-'+ext);
				$ext.find('.bsf-extension-start-install').addClass('show-install');
				var data = {
					'action': action,
					'product_id': product_id,
					'bundled' : bundled,
					'security' : security
				};

				var $product_link = $(this).attr('href');

				// We can also pass the url value separately from ajaxurl for front end AJAX implementations
				jQuery.post(ajaxurl, data, function(response) {

					var redirect = /({.+})/img;
					var matches = redirect.exec(response);

					if ( typeof matches[1] != "undefined" ) {
						var responseObj = jQuery.parseJSON( matches[1] );

						if ( responseObj.redirect != "" ) {
							window.location = responseObj.redirect;
						}
					}

					var blank_response = true;
					var plugin_status = response.split('|');
					var is_ftp = false;
					$.each(plugin_status, function(i,res){
						if(res === 'bsf-plugin-installed') {
							is_plugin_installed = true;
							blank_response = false;
						}
						if(res === 'bsf-plugin-activated') {
							is_plugin_activated = true;
							blank_response = false;
						}
						if(/Connection Type/i.test(response)) {
							is_ftp = true;
						}
					});
					if(is_plugin_installed) {
						$ext.addClass('bsf-plugin-installed');
						$ext.find('.bsf-install-button').addClass('bsf-plugin-installed-button').html('Installed <i class="dashicons dashicons-yes"></i>');
						$ext.find('.bsf-extension-start-install').removeClass('show-install');
					}
					if(is_plugin_activated) {
						$ext.addClass('bsf-plugin-activated');
					}
					if(blank_response) {
						//$ext.find('.bsf-extension-start-install').find('.bsf-extension-start-install-content').html(response);
						if(is_ftp == true) {
							$ext.find('.bsf-extension-start-install').find('.bsf-extension-start-install-content').html('<h3>FTP protected, <br/>redirecting to traditional installer.</h3>');
							$('.bsf-install-button').attr('disabled',true);
							setTimeout(function(){
								window.location = $product_link;
							},2000);
						} else {
							$ext.find('.bsf-extension-start-install').find('.bsf-extension-start-install-content').html('<h3>Something went wrong! Contact plugin author.</h3>');
						}
					}
				});
			});
		});
	})(jQuery);
	</script>
<?php endif; ?>
