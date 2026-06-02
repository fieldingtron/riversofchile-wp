<?php
/**
 * Create About Page Menu Item.
 */
function the_box_admin_menu() {
	add_theme_page( 'About The Box', 'About The Box', 'manage_options', 'about_the_box', 'the_box_about_page', 100  );
}
add_action( 'admin_menu', 'the_box_admin_menu' );


/**
 * Register and enqueue a custom stylesheet.
 */
function the_box_admin_style( $hook ) {
	if ( 'appearance_page_about_the_box' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'the-box-about-page-style', get_template_directory_uri() . '/inc/about-page/css/about-page.css', array(), '1.0.3' );
}
add_action( 'admin_enqueue_scripts', 'the_box_admin_style' );


/**
 * Render About Page HTML.
 */
function the_box_about_page() { ?>

	<?php
	$dl_theme_data = wp_get_theme();
	if ( is_child_theme() ) {
		$dl_theme_name = $dl_theme_data->parent()->get( 'Name' );
		$dl_theme_slug = $dl_theme_data->parent()->get_template();
	} else {
		$dl_theme_name = $dl_theme_data->get( 'Name' );
		$dl_theme_slug = $dl_theme_data->get_template();
	}
	$dl_theme_version  = $dl_theme_data->get('Version');
	$dl_theme_utm      = '?utm_source=WordPress&utm_medium=about_page&utm_campaign=' . $dl_theme_slug . '_upsell';
	$dl_theme_pro_name = $dl_theme_name . ' Plus';
	$dl_theme_pro_slug = $dl_theme_slug . '-plus';
	?>

	<div class="wrap about-wrap dl-about-wrap">
		<div class="dl-about-header">

			<div class="dl-about-header-top">
				<h1><?php printf( __( 'Welcome to %s', 'the-box' ), $dl_theme_name ); ?></h1>
				<div class="about-text">
					<p>
						<?php printf( __( 'You have successfully installed the %s WordPress theme!', 'the-box' ), $dl_theme_name ); ?>
						<br>
						<span class="dl-theme-info-label">
							<?php
							esc_html_e( 'Theme version:', 'the-box' );
							echo esc_html( ' ' . $dl_theme_version );
							?>
						</span>
					</p>
				</div>
			</div>

			<!-- Display tabs -->
			<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'the_box_tab_1'; ?>

			<nav class="nav-tab-wrapper wp-clearfix">
				<a href="?page=about_the_box&tab=the_box_tab_1" class="nav-tab <?php echo $active_tab == 'the_box_tab_1' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Getting Started', 'the-box' ); ?>
				</a>
				<a href="?page=about_the_box&tab=the_box_tab_2" class="nav-tab <?php echo $active_tab == 'the_box_tab_2' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Free vs Pro', 'the-box' ); ?>
				</a>
			</nav>
		</div><!-- .dl-about-header -->

		<hr class="wp-header-end">

		<!-- Display content for current tab -->
		<div class="dl-about-content">

			<?php if ( $active_tab == 'the_box_tab_1' ):  ?>

				<!-- Getting Started tab -->

				<div class="dl-about-section dl-has-2-columns">

					<div class="dl-about-column">
						<figure class="dl-thumb"><a href="<?php echo esc_url( 'https://www.designlabthemes.com/' . $dl_theme_pro_slug . '-wordpress-theme/' . $dl_theme_utm ) ?>" target="_blank"><img src="<?php echo esc_url( get_template_directory_uri() . '/inc/about-page/images/screenshot.jpg' ) ?>"/></a></figure>
					</div>

					<div class="dl-about-column">
						<h3><?php printf( __( 'Upgrade to %s', 'the-box' ), $dl_theme_pro_name ); ?></h3>
						<p>
							<?php printf( __( 'If you &hearts; %1$s, you’ll love all the extra features %2$s come with.', 'the-box' ), $dl_theme_name, $dl_theme_pro_name ); ?>
						</p>
						<ul class="dl-feature-list">
							<li>
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Additional Theme Features', 'the-box' ) ?>
							</li>
							<li>
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Blog Templates', 'the-box' ) ?>
							</li>
							<li>
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Premium Support', 'the-box' ) ?>
							</li>
						</ul>
						<p>
							<a href="<?php echo esc_url( 'https://www.designlabthemes.com/' . $dl_theme_pro_slug . '-wordpress-theme/' . $dl_theme_utm ) ?>" target="_blank" class="button button-primary button-hero">
								<?php printf( __( 'Get %s now', 'the-box' ), $dl_theme_pro_name ); ?>
							</a>
						<p>
					</div>
				</div><!-- .dl-about-section -->
				<div class="dl-about-section dl-has-1-column">
					<div class="dl-about-column">
						<div class="dl-icon-text">
							<div class="dl-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="48" height="48" aria-hidden="true" focusable="false"><path d="M7 13.8h6v-1.5H7v1.5zM18 16V4c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2zM5.5 16V4c0-.3.2-.5.5-.5h10c.3 0 .5.2.5.5v12c0 .3-.2.5-.5.5H6c-.3 0-.5-.2-.5-.5zM7 10.5h8V9H7v1.5zm0-3.3h8V5.8H7v1.4zM20.2 6v13c0 .7-.6 1.2-1.2 1.2H8v1.5h11c1.5 0 2.7-1.2 2.7-2.8V6h-1.5z"></path></svg>
							</div>
							<div class="dl-text">
								<h3><?php esc_html_e( 'Read Full Documentation', 'the-box' ) ?></h3>
								<p class="about">
									<?php esc_html_e( 'Need any help to setup and configure the theme? Please check our full documentation for detailed information on how to use it.', 'the-box' ) ?>
								</p>
								<p class="about">
									<a href="<?php echo esc_url( 'https://www.designlabthemes.com/documentation/' . $dl_theme_slug . '-documentation/' ) ?>" target="_blank"><?php esc_html_e( 'Read Documentation', 'the-box' ) ?></a>
								</p>
							</div>
						</div>

						<div class="dl-icon-text">
							<div class="dl-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="48" height="48" aria-hidden="true" focusable="false"><path d="M12 4c-4.4 0-8 3.6-8 8v.1c0 4.1 3.2 7.5 7.2 7.9h.8c4.4 0 8-3.6 8-8s-3.6-8-8-8zm0 15V5c3.9 0 7 3.1 7 7s-3.1 7-7 7z"></path></svg>
							</div>
							<div class="dl-text">
								<h3><?php esc_html_e( 'Customize your site', 'the-box' ) ?></h3>
								<p class="about">
									<?php esc_html_e( 'Using the WordPress Customizer you can easily customize every aspect of the theme.', 'the-box' ) ?>
								</p>
								<p class="about">
									<a href="<?php echo esc_url( admin_url( 'customize.php' ) ) ?>" class="button"><?php esc_html_e( 'Start Customize', 'the-box' ) ?></a>
								</p>
							</div>
						</div>

						<div class="dl-icon-text">
							<div class="dl-icon">
								<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="48" height="48" aria-hidden="true" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.68822 16.625L5.5 17.8145L5.5 5.5L18.5 5.5L18.5 16.625L6.68822 16.625ZM7.31 18.125L19 18.125C19.5523 18.125 20 17.6773 20 17.125L20 5C20 4.44772 19.5523 4 19 4H5C4.44772 4 4 4.44772 4 5V19.5247C4 19.8173 4.16123 20.086 4.41935 20.2237C4.72711 20.3878 5.10601 20.3313 5.35252 20.0845L7.31 18.125ZM16 9.99997H8V8.49997H16V9.99997ZM8 14H13V12.5H8V14Z"></path></svg>
							</div>
							<div class="dl-text">
								<h3><?php esc_html_e( 'Rate us', 'the-box' ) ?></h3>
								<p class="about">
									<?php echo wp_kses_post( sprintf( __( 'Please rate us <a target="_blank" href="https://wordpress.org/support/theme/%s/reviews/?filter=5" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733; on WordPress.org</a> to help us spread the word. Thank you from Design Lab Themes!', 'the-box' ), $dl_theme_slug ) ) ?>
								</p>
							</div>
						</div>
					</div>
				</div><!-- .dl-about-section -->

			<?php elseif ( $active_tab == 'the_box_tab_2' ) : ?>

				<!-- Free vs PRO tab -->
				<div class="dl-about-section">

					<div class="dl-free-pro-cta">
						<div class="dl-free-pro-box">
							<p><?php printf( __( 'Need more customizations and flexibility? Try %s', 'the-box' ), $dl_theme_pro_name ); ?></p>
							<p><a href="<?php echo esc_url( 'https://www.designlabthemes.com/' . $dl_theme_pro_slug . '-wordpress-theme/' . $dl_theme_utm ) ?>" target="_blank" class="button button-primary button-hero"><?php printf( __( 'Get %s now', 'the-box' ), $dl_theme_pro_name ); ?></a></p>
						</div>
					</div>

					<table class="dl-free-pro-table">
						<thead>
							<tr>
								<th></th>
								<th><?php esc_html_e( 'Free', 'the-box' ) ?></th>
								<th><?php esc_html_e( 'PRO', 'the-box' ) ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Basic Theme Customization', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'Pick an accent color, upload your logo, and easily customize your website', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'WordPress Block Editor', 'the-box' ) ?></h3>
									<p><?php printf( __( '%s is optimized for the Block Editor with nice styling for blocks and combinations.', 'the-box' ), $dl_theme_name ); ?><br> <a href="<?php echo esc_url( 'https://wordpress.org/documentation/article/wordpress-block-editor/' ) ?>" target="_blank"><?php esc_html_e( 'Read more about the WordPress Block Editor', 'the-box' ) ?></a></p>
								</td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Fast loading', 'the-box' ) ?></h3>
									<p><?php printf( __( 'With %s your website loads fast and runs smoothly.', 'the-box' ), $dl_theme_name ); ?></p>
								</td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'SEO Ready & AMP Support', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'Each page is search-engine-optimized (SEO) and fully AMP compatible (Official AMP plugin required)', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-yes"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Priority Support', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'You will benefit of our full support for any issues you have with the theme.', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Blog Templates', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'Six Homepage Layouts to choose from. Featured Posts Area to showcase your most important content at the top of the Homepage.', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Advanced Theme Customization', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'Make your website unique with multiple Layout Options, Header Options, Post Slider, and more!', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td>
									<h3><?php esc_html_e( 'Colors and Typography', 'the-box' ) ?></h3>
									<p><?php esc_html_e( 'Easily adjust theme elements\' color, font-family, and font styles.', 'the-box' ) ?></p>
								</td>
								<td><span class="dashicons dashicons-no"></span></td>
								<td><span class="dashicons dashicons-yes"></span></td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">
									<a href="<?php echo esc_url( 'https://www.designlabthemes.com/' . $dl_theme_pro_slug . '-wordpress-theme/' . $dl_theme_utm ) ?>" target="_blank" class="button button-primary button-hero">
										<?php printf( __( 'Get %s now', 'the-box' ), $dl_theme_pro_name ); ?>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!--.dl-about-section -->

			<?php endif; ?>

		</div><!-- .dl-about-content -->

	</div><!-- .dl-about-wrap -->

<?php }
