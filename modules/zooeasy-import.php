<?php

if ( ! is_admin() ) {
	die();
}

add_action('admin_menu', 'rough_collie_admin_menu');

function rough_collie_admin_menu() {
	add_menu_page(
		'Zoo Easy import',
		'Zoo Easy import',
		'edit_posts',
		'zooeasy-import',
		'zooeasy_import',
		'',
		null );
}

function zooeasy_import() {

	global $wpdb;

	echo "<h1>Gegevens honden, stambomen en tentoonstellingen bijwerken in twee stappen</h1>";

	$error_message_all = "Geen files gevonden";

	// create upload directory if it doesn't exist
	$upload_dir = wp_get_upload_dir();
	$upload_path = $upload_dir['path'];

	$zooeasy_file_names = array(
		'Combinatie.csv',
		'Dier.csv',
		'KMGroep.csv',
		'KMNaam.csv',
		'KMType.csv',
		'KMType.csv',
		'Logboek.csv',
		'LogboekCategorie.csv',
		'Naam.csv',
		'Persoon.csv',
		'PersoonCategorie.csv',
		'Ras.csv',
		'Tentoonstelling.csv',
	);


	$csv = array_map('str_getcsv', file($upload_path. '/' . 'Combinatie.csv' ) );

	if ( count($csv) > 1 ) {
			// $wpdb->query( "do something" );
	}

}
