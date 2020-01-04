<?php
/**
 * Roughcollie functions and definitions
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

function rough_collie_child_theme_slug_setup() {
	load_child_theme_textdomain( 'parent-theme-slug', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'rough_collie_child_theme_slug_setup' );


/**
 * Register vars for collies and kennels.
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

