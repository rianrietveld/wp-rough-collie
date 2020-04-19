<?php
/**
 * Template Name: Kennel
 *
 * @package WordPress
 * @subpackage Rough Collie
 * @since Rough Collie 1.0
 */

require get_stylesheet_directory() . '/modules/kennels.php';
$kennelvars = rough_collie_get_kennelvars();
$title      = rough_collie_get_kennel_title( $kennelvars );

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

					if ( empty( $kennelvars['rc_kennelname'] ) &&
					     empty( $kennelvars['rc_kennelletter'] ) &&
					     empty( $kennelvars['rc_kennelcountry'] ) &&
					     empty( $kennelvars['rc_kennelnumber'] ) ) {
						rough_collie_show_kennel_search_forms();
					} elseif ( ! empty( $kennelvars['rc_kennelnumber'] ) ) {
						rough_collie_show_single_kennel_data( $kennelvars['rc_kennelnumber'] );
					} else {
						rough_collie_show_kennels( $kennelvars );
					}
					?>

				</div>
			</article>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php
get_footer();
