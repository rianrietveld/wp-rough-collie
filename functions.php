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

/**
 * Register vasr for collies and kennels.
 *
 * @param $vars array Query vars.
 *
 * @return array Extended query vars.
 */
function rough_collie_query_vars_filter( $vars ) {
	$vars[] .= 'rc_colliename';
	$vars[] .= 'rc_collienumber';
	$vars[] .= 'rc_kennelname';
	$vars[] .= 'rc_kennelletter';
	$vars[] .= 'rc_kennelcountry';
	$vars[] .= 'rc_kennelnumber';
	return $vars;
}
add_filter( 'query_vars', 'rough_collie_query_vars_filter' );

if ( is_admin() ) {

	require get_stylesheet_directory() . '/modules/zooeasy-import.php';

	add_action( 'admin_menu', 'rough_collie_menu_pages' );
	function rough_collie_menu_pages() {
		remove_menu_page( 'edit.php' );                   //Posts
		remove_menu_page( 'edit-comments.php' );          //Comments
	};

}

function rough_collie_disable_search( $query, $error = true ) {

	if (is_search()) {
		$query->is_search       = false;
		$query->query_vars['s'] = false;
		$query->query['s']      = false;

		if ( $error === true )  {
			$query->is_404 = true;
		}
	}
}

if( !is_admin() ) {
	add_action( 'parse_query', 'rough_collie_disable_search' );
	add_filter( 'get_search_form', function() { return null; } );
}

function rough_collie_first_uppercase( $string ) {

	// $string = strtolower( $string );
	// $string = ucwords( esc_html( $string ) );

	return ( $string );
}

