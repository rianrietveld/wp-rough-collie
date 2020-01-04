<?php
/**
 * Template Name: Collie
 *
 * @package WordPress
 * @subpackage Roughcollie
 * @since Roughcollie 1.0
 */

require get_stylesheet_directory() . '/modules/collies.php';
$collievars = rough_collie_get_collievars();
$title      = rough_collie_get_collie_title( $collievars );

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<article class="hentry">

				<header class="entry-header">
					<?php

					printf( '<h1 class="entry-title">%s</h1>',
						esc_html( $title )
					);

					?>
				</header><!-- .entry-header -->
				<div class="entry-content">

				<?php
				if ( empty( $collievars['rc_colliename'] ) &&  empty( $collievars['rc_collienumber'] ) ) {
					rough_collie_show_search_forms();
				} else {
					rough_collie_show_collie( $collievars );
				}
				?>

				</div>
			</article>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
