<?php

function rough_collie_get_collievars() {

	$collievars = array();
	$collievars['rc_colliename']  = get_query_var( 'rc_colliename' );
	$collievars['rc_collienumber'] = get_query_var( 'rc_collienumber' );
	return $collievars;

}

function rough_collie_get_collie_title( $collievars ) {

	$title = get_the_title();

	if ( ! empty( $collievars['rc_colliename'] )  ) {
		$title = "U zocht op de naam: " . $collievars['rc_colliename'];
	}
	if ( ! empty( $collievars['rc_collienumber'] )  ) {
		$title = "U zocht op het nummer: " . $collievars['rc_collienumber'];
	}

	return $title;
}

function rough_collie_show_search_forms() {

?>

<form action="#" method="get" class="rc-form">
	<div>
		<label for="rc_colliename">Zoek een Collie op naam</label><br />
		<input type="text" name="rc_colliename" id="rc_colliename"><br />
		<input type="submit" name="submit" value="Zoek op naam"><br />
	</div>
</form>

<form action="#" method="get" class="rc-form">
	<div>
		<label for="rc_collienumber">Zoek een Collie op registratienummer</label><br />
		<input type="text" name="rc_collienumber" id="rc_collienumber"><br />
		<input type="submit" name="submit" value="Zoek op nummer"><br />
	</div>
</form>

<?php

}

function rough_collie_show_collie( $collievars ) {

	$animal_data = array();

	if ( ! empty( $collievars['rc_collienumber'] ) ) {
		$animal_data = rough_collie_get_animal_by_number( $collievars['rc_collienumber'] );
	} elseif( ! empty( $collievars['rc_colliename'] ) ) {
		$animal_data = rough_collie_get_animal_by_name( $collievars['rc_colliename'] );
	}

	if ( empty ($animal_data) ) {
		echo "niets gevonden";
		return;
	}

	rough_collie_show_data( $animal_data );
}

function rough_collie_get_animal_by_name( $name ) {

	global $wpdb;

	$name        = sanitize_text_field( $name );
	$animal_data = $wpdb->get_row( "SELECT * FROM rough_animal WHERE Name = '$name'" );

	return $animal_data;
}

function rough_collie_get_animal_by_number( $number ) {

	global $wpdb;

	$number      = sanitize_text_field( $number );
	$animal_data = $wpdb->get_row( "SELECT * FROM rough_animal WHERE RegistrationNumber = $number" );

	return $animal_data;
}

function rough_collie_show_data( $animal_data ) {

	foreach ( $animal_data as $key => $value ) {

		if ( $value === 0 ||
		     $value === "0" ||
		     $value === NULL ||
		     $value === "" ||
		     stristr( $value, 'onbekend' ) ) {

			$animal_data->$key = "-";
		}
	}
	$ch         = ( $animal_data->TitleInFrontOfName === "-"  ? "" : $animal_data->TitleInFrontOfName );
	$name       = $ch . " " . $animal_data->Name;
	$number     = $animal_data->RegistrationNumber;
	$color      = $animal_data->Color;
	$gender     = $animal_data->Gender;
	$born       = $animal_data->Born;
	$deceased   = $animal_data->Deceased;

	$breeder    = $animal_data->BreederNumber;
	if ( $breeder !==  "-" ) {
		$breeder_data = rough_collie_get_breeder_by_number( $breeder );
		if ( empty ( $breeder_data ) ) {
			$breeder = "-";
		} else {
			$breeder_name = $breeder_data->BusinessName;
			$breeder_url  = '/zoek-een-kennel/?rc_kennelnumber=' . $breeder;
			$breeder = sprintf(
				'<a href="%s">%s</a>',
						esc_url( $breeder_url ),
						esc_html($breeder_name)
			);
		}
	}

	//$pedigree_link  = esc_url( $breeder );
	//$offspring_link = esc_url( $breeder );
	//$shows_link     = esc_url( $breeder );


	?>

	<dl>
		<dt>Naam</dt><dd><?php echo esc_html( $name ); ?></dd>
		<dt>Stamboomnummer</dt><dd><?php echo esc_html( $number ); ?></dd>
		<dt>Kleur</dt><dd><?php echo esc_html( $color ); ?></dd>
		<dt>Geslacht</dt><dd><?php echo esc_html( $gender ); ?></dd>
		<dt>Geboortedatum</dt><dd><?php echo esc_html( $born ); ?></dd>
		<dt>Overlijdensdatum</dt><dd><?php echo esc_html( $deceased ); ?></dd>
		<dt>Kennel of Fokker</dt><dd><?php echo $breeder; ?></dd>
	</dl>

	<?php

}

function rough_collie_get_breeder_by_number( $number ) {

	global $wpdb;

	$number        = sanitize_text_field( $number);
	$breeder_data  = $wpdb->get_row( "SELECT * FROM rough_contact WHERE Number = $number" );

	return $breeder_data;

}
