<?php
/**
 * The template for displaying search results pages
 * Adapted from wp Twenty_Sixteen
 *
 * @package Ajax Live Search
 * @since Ajax Live Search 1.0
 */

get_header(); 

$users = als_has_authors();

?>

	<section id="primary" class="content-area als-search-content">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() || $users !==false) : ?>
			<div class="als-mainform"><?php get_search_form();?>
			
				<?php 
					
					$before_results = '<span class="als_before_results">' . __("Showing results for ")  . esc_attr__($_GET["s"]) . '</span>';
					
					echo apply_filters('als_before_results', $before_results);?>
					
			</div>
			<div id="#als-results" class="als-results">
			
			<?php
			do_action('als_before_results');
			
			if($users) {
				als_show_authors($users);
			}
			// Start the loop.
			while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class('als-snippet'); ?>>
			<div class="alsthumbnail">
					<?php if (has_post_thumbnail()) {

					?>
						<a class="als-post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
						<?php
						the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) );
						?>
						</a>
						<?php 
					}?>
			</div>
			<div class="alssnippet <?php if (!has_post_thumbnail()) echo 'alsfull'; ?>">
				<header class="entry-header als-header">
					<?php the_title( sprintf( '<h2 class="als-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
				</header><!-- .entry-header -->
				

				<div class="entry-summary">
					<div class="entry-meta"><?php do_action('als_entry_meta');?></div>

					 <?php echo als_snippet(get_the_content()); ?>
				</div><!-- .entry-summary -->
			</div>
			</article><!-- #post-## -->

			

			<?php // End the loop.
			endwhile;

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'als' ),
				'next_text'          => __( 'Next page', 'als' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'als' ) . ' </span>',
			) );
			do_action('als_after_results');
			 //endif
		echo '</div>';
		// If no content, include the "No posts found" template.
		else :
			echo '<div id="#als-results" class="als-results">';
			require_once( plugin_dir_path( __FILE__ ) . 'content-none.php'    );
			echo '</div>';
			
		endif;
		?>
		
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php //get_sidebar(); ?>
<?php get_footer(); ?>
