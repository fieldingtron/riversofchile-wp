<?php
/**
 * The template used for displaying page content
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header><!-- .entry-header -->

		<div class="entry-content is-layout-constrained has-global-padding">
			<?php if ( has_post_thumbnail() ) { ?>
				<?php if  ( get_option( 'thebox_page_featured_image' ) ) { ?><div class="featured-image"><?php the_post_thumbnail( 'large' ); ?></div><?php } ?>
			<?php } // has_post_thumbnail ?>
			<?php
				the_content();
				wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'the-box' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span class="page-number">',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'the-box' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<p>
				<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
				<span class="comments-link">
					<?php comments_popup_link( __( '<span class="icon-font icon-comment-alt"></span> Leave a comment', 'the-box' ), __( '<span class="icon-font icon-comment"></span> 1 Comment', 'the-box' ), __( '<span class="icon-font icon-comments-alt"></span> % Comments', 'the-box' ) ); ?>
				</span>
				<?php endif; ?>
				<?php edit_post_link( __( 'Edit', 'the-box' ), '<span class="sep"></span><span class="edit-link">', '</span>' ); ?>
			</p>
		</footer>
	</article><!-- #post-<?php the_ID(); ?> -->
