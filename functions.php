<?php
/**
 * Rough Coullie functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://developer.wordpress.org/themes/advanced-topics/child-themes/
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Rough_Collie
 * @since 1.0
 */


add_action( 'wp_enqueue_scripts', 'rough_collie_enqueue_styles' );
/**
 * Adds styling from parent theme
 */
function rough_collie_enqueue_styles() {

	$parent_style = 'twentyfifteen-style';

	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get('Version')
	);
}

add_action( 'init', 'rough_collie_create_posttypes' );
/**
 * Create custom post types
 */
function rough_collie_create_posttypes() {
	register_post_type( 'collie',

		array(
			'labels' => array(
				'name' => __( 'Collie' ),
				'singular_name' => __( 'Collie' )
			),
			'public' => true,
			'has_archive' => false,
			'rewrite' => array('slug' => 'collie'),
		)
	);

	register_post_type( 'kennel',

		array(
			'labels' => array(
				'name' => __( 'Kennel' ),
				'singular_name' => __( 'Kennel' )
			),
			'public' => true,
			'has_archive' => false,
			'rewrite' => array('slug' => 'fokker'),
		)
	);

}


if ( is_admin() ) {

	require get_stylesheet_directory() . '/modules/zooeasy-import.php';

	add_action( 'admin_menu', 'rough_collie_menu_pages' );
	function rough_collie_menu_pages() {
		remove_menu_page( 'edit.php' );                   //Posts
		remove_menu_page( 'edit-comments.php' );          //Comments
	};

}

/*
Import setup (dit in de wiki uitschrijven)

per file:
Empty data in DB
Fill with new data

If all are completed:

Update existing collie with new data if exist or else
add new collie: cpt

Update existing kennels with new data if exist or else
add new kennel: cpt

data structure:
Collie:
Registratienummer, Naam,

Kennel:*/



