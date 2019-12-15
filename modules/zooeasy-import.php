<?php
/** Import */

if ( ! is_admin() ) {
	die();
}

/**
 * Create menu item
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

	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Gegevens honden, stambomen en tentoonstellingen bijwerken</h1>
	<?php

	$upload_dir  = wp_get_upload_dir();
	$upload_path = $upload_dir['path'];

	if ( isset( $_GET['rc_action'] ) ) {

		if ( 'import' === $_GET['rc_action'] ) {
			echo "<h2>Gegevens in de database verwerken.</h2>";

			$data_rows_names = rough_collie_data();

			rough_collie_import_and_process( $data_rows_names, $upload_path );
		}

	} else {

		echo "<h2>Bestanden controleren</h2>";

		$zooeasy_file_names = rough_collie_files();

		foreach ( $zooeasy_file_names as $name ) {
			if ( ! file_exists( $upload_path. '/' . $name ) ) {
				echo "<p>Bestand $name is nog niet ge-upload, doe dat eerst voordat je verder gaat.</p>";
			}
		}

		printf( '<a href="%s">%s</a>',
			admin_url( '?page=zooeasy-import&rc_action=import' ),
			esc_html( 'Gegevens inporteren in de database' )
		);

	}

	?>
	</div>
	<?php
}

	function rough_collie_import_and_process( $data_rows_names, $upload_path ) {

		// Combinatie.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Combinatie.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Combinatie"], 'Combinatie', 'rough_combinatie' );
		echo $success;

		// Dier.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Dier.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Dier"], 'Dier', 'rough_dier' );
		echo $success;

		// KMGroep.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'KMGroep.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["KMGroep"], 'KMGroep', 'rough_kmgroep' );
		echo $success;

		// KMNaam.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'KMNaam.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["KMNaam"], 'KMNaam', 'rough_kmnaam' );
		echo "<p>$success</p>";

		// KMType.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'KMType.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["KMType"], 'KMType', 'rough_kmtype' );
		echo "<p>$success</p>";

		// Logboek.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Logboek.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Logboek"], 'Logboek', 'rough_logboek' );
		echo "<p>$success</p>";

		// LogboekCategorie.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'LogboekCategorie.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["LogboekCategorie"], 'LogboekCategorie', 'rough_logboekcategorie' );
		echo "<p>$success</p>";

		// Naam.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Naam.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Naam"], 'Naam', 'rough_naam' );
		echo "<p>$success</p>";

		// Persoon.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Persoon.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Persoon"], 'Persoon', 'rough_persoon' );
		echo "<p>$success</p>";

		// PersoonCategorie.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'PersoonCategorie.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["PersoonCategorie"], 'PersoonCategorie', 'rough_persooncategorie' );
		echo "<p>$success</p>";

		// Ras.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Ras.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Ras"], 'Ras', 'rough_ras' );
		echo "<p>$success</p>";

		// Tentoonstelling.csv
		$csv     = array_map('str_getcsv', file( $upload_path . '/' . 'Tentoonstelling.csv' ) );
		$success = rough_collie_process( $csv, $data_rows_names["Tentoonstelling"], 'Tentoonstelling', 'rough_tentoonstelling' );
		echo "<p>$success</p>";

		echo "<p>Alle bestanden zijn verwerkt</p>";
	}


	function rough_collie_process ( $csv, $data_rows_names, $name, $table ) {
		global $wpdb;

		$array_size = count( $csv );

		if ( $array_size > 1 ) {

			// Get the column names of the CSV in an array.
			$csv_head_names = explode( ';', $csv[0][0] );

			// Fill the array with names and keys from the CSV.
			$data_rows_filled = rough_collie_get_key_by_name( $data_rows_names, $csv_head_names );

			// Truncate the database table.
			$sql = "TRUNCATE TABLE $table";
			$wpdb->query( $sql );

			echo "<p>Start vullen $name ($array_size gegevens te vullen)</p>";

			// Add data to the according database tables.

			// Skip the first row (those are the column names).
			unset( $csv[0] );

			// Insert line by line in the database.
			foreach ( $csv as $line ) {

				// Create an array of the line.
				$line   = explode( ';', $line[0] );

				foreach ( $data_rows_filled as $key => $value ) {
					if ( empty ( $line[ $value ] ) ) {
						$line[ $value ] = 0;
					}
					// Create the array with column names and values.
					$data[$key] = sanitize_text_field( $line[ $value ] );
				}

				$wpdb->insert( $table, $data);
			}
		}

		return "<p>$name is klaar.</p>";
	}

/**
 * @param $table_names
 * @param $name
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

function rough_collie_data() {

	$data_rows_names['Dier'] = array (
		'IdentificatieCombinatie',
		'IdentificatieKleur',
		'IdentificatieRas',
		'IdentificatieFokker',
		'Geslacht',
		'Geboortedatum',
		'Overlijdingsdatum',
		'Opmerkingen',
		'Bijzonderheden',
		'Volgnr',
		'IdentificatieTitelVoorNaam',
		'Naam',
		'Registratienummer',
		'RegistratienummerMoeder',
		'RegistratienummerVader',
		'IdentificatieTitelAchterNaam',
		'TentoonstellingJN',
		'Statuskleur',
		'AVK',
		'Roepnaam',
		'Geboortejaar'
	);

	$data_rows_names['Persoon'] = array (
		'CombinatieNaam',
		'Naam'
	);

	$data_rows_names['Tentoonstelling'] = array (
		'IdentificatieNaam',
		'IdentificatieKeurmeester',
		'IdentificatiePlaats',
		'IdentificatiePrijs',
		'IdentificatiePredikaat',
		'Type',
		'Datum',
		'Seizoen',
		'Punten',
		'Keuringsverslag',
		'IdentificatieKlasse',
		'Registratienummer',
		'Jaar'
	);

	return $data_rows_names;

}

function rough_collie_files() {

	$zooeasy_file_names = array (
		'Combinatie.csv',
		'Dier.csv',
		'KMGroep.csv',
		'KMNaam.csv',
		'KMType.csv',
		'Logboek.csv',
		'LogboekCategorie.csv',
		'Naam.csv',
		'Persoon.csv',
		'PersoonCategorie.csv',
		'Ras.csv',
		'Tentoonstelling.csv'
	);

	return $zooeasy_file_names;
}
