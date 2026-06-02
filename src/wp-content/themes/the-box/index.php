<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package The Box
 * @since The Box 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
		
		<?php if ( have_posts() ) : ?>
		
			<div class="posts-loop clearfix">
				<div class="row">
		
					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>
						
						<div class="<?php thebox_grid(); ?>">
							<?php get_template_part( 'template-parts/content', get_post_format() ); ?>
						</div>
	
					<?php endwhile; ?>
					
				</div>
			</div><!-- .posts-loop -->
			
			<?php the_posts_pagination( array(
			'mid_size' => 2,
			'prev_text' => __( '&larr;', 'the-box' ),
			'next_text' => __( '&rarr;', 'the-box' ),
			) ); ?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>


		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php
$blog_layout = get_option( 'thebox_sidebar_settings' );
if ( $blog_layout != 'one-column' && $blog_layout != 'grid3' ) {
	get_sidebar();	
}
?>
<?php get_footer(); ?>
