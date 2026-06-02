<?php
/**
 * The Box Customizer functionality
 *
 * @package The Box
 * @since The Box 1.0
 */


/**
 * Loads Customizer stylesheet.
 */
function thebox_customize_style() {
	wp_enqueue_style('customize-styles', get_template_directory_uri() . '/inc/css/customize-style.css', array(), '20200720' );
}
add_action( 'customize_controls_enqueue_scripts', 'thebox_customize_style' );


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function thebox_customize_preview() {
	wp_enqueue_script( 'thebox_customizer', get_template_directory_uri() . '/inc/js/customize-preview.js', array( 'customize-preview' ), '20200720', true );
}
add_action( 'customize_preview_init', 'thebox_customize_preview' );


/**
 * Custom Classes
 */
if ( class_exists( 'WP_Customize_Control' ) ) {

	class TheBox_Post_Layout_Control extends WP_Customize_Control {
		public function render_content() {
			if ( empty( $this->choices ) )
				return;

			$name = '_customize-radio-' . $this->id;

			?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php
			foreach ( $this->choices as $value => $label ) :
				?>
				<label>
					<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
					<?php echo '<div class="' . esc_attr( $value ) . '"></div>'; ?>
				</label>
				<?php
			endforeach;
		}
	}

	class TheBox_Important_Links extends WP_Customize_Control {

		public $type = "thebox-important-links";

		public function render_content() {
    	$important_links = array(
			'upgrade' => array(
	        'link' => esc_url('https://www.designlabthemes.com/the-box-plus-wordpress-theme/?utm_source=customizer_link&utm_medium=wordpress_dashboard&utm_campaign=the_box_upsell'),
	        'text' => __('Try The Box Plus', 'the-box'),
	        ),
			'theme' => array(
			'link' => esc_url('https://www.designlabthemes.com/the-box-wordpress-theme/'),
			'text' => __('Theme Homepage', 'the-box'),
			),
	        'documentation' => array(
			'link' => esc_url('https://www.designlabthemes.com/the-box-documentation/'),
			'text' => __('Theme Documentation', 'the-box'),
			),
			'rating' => array(
			'link' => esc_url('https://wordpress.org/support/theme/the-box/reviews/#new-post'),
			'text' => __('Rate This Theme', 'the-box'),
			),
			'instagram' => array(
			'link' => esc_url('https://instagram.com/designlabthemes/'),
			'text' => __('Follow on Instagram', 'the-box'),
			),
			'twitter' => array(
			'link' => esc_url('https://twitter.com/designlabthemes/'),
			'text' => __('Follow on Twitter', 'the-box'),
			)
		);
    	foreach ($important_links as $important_link) {
    	echo '<p><a class="button" target="_blank" href="' . esc_url( $important_link['link'] ). '" >' . esc_html($important_link['text']) . ' </a></p>';
    		}
	    }
	}
	class TheBox_Plus_Version extends WP_Customize_Control {
		public $type = 'the-box-plus-version';

		function render_content() {
		$pro_version_text = esc_html( 'Try The Box Plus', 'the-box' );
		$pro_version_link = esc_url( 'https://www.designlabthemes.com/the-box-plus-wordpress-theme/?utm_source=customizer_link&utm_medium=wordpress_dashboard&utm_campaign=the_box_upsell' );

		if ( ! empty( $this->label ) ) {
			echo '<div class="description customize-control-description short-news-custom-description">';
			echo '<strong>' . esc_html( $this->label ) . '</strong> ';
			echo '<a target="_blank" href="' . esc_url( $pro_version_link ). '" >' . esc_html( $pro_version_text ) . '</a>';
			echo '</div>';
			}
		}
	}
}


/**
 * Theme Settings
 */
function thebox_theme_customizer( $wp_customize ) {

	// Add postMessage support for site title and description for the Theme Customizer.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

	// Rename the label to "Site Title and Tagline Color"
	$wp_customize->get_control( 'header_textcolor' )->label = __( 'Site Title and Tagline Color', 'the-box' );

	// Panels
	$wp_customize->add_panel( 'thebox_panel', array(
		'title' => __( 'The Box Settings', 'the-box' ),
		'priority' => 10,
	) );

	// Sections
	$wp_customize->add_section( 'thebox_layout_section' , array(
		'title' => __( 'General', 'the-box' ),
		'description' => __( 'Main Theme Settings.', 'the-box'),
		'priority' => 10,
		'panel' => 'thebox_panel',
	) );

	$wp_customize->add_section( 'thebox_post_section' , array(
		'title' => __( 'Post', 'the-box' ),
		'description'	=> __( 'Settings for Single Post.', 'the-box' ),
		'priority' => 15,
		'panel' => 'thebox_panel',
	) );

	$wp_customize->add_section( 'thebox_page_section' , array(
		'title' => __( 'Page', 'the-box' ),
		'description'	=> __( 'Settings for Static Page.', 'the-box' ),
		'priority' => 20,
		'panel' => 'thebox_panel',
	) );

	$wp_customize->add_section('thebox_social_section' , array(
		'title' => __('Social Links', 'the-box'),
		'priority' => 25,
		'description' => __('Paste here your Social Links.', 'the-box' ),
		'panel' => 'thebox_panel',
	) );

	$wp_customize->add_section('thebox_links_section', array(
		'title' => __('The Box Links', 'the-box'),
		'priority' => 11,
	) );

	// Settings

	// General Options
	$wp_customize->add_setting( 'thebox_sidebar_settings', array(
		'default' => 'content-sidebar',
		'type' => 'option',
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'esc_attr',
	) );

	$wp_customize->add_control( new TheBox_Post_Layout_Control( $wp_customize, 'thebox_sidebar_settings', array(
		'label' => __( 'Site Layout', 'the-box' ),
		'description' => __( 'Layout Settings for Blog Homepage, Category, Tag, Search and Archive Pages.', 'the-box' ),
		'section' => 'thebox_layout_section',
		'size' => false,
		'choices' => array(
			'content-sidebar' => __( 'Sidebar Right', 'the-box' ),
			'sidebar-content' => __( 'Sidebar Left', 'the-box' ),
			'one-column' => __( 'One Column', 'the-box' ),
			'grid2-sidebar' => __( '2 Columns + Sidebar Right', 'the-box' ),
	) ) ) );

	// Excerpt or Full Content
	$wp_customize->add_setting( 'thebox_post_settings', array(
		'default' => 'option1',
		'sanitize_callback' => 'thebox_sanitize_choices',
	) );

	$wp_customize->add_control( 'thebox_post_settings', array(
		'label' => __( 'Post length', 'the-box' ),
		'section' => 'thebox_layout_section',
		'active_callback' => 'thebox_check_layout_options',
		'type' => 'radio',
		'choices' => array(
		'option1' => __( 'Excerpt', 'the-box' ),
		'option2' => __( 'Full Content', 'the-box' ),
	) ) );

	// Display Thumbnails
	$wp_customize->add_setting( 'thebox_show_thumbnails', array(
		'default' => 1,
		'type' => 'option',
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'thebox_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'thebox_show_thumbnails', array(
		'label'    => __( 'Display Post Thumbnails', 'the-box' ),
		'section' => 'thebox_layout_section',
		'type'     => 'checkbox',
	) );

	$wp_customize->add_setting( 'layout_plus_link', array(
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new TheBox_Plus_Version( $wp_customize, 'layout_plus_link', array(
		'label'		=> __( 'Need more options?', 'the-box' ),
		'section'	=> 'thebox_layout_section',
	) ) );

	// Enable Featured Image on Single Post
	$wp_customize->add_setting( 'thebox_enable_featured_image', array(
        'default' => '',
        'capability' => 'edit_theme_options',
		'type'       => 'option',
		'sanitize_callback' => 'thebox_sanitize_checkbox',
    ) );

	$wp_customize->add_control( 'thebox_enable_featured_image', array(
	    'label'    		=> __( 'Enable Featured Image', 'the-box' ),
	    'description'	=> __( 'Display Featured Image on Single Posts.', 'the-box' ),
	    'section'  		=> 'thebox_post_section',
	    'type'     		=> 'checkbox',
	) );

	$wp_customize->add_setting( 'post_plus_link', array(
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new TheBox_Plus_Version( $wp_customize, 'post_plus_link', array(
		'label'		=> __( 'Need more options?', 'the-box' ),
		'section'	=> 'thebox_post_section',
	) ) );

	// Enable Featured Image on Static Page
	$wp_customize->add_setting( 'thebox_page_featured_image', array(
		'default' => '',
        'capability' => 'edit_theme_options',
		'type'       => 'option',
		'sanitize_callback' => 'thebox_sanitize_checkbox',
    ) );

	$wp_customize->add_control( 'thebox_page_featured_image', array(
	    'label'    		=> __( 'Enable Featured Image', 'the-box' ),
	    'description'	=> __( 'Display Featured Image on Pages.', 'the-box' ),
	    'section'  		=> 'thebox_page_section',
	    'type'     		=> 'checkbox',
	) );

	$wp_customize->add_setting( 'page_plus_link', array(
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new TheBox_Plus_Version( $wp_customize, 'page_plus_link', array(
		'label'		=> __( 'Need more options?', 'the-box' ),
		'section'	=> 'thebox_page_section',
	) ) );

	// Social Links

	// Backward compatibility for Theme versions older than 4.1.3
	if ( get_option( 'thebox_theme_options' ) ) {
		$options = get_option( 'thebox_theme_options', '' ); // Old Theme Options Page Values
	} else {
		$options = array(
			'facebookurl' => '',
			'twitterurl' => '',
			'googleplusurl' => '',
			'linkedinurl' => '',
			'instagramurl' => '',
			'youtubeurl' => '',
			'pinteresturl' => '',
			'stumbleuponurl' => '',
			'flickrurl' => '',
			'tumblrurl' => '',
			'mediumurl' => '',
			'githuburl' => '',
		);
	}

	$wp_customize->add_setting( 'thebox_show_rss', array(
        'default' => 1,
        'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'thebox_sanitize_checkbox',
    ) );

	$wp_customize->add_control( 'thebox_show_rss', array(
	    'label'    => __( 'Show the RSS Feed icon', 'the-box' ),
	    'section' => 'thebox_social_section',
	    'type'     => 'checkbox',
	) );

	$wp_customize->add_setting( 'facebook_url', array(
        'default' => $options['facebookurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'facebook_url', array(
        'label' => __( 'Facebook Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'twitter_url', array(
        'default' => $options['twitterurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'twitter_url', array(
        'label' => __( 'Twitter Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'googleplus_url', array(
        'default' => $options['googleplusurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'googleplus_url', array(
        'label' => __( 'Google + Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'linkedin_url', array(
        'default' => $options['linkedinurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'linkedin_url', array(
        'label' => __( 'Linkedin Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'instagram_url', array(
        'default' => $options['instagramurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'instagram_url', array(
        'label' => __( 'Instagram Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'youtube_url', array(
        'default' => $options['youtubeurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'youtube_url', array(
        'label' => __( 'Youtube Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'pinterest_url', array(
        'default' => $options['pinteresturl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'pinterest_url', array(
        'label' => __( 'Pinterest Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'stumbleupon_url', array(
        'default' => $options['stumbleuponurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'stumbleupon_url', array(
        'label' => __( 'StumbleUpon Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'flickr_url', array(
        'default' => $options['flickrurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'flickr_url', array(
        'label' => __( 'Flickr Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'tumblr_url', array(
        'default' => $options['tumblrurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'tumblr_url', array(
        'label' => __( 'Tumblr Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'medium_url', array(
        'default' => $options['mediumurl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'medium_url', array(
        'label' => __( 'Medium Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'github_url', array(
        'default' => $options['githuburl'],
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'github_url', array(
        'label' => __( 'Github Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

    $wp_customize->add_setting( 'xing_url', array(
        'default' => '',
		'type' => 'option',
		'capability' => 'edit_theme_options',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'xing_url', array(
        'label' => __( 'Xing Url', 'the-box' ),
        'section' => 'thebox_social_section',
        'type' => 'text',
    ) );

	// Accent Color
	$wp_customize->add_setting( 'color_primary', array(
		'default' => '#0fa5d9',
		'type' => 'option',
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color_primary', array(
		'label' => __( 'Accent Color', 'the-box' ),
		'section' => 'colors',
	) ) );

	// Footer Background
	$wp_customize->add_setting( 'color_footer', array(
		'default' => '#353535',
		'type' => 'option',
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color_footer', array(
		'label' => __( 'Footer Background', 'the-box' ),
		'section' => 'colors',
	) ) );

	// The Box Links
	$wp_customize->add_setting('thebox_links', array(
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control(new TheBox_Important_Links($wp_customize, 'thebox_links', array(
	  'section' => 'thebox_links_section',
	) ) );

}
add_action('customize_register', 'thebox_theme_customizer');


/**
 * Sanitizes Checkbox
 */
function thebox_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return '';
    }
}


/**
 * Sanitize Radio Buttons and Select Lists
 */
function thebox_sanitize_choices( $input, $setting ) {
    global $wp_customize;

    $control = $wp_customize->get_control( $setting->id );

    if ( array_key_exists( $input, $control->choices ) ) {
        return $input;
    } else {
        return $setting->default;
    }
}


/**
 * Checks conditions to display Post Lenght Option
 */
function thebox_check_layout_options( $control ) {
    if ( $control->manager->get_setting('thebox_sidebar_settings')->value() != 'grid2-sidebar'  ) {
		return true;
    } else {
        return false;
    }
}
