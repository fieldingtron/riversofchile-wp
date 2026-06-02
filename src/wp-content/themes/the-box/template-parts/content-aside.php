<?php
/**
 * The template for displaying posts in the Aside post format
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( is_single() ) : ?>
		
		<div class="entry-content">
			<?php
				the_content();
				wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'the-box' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'the-box' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div><!-- .entry-content -->
		
	<?php else : ?>
	
		<div class="entry-summary">
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'the-box' ) ); ?>
		</div><!-- .entry-summary -->
		
	<?php endif; ?>
		
</article><!-- #post-<?php the_ID(); ?> -->
