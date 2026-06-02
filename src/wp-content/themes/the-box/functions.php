<?php
/**
 * Theme functions and definitions
 *
 * @package The Box
 * @since The Box 1.0
 */


/**
* Make theme available for translation.
*/
add_action( 'init', function() {
	load_theme_textdomain( 'the-box', get_template_directory() . '/languages');
} );


if ( ! function_exists( 'thebox_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */
function thebox_setup() {

	// Set the default content width.
	$GLOBALS['content_width'] = 600;

	// Supporting title tag via add_theme_support (since WordPress 4.1)
	add_theme_support( 'title-tag' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Enable support for Post Thumbnail
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 940, 9999 ); //600 pixels wide (and unlimited height)

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'the-box' ),
		'secondary' => __( 'Footer Menu', 'the-box' )
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption' ) );

	// Enable support for Post Formats
	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

	// Set up the WordPress Custom Background Feature.
	add_theme_support( 'custom-background', apply_filters( 'thebox_custom_background_args', array(
		'default-color' => 'f0f3f5',
		'default-image' => '',
	) ) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// This theme styles the visual editor to resemble the theme style,
	add_editor_style( array( 'inc/css/editor-style.css', thebox_fonts_url() ) );

	// Add support for custom color scheme.
	add_theme_support(
		'editor-color-palette', array(
		array(
			'name'  => __( 'Black', 'the-box' ),
			'slug'  => 'black',
			'color' => '#000000',
		),
		array(
			'name'  => __( 'Dark Gray', 'the-box' ),
			'slug'  => 'dark-gray',
			'color' => '#252525',
		),
		array(
			'name'  => __( 'Medium Gray', 'the-box' ),
			'slug'  => 'medium-gray',
			'color' => '#353535',
		),
		array(
			'name'  => __( 'Light Gray', 'the-box' ),
			'slug'  => 'light-gray',
			'color' => '#959595',
		),
		array(
			'name'  => __( 'White', 'the-box' ),
			'slug'  => 'white',
			'color' => '#ffffff',
		),
		array(
			'name'  => __( 'Accent Color', 'the-box' ),
			'slug'  => 'accent',
			'color' => esc_attr( get_option( 'color_primary', '#0fa5d9' ) ),
		),
	) );

}
endif;
add_action( 'after_setup_theme', 'thebox_setup' );


if ( ! function_exists( 'thebox_fonts_url' ) ) :
/**
 * Register Google fonts.
 *
 * @return string Google fonts URL for the theme.
 */
function thebox_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Open Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Source Sans Pro: on or off', 'the-box' ) ) {
		$fonts[] = 'Source Sans Pro:400,700,400italic,700italic';
	}

	/* translators: If there are characters in your language that are not supported by Roboto, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Oxygen: on or off', 'the-box' ) ) {
		$fonts[] = 'Oxygen:400,700,300';
	}

	/* translators: To add an additional character subset specific to your language, translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language. */
	$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'the-box' );

	if ( 'cyrillic' == $subset ) {
		$subsets .= ',cyrillic,cyrillic-ext';
	} elseif ( 'greek' == $subset ) {
		$subsets .= ',greek,greek-ext';
	} elseif ( 'devanagari' == $subset ) {
		$subsets .= ',devanagari';
	} elseif ( 'vietnamese' == $subset ) {
		$subsets .= ',vietnamese';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
			'display' => 'swap',
		), 'https://fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}
endif;


/**
 * Add preconnect for Google Fonts.
 *
 * @since The Box 1.5.2
 *
 * @param array  $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed.
 * @return array URLs to print for resource hints.
 */
function thebox_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'thebox-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'thebox_resource_hints', 10, 2 );


/**
 * Enqueue scripts and styles for the front end.
 *
 */
function thebox_scripts() {

	// Add Google Fonts.
	wp_enqueue_style( 'thebox-fonts', thebox_fonts_url(), array(), null );

	// Add Icons Font, used in the main stylesheet.
	wp_enqueue_style( 'thebox-icons', get_template_directory_uri() . '/assets/css/fa-icons.min.css', array(), '1.7' );

	// Theme stylesheet.
	$theme_version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'thebox-style', get_stylesheet_uri(), array(), $theme_version );

	// Mobile navigation inline style
	wp_add_inline_style( 'thebox-style', thebox_mobile_nav_css() );

	// Main js.
	wp_enqueue_script( 'thebox-script', get_template_directory_uri() . '/assets/js/script.js', array( 'jquery' ), '20220516', true );

	// Comment reply script.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

}
add_action( 'wp_enqueue_scripts', 'thebox_scripts' );


/**
 * Register widgetized area and update sidebar with default widgets
 *
 */
function thebox_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Sidebar Primary', 'the-box' ),
		'id' => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your Sidebar.', 'the-box' ),
		'before_widget' => '<div class="widget-wrapper"><div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="widget-title"><span>',
		'after_title' => '</span></h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Footer', 'the-box' ),
		'id' => 'sidebar-2',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'thebox_widgets_init' );


/**
 * Shim for wp_body_open, ensuring backward compatibility with versions of WordPress older than 5.2.
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}


/**
 * Implement the Custom Header feature
 */
require( get_template_directory() . '/inc/custom-header.php' );


/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/customizer-css.php';


/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';


/**
 * Add theme support for Infinite Scroll.
 * See: https://jetpack.com/support/infinite-scroll/
 */

function thebox_infinite_scroll_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'content',
		'footer'    => 'page',
	) );
}
add_action( 'after_setup_theme', 'thebox_infinite_scroll_setup' );


/*
 * Social Links
 *
 */
function thebox_social_links() {
	$options['facebookurl']    = '';
	$options['flickrurl']      = '';
	$options['githuburl']      = '';
	$options['instagramurl']   = '';
	$options['linkedinurl']    = '';
	$options['mediumurl']      = '';
	$options['pinteresturl']   = '';
	$options['tumblrurl']      = '';
	$options['twitterurl']     = '';
	$options['youtubeurl']     = '';

	// Backward compatibility for Theme versions older than 4.1.3
	if( get_option( 'thebox_theme_options' ) ) {
		$options = get_option( 'thebox_theme_options' ); // Old Theme Options Page Values
	}

	$facebook_url   = get_option( 'facebook_url', $options['facebookurl'] );
	$flickr_url     = get_option( 'flickr_url', $options['flickrurl'] );
	$github_url     = get_option( 'github_url', $options['githuburl'] );
	$instagram_url  = get_option( 'instagram_url', $options['instagramurl'] );
	$linkedin_url   = get_option( 'linkedin_url', $options['linkedinurl'] );
	$medium_url     = get_option( 'medium_url', $options['mediumurl'] );
	$pinterest_url  = get_option( 'pinterest_url', $options['pinteresturl'] );
	$tiktok_url     = get_option( 'tiktok_url', '' );
	$tumblr_url     = get_option( 'tumblr_url', $options['tumblrurl'] );
	$twitter_url    = get_option( 'twitter_url', $options['twitterurl'] );
	$xing_url       = get_option( 'xing_url', '');
	$youtube_url    = get_option( 'youtube_url', $options['youtubeurl'] );

	echo '<ul class="social-links">'; ?>

	<?php if ( $facebook_url != '' ) : ?>
		<li><a href="<?php echo $facebook_url; ?>" class="facebook" title="facebook" target="_blank"><span class="icon-facebook"></span></a></li>
	<?php endif; ?>

	<?php if ( $twitter_url != '' ) : ?>
		<li><a href="<?php echo $twitter_url; ?>" class="twitter" title="twitter" target="_blank"><span class="icon-twitter"></span></a></li>
	<?php endif; ?>

	<?php if ( $linkedin_url != '' ) : ?>
		<li><a href="<?php echo $linkedin_url; ?>" class="linkedin" title="linkedin" target="_blank"><span class="icon-linkedin"></span></a></li>
	<?php endif; ?>

	<?php if ( $instagram_url != '' ) : ?>
		<li><a href="<?php echo $instagram_url; ?>" class="instagram" title="instagram" target="_blank"><span class="icon-instagram"></span></a></li>
	<?php endif; ?>

	<?php if ( $youtube_url != '' ) : ?>
		<li><a href="<?php echo $youtube_url; ?>" class="youtube" title="youtube" target="_blank"><span class="icon-youtube"></span></a></li>
	<?php endif; ?>

	<?php if ( $pinterest_url != '' ) : ?>
		<li><a href="<?php echo $pinterest_url; ?>" class="pinterest" title="pinterest" target="_blank"><span class="icon-pinterest"></span></a></li>
	<?php endif; ?>

	<?php if ( $tiktok_url != '' ) : ?>
		<li><a href="<?php echo $tiktok_url; ?>" class="tiktok" title="tiktok" target="_blank"><span class="icon-tiktok"></span></a></li>
	<?php endif; ?>

	<?php if ( $flickr_url != '' ) : ?>
		<li><a href="<?php echo $flickr_url; ?>" class="flickr" title="flickr" target="_blank"><span class="icon-flickr"></span></a></li>
	<?php endif; ?>

	<?php if ( $tumblr_url != '' ) : ?>
		<li><a href="<?php echo $tumblr_url; ?>" class="tumblr" title="tumblr" target="_blank"><span class="icon-tumblr"></span></a></li>
	<?php endif; ?>

	<?php if ( $medium_url != '' ) : ?>
		<li><a href="<?php echo $medium_url; ?>" class="medium" title="medium" target="_blank"><span class="icon-medium"></span></a></li>
	<?php endif; ?>

	<?php if ( $github_url != '' ) : ?>
		<li><a href="<?php echo $github_url; ?>" class="github" title="github" target="_blank"><span class="icon-github"></span></a></li>
	<?php endif; ?>

	<?php if ( $xing_url != '' ) : ?>
		<li><a href="<?php echo $xing_url; ?>" class="xing" title="xing" target="_blank"><span class="icon-xing"></span></a></li>
	<?php endif; ?>

	<?php if ( get_option( 'thebox_show_rss', 1 ) ) : ?>
		<li><a href="<?php bloginfo( 'rss2_url' ); ?>" class="rss" title="rss" target="_blank"><span class="icon-rss"></span></a></li>
	<?php endif; ?>

	<?php echo '</ul>';
}


/**
 * Filter the except length to 18/40 characters.
 *
 */
function thebox_custom_excerpt_length( $length ) {
	if ( get_option( 'thebox_sidebar_settings' ) == 'grid2-sidebar' ) {
		return 18;
	} elseif ( get_option( 'thebox_sidebar_settings' ) == 'one-column') {
		return 50;
	} else {
		return 40;
	}
}
add_filter( 'excerpt_length', 'thebox_custom_excerpt_length', 999 );


/**
 * Filter the "read more" excerpt string link to the post.
 *
 * @param string $more "Read more" excerpt string.
 * @return string (Maybe) modified "read more" excerpt string.
 */
function thebox_excerpt_more( $more ) {
	return sprintf( ' ... <a class="more-link" href="%1$s">%2$s &raquo;</a>',
		get_permalink( get_the_ID() ),
		__( 'Read More', 'the-box' )
	);
}
add_filter( 'excerpt_more', 'thebox_excerpt_more' );


/**
 * The Box Grid
 */
if ( !function_exists('thebox_grid') ) {
	function thebox_grid() {
		// Get Sidebar Options
		$layout_type = get_option( 'thebox_sidebar_settings', 'content-sidebar' );
		if ( $layout_type == 'grid2-sidebar' ) {
			echo 'col-6 col-sm-6';
		} else {
			echo 'col-12';
		}
	}
}


/*
 * Prints Credits in the Footer
 *
 */
function thebox_credits() {
	$website_credits = '';
	$website_author = get_bloginfo('name');
	$website_date =  date ('Y');
	$website_credits = '&copy; ' . $website_date . ' ' . $website_author;
	echo esc_html( $website_credits );
}


/**
 * Add specific CSS class by filter
 */
function thebox_custom_classes( $classes ) {
	$classes[] = get_option('thebox_sidebar_settings', 'content-sidebar');

	// Adds a class of group-blog to blogs with more than 1 published author
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// return the $classes array
	return $classes;
}
add_filter( 'body_class', 'thebox_custom_classes' );


/**
 * Add styles for mobile navigation
 */
function thebox_mobile_nav_css() {
	$mobile_nav = '
	.menu-toggle,
	button.menu-toggle {
		display: none;
		position: absolute;
		right: 0;
		top: 0;
		width: 40px;
		height: 40px;
		text-decoration: none;
		color: #151515;
		padding: 0;
		margin: 0;
		background-color: transparent;
		border: 0;
		border-radius: 0;
		text-align: center;
		cursor: pointer;
	}
	.menu-toggle:hover,
	.menu-toggle:active,
	button.menu-toggle:hover,
	button.menu-toggle:active {
		background-color: transparent;
		opacity: 1;
	}
	.button-toggle {
		display: block;
		background-color: #151515;
		height: 3px;
		opacity: 1;
		position: absolute;
		transition: opacity 0.3s ease 0s, background 0.3s ease 0s;
		width: 24px;
		z-index: 20;
		left: 8px;
		top: 20px;
		border-radius: 2px;
	}
	.button-toggle:before {
		content: "";
		height: 3px;
		left: 0;
		position: absolute;
		top: -7px;
		transform-origin: center center 0;
		transition: transform 0.3s ease 0s, background 0.3s ease 0s;
		width: 24px;
		background-color: #151515;
		border-radius: inherit;
	}
	.button-toggle:after {
		bottom: -7px;
		content: "";
		height: 3px;
		left: 0;
		position: absolute;
		transform-origin: center center 0;
		transition: transform 0.3s ease 0s, background 0.3s ease 0s;
		width: 24px;
		background-color: #151515;
		border-radius: inherit;
	}
	.toggled-on .button-toggle {
		background-color: transparent;
	}
	.toggled-on .button-toggle:before,
	.toggled-on .button-toggle:after {
		opacity: 1;
		background-color: #fff;
	}
	.toggled-on .button-toggle:before {
		transform: translate(0px, 7px) rotate(-45deg);
	}
	.toggled-on .button-toggle:after {
		transform: translate(0px, -7px) rotate(45deg);
	}
	@media (max-width: 480px) {
		.mobile-navigation {
			padding-left: 20px;
			padding-right: 20px;
		}
	}
	@media (max-width: 768px), (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape) and (-webkit-min-device-pixel-ratio: 1) {
		#site-navigation-sticky-wrapper,
		#site-navigation {
			display: none;
		}
		.menu-toggle,
		button.menu-toggle {
			display: block;
			z-index: 1000;
			border: 0;
			border-radius: 0;
			text-decoration: none;
			text-align: center;
		}
		.mobile-navigation {
			display: block;
			background-color: #151515;
			height: 100vh;
			opacity: 0;
			overflow-y: auto;
			overflow-x: hidden;
			padding: 60px 40px 40px;
			font-size: 16px;
			visibility: hidden;
			position: fixed;
			top: 0;
			right: 0;
			left: 0;
			z-index: 999;
			-webkit-transition: .3s;
			transition: .3s;
			box-sizing: border-box;
		}
		.admin-bar .mobile-navigation {
			padding-top: 100px;
		}
		.mobile-navigation ul {
			list-style-type: none;
		}
		.mobile-navigation ul li {
			display: block;
			margin: 0;
		}
		.mobile-navigation ul ul {
			margin: 0;
			padding: 0 0 0 20px;
			opacity: 0;
			visibility: hidden;
			max-height: 0;
			-webkit-transition: .4s ease-in-out;
			transition: .4s ease-in-out;
		}
		.mobile-navigation .home-link {
			float: none;
			padding: 0 10px;
		}
		.mobile-navigation .home-link a {
			color: #fff;
		}
		.mobile-navigation .icon-home {
			font-size: 18px;
		}
		.mobile-nav-menu {
			padding: 0;
			margin: 0;
		}
		.mobile-nav-menu .icon-home {
			font-size: 18px;
		}
		.mobile-nav-menu > li {
			border-bottom: 1px solid rgba(255,255,255,.1);
		}
		.mobile-nav-menu a {
			display: inline-block;
			width: auto;
			height: auto;
			padding: 15px 10px;
			line-height: 1.5;
			color: #ddd;
			background: transparent;
			text-decoration: none;
			border: 0;
		}
		.mobile-nav-menu a:hover {
			text-decoration: none;
		}
		.mobile-nav-menu ul a {
			padding-left: inherit;
		}
		.mobile-nav-menu a:hover {
			background-color: #151515;
			color: #fff;
		}
		.mobile-navigation.toggled-on {
			opacity: 1;
			visibility: visible;
		}
		.mobile-nav-open {
			overflow: hidden;
		}
		.mobile-navigation li.toggle-on > a ~ ul {
			opacity: 1;
			visibility: visible;
			max-height: 1024px;
		}
		.mobile-navigation .dropdown-toggle {
			display: inline-block;
			position: relative;
			padding: 10px;
			color: #fff;
			vertical-align: middle;
			cursor: pointer;
		}
		.mobile-navigation .dropdown-toggle:before {
			border-color: currentcolor;
			border-style: solid;
			border-width: 0 2px 2px 0;
			border-radius: 2px;
			content: "";
			height: 7px;
			width: 7px;
			position: absolute;
			right: 6px;
			top: 4px;
			transform: rotate(45deg);
		}
		.mobile-navigation .toggle-on > .dropdown-toggle:before {
			transform: rotate(-135deg);
		}
	}
	@media (min-width: 769px) {
		.mobile-navigation {
			display: none;
		}
	}';
	return $mobile_nav;
}


/**
 * Add Upsell "pro" link to the customizer
 *
 */
require_once( trailingslashit( get_template_directory() ) . '/inc/customize-pro/class-customize.php' );


/**
 * Add About Page
 */
require_once get_template_directory() . '/inc/about-page/about-the-box.php';


/**
 * Add Upsell notice
 */
function the_box_notice() {
	$user_id = get_current_user_id();
	if ( ! get_user_meta( $user_id, 'the_box_notice_dismissed' ) ) {
	?>
	<div class="updated notice notice-success is-dismissible the-box-admin-notice">
		<h2 class="welcome-title">
			<?php esc_html_e( 'Welcome! Thank you for choosing The Box WordPress Theme', 'the-box' ); ?>
		</h2>
		<p>
			<?php echo wp_kses_post( __( '<strong>To fully take advantage</strong> of the best our theme can offer, please visit our', 'the-box' ) ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php?page=about_the_box' ) ); ?>"><strong><?php echo esc_html__( 'Welcome Page', 'the-box' ); ?></strong></a>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( 'https://www.designlabthemes.com/the-box-plus-wordpress-theme/?utm_source=WordPress&utm_medium=notice&utm_campaign=the-box_upsell' ); ?>" target="_blank">
				<?php esc_html_e( 'View The Box Plus', 'the-box' ); ?>
			</a>
			<a style="color: #646970;margin-left: 0.5em;" href="<?php echo esc_url( '?the-box-dismissed' ); ?>">
				<?php esc_html_e( 'Dismiss', 'the-box' ); ?>
			</a>
		</p>
	</div>
	<?php
	}
}
add_action( 'admin_notices', 'the_box_notice' );

function the_box_notice_dismissed() {
	$user_id = get_current_user_id();
	if ( isset( $_GET['the-box-dismissed'] ) ) {
		add_user_meta( $user_id, 'the_box_notice_dismissed', 'true', true );
	}
}
add_action( 'admin_init', 'the_box_notice_dismissed' );
