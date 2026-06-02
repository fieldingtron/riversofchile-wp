<?php
/**
 * Custom header implementation
 *
 * @link https://codex.wordpress.org/Custom_Headers
 *
 * @package The Box
 * @since The Box 1.0
 */


/**
 * Set up the WordPress core custom header feature.
 *
 * @uses thebox_header_style()
 */
function thebox_custom_header_setup() {
	$args = array(
		'default-text-color'     => '252525',
		'default-image'          => '',
		'height'                 => 500,
		'width'                  => 1440,
		'flex-height'            => true,
		'random-default'         => false,
		'wp-head-callback'       => 'thebox_header_style',
	);

	add_theme_support( 'custom-header', $args );
}
add_action( 'after_setup_theme', 'thebox_custom_header_setup' );


/**
 * Style the header text displayed on the blog.
 *
 * get_header_textcolor() options: 515151 is default, hide text (returns 'blank'), or any hex value.
 *
 */
function thebox_header_style() {
	$text_color = get_header_textcolor();

	// If no custom options for text are set, let's bail
	if ( $text_color == get_theme_support( 'custom-header', 'default-text-color' ) )
		return;

	// If we get this far, we have custom styles.
	?>
	<style type="text/css" id="thebox-header-css">
	<?php
		// Has the text been hidden?
		if ( ! display_header_text() ) :
	?>
		.site-header .site-title,
		.site-header .site-description {
			position: absolute;
			clip: rect(1px 1px 1px 1px); /* IE7 */
			clip: rect(1px, 1px, 1px, 1px);
		}
	<?php
		// If the user has set a custom color for the text, use that.
		else :
	?>
		.site-header .site-title a,
		.site-header .site-description {
			color: #<?php echo $text_color; ?>;
		}
	<?php endif; ?>
	</style>
	<?php
}
