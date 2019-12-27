<?php
/**
 * Import functionality.
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
		null );
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

	printf( '<h1 class="wp-heading-inline"%s</h1>',
		esc_html__( 'Gegevens honden en kennels importeren', 'roughcollie' )
	);

	$upload_dir  = wp_get_upload_dir();
	$upload_path = $upload_dir['path'];

	if ( isset( $_GET['rc_action'] ) ) {

		if ( 'import' === $_GET['rc_action'] ) {

			printf( '<h2>%s</h2>',
				esc_html__( 'Gegevens in de database verwerken', 'roughcollie' )
			);

			rough_collie_import_and_process( $upload_path );
		}

	} else {

		printf( '<h2>%s</h2>',
			esc_html__( 'Start', 'roughcollie' )
		);

		printf( '<a href="%s" class="button">%s</a>',
			admin_url( '?page=zooeasy-import&rc_action=import' ),
			esc_html__( 'Gegevens importeren in de database', 'roughcollie' )
		);

		printf( '<p>%s</p>',
			esc_html__( 'Dit kan een paar minuten duren, onderbreek het proces niet', 'roughcollie' )
		);

	}

	?></div><?php

}


/**
 * Collect CSV files and process them.
 *
 * @param $upload_path string Upload path import files.
 *
 * @return string End process.
 */
function rough_collie_import_and_process( $upload_path ) {

	$zooeasy_files = glob( $upload_path . '/*.{csv}', GLOB_BRACE );

	if ( empty( $zooeasy_files ) ) {
		return esc_html__( 'Geen csv bestanden gevonden om te importeren.', 'roughcollie' );
	}

	// Truncate rough_animal and rough_contact.
	global $wpdb;
	$wpdb->query('TRUNCATE TABLE rough_animal');
	$wpdb->query('TRUNCATE TABLE rough_contact');

	foreach ( $zooeasy_files as $zooeasy_file ) {

		printf( '<p>%s: %s</p>',
			esc_html__( 'Verwerken', 'roughcollie' ),
			esc_url( $zooeasy_file )
		);


		if ( file_exists( $zooeasy_file  ) ) {
			$done = rough_collie_ftp_import( $zooeasy_file );
			echo "<p>" . esc_url( $done ) . "</p>";
		}

	}
	return esc_html__( 'Alle bestanden verwerkt.', 'roughcollie' );
	}


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

function rough_collie_ftp_import( $zooeasy_file ) {

	global $wpdb;

	// determine which file and columns
	if ( stristr( $zooeasy_file, 'animal') ) {

		$column_names = array (
			"RegistrationNumber",           // IdentificatieCombinatie      2468/97
			"Gender",                       // Geslacht                     Teef
			"Name",                         // Naam                         Sheranda's Royal Guard Dream
			"TitleInFrontOfName",           // IdentificatieTitelVoorNaam   Ch.
			"Color",                        // IdentificatieKleur           Sable
			"BreederNumber",                // IdentificatieFokker  // in contacts
			"FatherRegistrationNumber",     // RegistratienummerVader
			"MotherRegistrationNumber",     // RegistratienummerMoeder
			"Born",                         // Geboortejaar
			"Deceased"                      // Overlijdingsdatum
		);
		$table              = "rough_animal";


	} elseif ( stristr( $zooeasy_file, 'contact')  ) {

		$column_names = array (
			'Number',
			'BusinessName',
			"Homepage"
		);
		$table        = "rough_contact";

	} else {

		return ( "<p><strong>" . $zooeasy_file . '</strong> is geen invoerbestand</p>' );

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

	return "<p>$zooeasy_file ingevoerd en bestand verwijderd</p>";

}


function rough_collie_remove_csv_attachment( $zooeasy_file ) {

	$base          = basename( $zooeasy_file );
	$wp_upload_dir = wp_upload_dir();
	$filurl        = $wp_upload_dir['url'] . "/" . $base;
	$csv_id        = attachment_url_to_postid( $filurl );

	if ( $csv_id > 0 ) {
		wp_delete_attachment ( $csv_id, true );
	} else {
		echo "<p>Kon $zooeasy_file niet verwijderen, doe het handmatig in de Media Bibiotheek</p>";
	}
}
