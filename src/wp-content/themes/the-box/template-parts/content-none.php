<?php
/**
 * The template for displaying a "No posts found" message
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

	<article id="post-0" class="post hentry no-results not-found">
		<header class="entry-header">
			<h1 class="entry-title"><?php _e( 'Nothing Found', 'the-box' ); ?></h1>
		</header><!-- .entry-header -->
		<div class="entry-content is-layout-constrained has-global-padding">
			<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

				<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'the-box' ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

			<?php else : ?>

				<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'the-box' ); ?></p>
				<?php get_search_form(); ?>

			<?php endif; ?>
		</div><!-- .entry-content -->
		<footer class="entry-footer"></footer>
	</article><!-- #post-0 .post .no-results .not-found -->
