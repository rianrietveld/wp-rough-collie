<?php
/**
 * The function for importing the  Zooeasy data.
 *
 * @package WordPress
 * @subpackage Rougcollie
 * @since Rougcollie 1.0
 */

if ( ! is_admin() ) {
	die();
}

/**
 * Create menu item in the Adin.
 */
function rough_collie_admin_menu() {
	add_menu_page(
		'Zoo Easy import',
		'Zoo Easy import',
		'edit_posts',
		'zooeasy-import',
		'zooeasy_import',
		'',
		21 );
}
add_action('admin_menu', 'rough_collie_admin_menu');

/**
 * Make sure .csv uploads are allowed.
 * @param array $mimes Mime types.
 *
 * @return array Mime types plus extra added.
 */
function rough_collie_upload_mimes($mimes = array()) {

	// Add a key and value for the CSV file type
	$mimes['csv'] = "text/csv";
	return $mimes;
}
add_filter('upload_mimes', 'rough_collie_upload_mimes');

/**
 * Main function to start the page.
 */
function zooeasy_import() {

	?> <div class="wrap"><?php

	printf( '<h1 class="wp-heading-inline">%s</h1>',
		esc_html__( 'Import data from collies and kennels.', 'roughcollie' )
	);

	printf( '<p>%s</p>',
		esc_html__( 'Use CSV files with the text animal or contact included in the filename.', 'roughcollie' )
	);

	$upload_dir  = wp_get_upload_dir();
	$upload_path = $upload_dir['path'];

	if ( isset( $_GET['rc_action'] ) ) {

		if ( 'import' === $_GET['rc_action'] ) {

			printf( '<h2>%s</h2>',
				esc_html__( 'Processing the data.', 'roughcollie' )
			);

			rough_collie_import_and_process( $upload_path );
		}

	} else {

		printf( '<h2>%s</h2>',
			esc_html__( 'Start', 'roughcollie' )
		);

		printf( '<a href="%s" class="button">%s</a>',
			admin_url( '?page=zooeasy-import&rc_action=import' ),
			esc_html__( 'Import data into the database.', 'roughcollie' )
		);

		printf( '<p>%s</p>',
			esc_html__( 'This process will take a few minutes, do not interrupt it.', 'roughcollie' )
		);

	}

	?></div><?php

}

/**
 * Collect CSV files and process them.
 *
 * @param $upload_path string Upload path import files.
 *
 * @return void.
 */
function rough_collie_import_and_process( $upload_path ) {

	$zooeasy_files = glob( $upload_path . '/*.{csv}', GLOB_BRACE );

	// Checks on existing CSV files.
	if ( empty( $zooeasy_files ) || 0 === count( $zooeasy_files ) ) {
		esc_html_e( 'No CSV files found to import. Please upload the animals and contact files in the media first.', 'roughcollie' );
		return;
	}

	// Truncate rough_animal and rough_contact.
	global $wpdb;
	$wpdb->query('TRUNCATE TABLE rough_animal');
	$wpdb->query('TRUNCATE TABLE rough_contact');

	foreach ( $zooeasy_files as $zooeasy_file ) {

		if ( file_exists( $zooeasy_file  ) ) {
			rough_collie_ftp_import( $zooeasy_file );
		}

	}

	esc_html_e( 'All files have been processed and deleted.', 'roughcollie' );

}


/**
 * @param $data_rows_names
 * @param $csv_head_names
 *
 * @return array
 */
function rough_collie_get_key_by_name( $data_rows_names, $csv_head_names ) {

	$data_rows_numbers = array();

	foreach ( $data_rows_names as $key => $value ) {
		$found_key = array_search($value, $csv_head_names, true);
		if ( $found_key >= 0 ) {
			$data_rows_numbers[$value] = $found_key;
		}
	}

	return $data_rows_numbers;

}

/**
 * @param $zooeasy_file
 */
function rough_collie_ftp_import( $zooeasy_file ) {

	global $wpdb;

	// Determine which file and columns.
	if ( stristr( $zooeasy_file, 'animal') ) {

		$column_names = array (
			"RegistrationNumber",           // IdentificatieCombinatie      2468/97.
			"Gender",                       // Geslacht                     Teef.
			"Name",                         // Naam                         Sheranda's Royal Guard Dream.
			"TitleInFrontOfName",           // IdentificatieTitelVoorNaam   Ch.
			"Color",                        // IdentificatieKleur           Sable.
			"BreederNumber",                // IdentificatieFokker          // in contacts.
			"OwnerNumber",                  // IdentificatieEigenaar        // in contacts.
			"FatherRegistrationNumber",     // RegistratienummerVader
			"MotherRegistrationNumber",     // RegistratienummerMoeder
			"Born",                         // Geboortejaar
			"Deceased"                      // Overlijdingsdatum
		);
		$table       = "rough_animal";


	} elseif ( stristr( $zooeasy_file, 'contact')  ) {

		$column_names = array (
			'Number',
			//'Initials',
			//'PrefixLastname',
			//'Lastname',
			'BusinessName',
			'Name',
			"Homepage",
			"Country"
		);
		$table        = "rough_contact";

	} else {

		printf( '<p><strong>"%s</strong> %s</p>',
			esc_html( basename( $zooeasy_file ) ),
			esc_html__( 'is not an import file.', 'roughcollie' )
		);


		return;

	}

	$handle = @fopen( $zooeasy_file, "r") ;

	if ( $handle ) {

		// First row , column names.
		$buffer                 = fgets( $handle );
		$csv_head_names = explode(";", $buffer );

		while ( !feof( $handle ) ) {

			$buffer = fgets( $handle );
			$line   = explode( ";", $buffer );

			$data_rows_numbers = rough_collie_get_key_by_name( $column_names, $csv_head_names );

			foreach ( $data_rows_numbers as $key => $value ) {

					if ( empty ( $line[ $value ] ) ) {
						$line[ $value ] = 0;
					}
					$line[ $value ] = trim ( $line[ $value ], '"' );
					$data[$key]     = sanitize_text_field( $line[ $value ] );
					}

				$wpdb->insert( $table, $data );

		}

		fclose( $handle );

		rough_collie_remove_csv_attachment( $zooeasy_file );

	};

	printf( '<p><strong>"%s</strong>: %s</p>',
		esc_html( basename( $zooeasy_file ) ),
		esc_html__( 'imported.', 'roughcollie' )
	);

	return;

}


/**
 * @param $zooeasy_file
 */
function rough_collie_remove_csv_attachment( $zooeasy_file ) {

	$base          = basename( $zooeasy_file );
	$wp_upload_dir = wp_upload_dir();
	$fileurl        = $wp_upload_dir['url'] . "/" . $base;
	$csv_id        = attachment_url_to_postid( $fileurl );

	if ( $csv_id > 0 ) {
		wp_delete_attachment ( $csv_id, true );
	} else {

		printf('<p><strong>%s</strong>%s</p>',
			esc_html( $zooeasy_file ),
			esc_html__( 'has not been removed, please remove it yourself in the media library.', 'roughcollie' )
		);

	}
}
