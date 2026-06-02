<?php
/**
 * The template part for displaying single posts
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<div class="entry-time">
			<span class="entry-time-day"><?php the_time('j') ?></span>
			<span class="entry-time-month"><?php the_time('M') ?></span>
			<span class="entry-format-icon"></span>
		</div>
		<div class="entry-meta">
			<?php thebox_posted_on(); ?>
			<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
				<span class="comments-link">
					<?php comments_popup_link( '<span class="icon-font icon-comment-alt"></span> 0', '<span class="icon-font icon-comments-alt"></span> 1', '<span class="icon-font icon-comments-alt"></span> %' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</header><!-- .entry-header -->

	<div class="entry-content is-layout-constrained has-global-padding">
		<?php if ( has_post_thumbnail() ) { ?>
    		<?php if  ( get_option( 'thebox_enable_featured_image' ) ) { ?><div class="featured-image"><?php the_post_thumbnail( 'large' ); ?></div><?php } ?>
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
			<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ', ', 'the-box' ) );
				if ( $categories_list && thebox_categorized_blog() ) :
			?>
			<span class="cat-links">
				<span class="icon-font icon-category-alt"></span>
				<?php printf( __( '%1$s', 'the-box' ), $categories_list ); ?>
			</span>
			<span class="sep"></span>
			<?php endif; // End if categories ?>

			<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '', __( ', ', 'the-box' ) );
				if ( $tags_list ) :
			?>
			<span class="tags-links">
				<span class="icon-font icon-tag-alt"></span>
				<?php printf( __( '%1$s', 'the-box' ), $tags_list ); ?>
			</span>
			<span class="sep"></span>
			<?php endif; // End if $tags_list ?>
			<?php edit_post_link( __( 'Edit', 'the-box' ), '<span class="edit-link">', '</span>' ); ?>
		</p>
	</footer><!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
