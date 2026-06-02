<?php
/**
 * The Sidebar Primary containing the main widget areas.
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

	<aside id="secondary" class="sidebar widget-area">
		<?php do_action( 'before_sidebar' ); ?>
		<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
	
			<div id="search" class="widget widget_search">
				<?php get_search_form(); ?>
			</div>
	
			<div id="archives" class="widget widget_archive">
				<h3 class="widget-title"><span><?php _e( 'Archives', 'the-box' ); ?></span></h3>
				<ul>
					<?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
				</ul>
			</div>
	
			<div id="meta" class="widget widget_meta">
				<h3 class="widget-title"><span><?php _e( 'Meta', 'the-box' ); ?></span></h3>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<?php wp_meta(); ?>
				</ul>
			</div>
	
		<?php endif; // end sidebar widget area ?>
	</aside><!-- #secondary .widget-area -->
