<?php
/*
Template Name: Sample WP-BASe Page
*/

/*
WP-BASe loads plugin specific js and css files conditionally, that is, 
only if it finds a WP-BASe shortcode inside the post content of the page, or content of the template to generate the page.
In practise, it checks for "[app_" string using strpos on page load.
Also WP-BASe needs to know your exact WP-BASe content, i.e. shortcodes, in order to update them while doing ajax.

Therefore, when using a custom template you should include WP_BASe shortcodes in the template. 
Probably the most direct way of this is by using do_shortcode function, e.g. echo do_shortcode('[app_book]');
If for some reason this does not work (possibly because of a very special/incompliant theme), you can use one of the 3 alternatives below.

------------------------------------------
1)
Add this code at the END of your template:
<script type="text/template">
[app_book][other WP-BASe shortcodes on your template]
</script>

This code is invisible to the browsers (and valid HTML), but WP-BASe can dedect and use them to modify page by ajax.

--- OR -------------------------------------
2)
Use [app_no_html] in the post content like this:

[app_no_html][app_book][other WP-BASe shortcodes on your template][/app_no_html]

This shortcode set will not produce any HTML output, but tell WP-BASe that it has related shortcodes.
The downside is you need to add this to every post content your template is working for.

--- OR -------------------------------------
3)
Add these inside function.php of your theme to force loading of css and js codes of the plugin:
function add_wpb_files() {
	if ( !function_exists( 'BASe' ) )
		return;
	BASe()->add_default_js( );
	BASe()->load_scripts_styles( );
}
add_action( 'template_redirect', 'add_wpb_files' );


AND

Let WP-BASe know about your template content (Only the WP-BASe shortcodes):
function my_template_content( $content, $post, $ajax ) {
	if ( isset( $post->ID ) && 3 == $post->ID ) // Replace 3 with your real page_id
		return '[app_no_html][app_book][other WP-BASe shortcodes on your template][/app_no_html]';
	else
		return $content;
}
add_filter( 'app_post_content', 'my_template_content', 10, 3 )


*/

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</header><!-- .entry-header -->

						<div class="entry-content">
						<!-- You can add other shortcodes like the below sample -->
							<?php echo do_shortcode('[app_book]'); ?>
							<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:' ) . '</span>', 'after' => '</div>' ) ); ?>
						</div><!-- .entry-content -->
						
						<footer class="entry-meta">
							<?php edit_post_link( __( 'Edit' ), '<span class="edit-link">', '</span>' ); ?>
						</footer><!-- .entry-meta -->
					</article><!-- #post-<?php the_ID(); ?> -->

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>