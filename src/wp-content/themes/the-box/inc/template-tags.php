<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package The Box
 * @since The Box 1.0
 */


if ( ! function_exists( 'thebox_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function thebox_posted_on() {
	
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		get_the_date( DATE_W3C ),
		get_the_date(),
		get_the_modified_date( DATE_W3C ),
		get_the_modified_date()
	);

	printf( '<span class="posted-on"><span class="screen-reader-text">%1$s </span><span class="icon-font icon-date"></span> <a href="%2$s" rel="bookmark">%3$s</a></span>',
		_x( 'Posted on', 'Used before publish date.', 'the-box' ),
		esc_url( get_permalink() ),
		$time_string
	);

	printf( '<span class="byline"><span class="author vcard"><span class="screen-reader-text">%1$s </span> <a class="url fn n" href="%2$s"><span class="icon-font icon-user"></span> %3$s</a></span></span>',
		_x( 'Author', 'Used before post author name.', 'the-box' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		get_the_author()
	);
	
}
endif;


if ( ! function_exists( 'thebox_comment' ) ) :
/**
 * Template for comments and pingbacks.
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function thebox_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'the-box' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'the-box' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<footer>
				<div class="comment-author vcard">
					<?php echo get_avatar( $comment, 40 ); ?>
					<?php printf( __( '%s <span class="says">says:</span>', 'the-box' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
				</div><!-- .comment-author .vcard -->
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'the-box' ); ?></em>
					<br />
				<?php endif; ?>

				<div class="comment-meta commentmetadata">
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time datetime="<?php comment_time( 'c' ); ?>">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s at %2$s', 'the-box' ), get_comment_date(), get_comment_time() ); ?>
					</time></a>
					<?php edit_comment_link( __( '(Edit)', 'the-box' ), ' ' );
					?>
				</div><!-- .comment-meta .commentmetadata -->
			</footer>

			<div class="comment-content"><?php comment_text(); ?></div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif; // ends check for thebox_comment()


/**
 * Returns true if a blog has more than 1 category
 */
function thebox_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so thebox_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so thebox_categorized_blog should return false
		return false;
	}
} 