<?php
/**
 * The template part for displaying content
 *
 * @package The Box
 * @since The Box 1.0
 */
?>

<?php // The Box Plus Settings
	$layout_type = get_option( 'thebox_sidebar_settings' );
	$post_lenght = get_theme_mod( 'thebox_post_settings' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php if ( $layout_type == 'grid2-sidebar' ) { ?>
			<?php if ( has_post_thumbnail() && ! post_password_required() && get_option( 'thebox_show_thumbnails', 1 ) ) : ?>
				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>" rel="bookmark">
						<?php the_post_thumbnail( 'medium' ); ?>
					</a>
				</div>
			<?php endif; ?>
		<?php } ?>

		<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		<div class="entry-time">
			<span class="entry-time-day"><?php the_time('j') ?></span>
			<span class="entry-time-month"><?php the_time('M') ?></span>
			<span class="entry-format-icon"></span>
		</div>
	</header><!-- .entry-header -->

	<?php if ( $layout_type != 'grid2-sidebar' && ( get_post_format() || $post_lenght == 'option2' ) ) : ?>

		<div class="entry-content is-layout-constrained has-global-padding">
			<?php if ( has_post_thumbnail() && ! post_password_required() && get_option( 'thebox_show_thumbnails', 1 ) ) : ?>
				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>" rel="bookmark">
						<?php the_post_thumbnail( 'large' ); ?>
					</a>
				</div>
			<?php endif; ?>
			<?php
				the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'the-box' ) );
				wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'the-box' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'the-box' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div> <!-- .entry-content -->

	<?php else : ?>

		<div class="entry-summary">
			<?php if ( get_option( 'thebox_show_thumbnails', 1 ) && has_post_thumbnail() && ! post_password_required() && $layout_type != 'grid2-sidebar' ) : ?>
				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>" rel="bookmark">
						<?php the_post_thumbnail('large'); ?>
					</a>
				</div>
			<?php endif; ?>
			<?php the_excerpt(); ?>
		</div> <!-- .entry-summary -->

	<?php endif; // get_post_format() ?>

	<footer class="entry-footer">
		<p>
			<?php if ( 'post' === get_post_type() ) : // Hide category and tag text for pages on Search ?>
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
				<?php  endif; // End if categories ?>

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

			<?php endif; ?>

			<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
				<span class="comments-link">
					<?php comments_popup_link( __( '<span class="icon-font icon-comment-alt"></span> Leave a comment', 'the-box' ), __( '<span class="icon-font icon-comment"></span> 1 Comment', 'the-box' ), __( '<span class="icon-font icon-comments-alt"></span> % Comments', 'the-box' ) ); ?>
				</span>
				<span class="sep"></span>
			<?php endif; ?>

			<?php edit_post_link( __( 'Edit', 'the-box' ), '<span class="edit-link">', '</span>' ); ?>
		</p>
	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
