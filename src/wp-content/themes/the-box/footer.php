<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package The Box
 * @since The Box 1.0
 */
?>
		
	</div><!-- #main .site-main -->

	<footer id="colophon" class="site-footer clearfix">
		<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
			<div id="tertiary" class="clearfix">
				<?php dynamic_sidebar( 'sidebar-2' ); ?>
			</div>
		<?php endif; // end footer widget area ?>
		
		<div class="row">
			<div class="col-6">
				<div class="credits">
					<?php thebox_credits(); ?><br>
					<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'the-box' ) ); ?>"><?php printf( __( 'Powered by %s', 'the-box' ), 'WordPress' ); ?></a>
					<span class="sep"> / </span>
					<a href="<?php echo esc_url( __( 'https://www.designlabthemes.com/', 'the-box' ) ); ?>" rel="nofollow"><?php printf( __( 'Theme by %s', 'the-box' ), 'Design Lab' ); ?></a>
				</div>
			</div>
			<div class="col-6">
				<?php
				if ( has_nav_menu( 'secondary' ) ) { ?>
					<nav id="footer-navigation" class="footer-navigation">
						<?php wp_nav_menu( array( 'theme_location' => 'secondary', 'menu_id' => 'secondary-menu', 'container_class' => 'menu-container', 'depth' => 1 ) ); ?>
					</nav>
				<?php } ?>
			</div>
		</div>
	</footer><!-- #colophon .site-footer -->
	
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>