<?php
/**
 * The function for displaying the collie information.
 *
 * @package WordPress
 * @subpackage Rougcollie
 * @since Rougcollie 1.0
 */


function rough_collie_get_collievars() {

	$collievars = array();
	$collievars['rc_colliename']  = get_query_var( 'rc_colliename' );
	$collievars['rc_collienumber'] = get_query_var( 'rc_collienumber' );
	return $collievars;

}

function rough_collie_get_collie_title( $collievars ) {

	$title = get_the_title();

	if ( ! empty( $collievars['rc_colliename'] )  ) {
		$title = $collievars['rc_colliename'];
	}
	if ( ! empty( $collievars['rc_collienumber'] )  ) {
		$title = __("Registration number", 'roughcollie') . ": " .$collievars['rc_collienumber'];
	}

	return $title;
}

function rough_collie_show_search_forms() {

	?>

	<form action="#" method="get" class="rc-form">
		<div>
			<label for="rc_colliename"><?php esc_html_e('Search a Collie by name', 'roughcollie'); ?></label><br />
			<input type="text" name="rc_colliename" id="rc_colliename"><br />
			<input type="submit" name="submit" value="<?php esc_html_e('Search by name', 'roughcollie'); ?>"><br />
		</div>
	</form>

	<form action="#" method="get" class="rc-form">
		<div>
			<label for="rc_collienumber"><?php esc_html_e('Search a Collie by registration number', 'roughcollie'); ?></label><br />
			<input type="text" name="rc_collienumber" id="rc_collienumber"><br />
			<input type="submit" name="submit" value="<?php esc_html_e('Search by number', 'roughcollie'); ?>"><br />
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
		esc_html_e( 'Nothing found', 'roughcollie' );
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

	$name      = rough_collie_compose_animal_name( $animal_data );
	$number    = $animal_data->RegistrationNumber;
	$color     = $animal_data->Color;
	$gender    = $animal_data->Gender;
	$born      = $animal_data->Born;
	$deceased  = $animal_data->Deceased;
	$breeder   = rough_collie_contact_data( $animal_data->BreederNumber, 1 );
	$owner     = rough_collie_contact_data( $animal_data->OwnerNumber, 0 );

	echo $animal_data->OwnerNumber;

	?>

	<dl>
		<dt><?php esc_html_e('Name', 'roughcollie'); ?></dt><dd><?php echo esc_html( $name['name'] ); ?></dd>
		<dt><?php esc_html_e('Registration number', 'roughcollie'); ?></dt><dd><?php echo esc_html( $number ); ?></dd>
		<dt><?php esc_html_e('Colour', 'roughcollie'); ?></dt><dd><?php echo esc_html( $color ); ?></dd>
		<dt><?php esc_html_e('Sex', 'roughcollie'); ?></dt><dd><?php echo esc_html( $gender ); ?></dd>
		<dt><?php esc_html_e('Date of birth', 'roughcollie'); ?></dt><dd><?php echo esc_html( $born ); ?></dd>
		<dt><?php esc_html_e('Deceased on', 'roughcollie'); ?></dt><dd><?php echo esc_html( $deceased ); ?></dd>
		<dt><?php esc_html_e('Breeder', 'roughcollie'); ?></dt><dd><?php echo $breeder; ?></dd>
		<dt><?php esc_html_e('Owner', 'roughcollie'); ?></dt><dd><?php echo $owner; ?></dd>

	</dl>

	<?php

	// Pedigree.
	$mother_id  = $animal_data->MotherRegistrationNumber;
	$father_id  = $animal_data->FatherRegistrationNumber;

	if ( $mother_id !== "" || $father_id !== ""  ) {

		// First column.
		$father  = rough_collie_get_animal_by_number( $father_id );
		$mother  = rough_collie_get_animal_by_number( $mother_id );

		// Second column.
		$fatherF  = rough_collie_get_animal_by_number( $father->FatherRegistrationNumber );
		$fatherM  = rough_collie_get_animal_by_number( $father->MotherRegistrationNumber );

		$motherF  = rough_collie_get_animal_by_number( $mother->FatherRegistrationNumber );
		$motherM  = rough_collie_get_animal_by_number( $mother->MotherRegistrationNumber );

		// Third column.
		$fatherFF  = rough_collie_get_animal_by_number( $fatherF->FatherRegistrationNumber );
		$fatherFM  = rough_collie_get_animal_by_number( $fatherF->MotherRegistrationNumber );
		$fatherMF  = rough_collie_get_animal_by_number( $fatherM->FatherRegistrationNumber );
		$fatherMM  = rough_collie_get_animal_by_number( $fatherM->MotherRegistrationNumber );

		$motherFF  = rough_collie_get_animal_by_number( $motherF->FatherRegistrationNumber );
		$motherFM  = rough_collie_get_animal_by_number( $motherF->MotherRegistrationNumber );
		$motherMF  = rough_collie_get_animal_by_number( $motherM->FatherRegistrationNumber );
		$motherMM  = rough_collie_get_animal_by_number( $motherM->MotherRegistrationNumber );

		// Fourth column.
		$fatherFFF = rough_collie_get_animal_by_number( $fatherFF->FatherRegistrationNumber );
		$fatherFFM = rough_collie_get_animal_by_number( $fatherFF->MotherRegistrationNumber );
		$fatherFMF = rough_collie_get_animal_by_number( $fatherFM->FatherRegistrationNumber );
		$fatherFMM = rough_collie_get_animal_by_number( $fatherFM->MotherRegistrationNumber );
		$fatherMFF = rough_collie_get_animal_by_number( $fatherMF->FatherRegistrationNumber );
		$fatherMFM = rough_collie_get_animal_by_number( $fatherMF->MotherRegistrationNumber );
		$fatherMMF = rough_collie_get_animal_by_number( $fatherMM->FatherRegistrationNumber );
		$fatherMMM = rough_collie_get_animal_by_number( $fatherMM->MotherRegistrationNumber );

		$motherFFF = rough_collie_get_animal_by_number( $fatherFF->FatherRegistrationNumber );
		$motherFFM = rough_collie_get_animal_by_number( $fatherFF->MotherRegistrationNumber );
		$motherFMF = rough_collie_get_animal_by_number( $fatherFM->FatherRegistrationNumber );
		$motherFMM = rough_collie_get_animal_by_number( $fatherFM->MotherRegistrationNumber );
		$motherMFF = rough_collie_get_animal_by_number( $fatherMF->FatherRegistrationNumber );
		$motherMFM = rough_collie_get_animal_by_number( $fatherMF->MotherRegistrationNumber );
		$motherMMF = rough_collie_get_animal_by_number( $fatherMM->FatherRegistrationNumber );
		$motherMMM = rough_collie_get_animal_by_number( $fatherFM->MotherRegistrationNumber );



		?><h2><?php esc_html_e('Pedigree', 'roughcollie'); ?> <?php echo esc_html( $name['name'] ); ?></h2>

		<table class="pedigree_table">
			<caption class="screen-reader-text"><?php esc_html_e('Pedigree', 'roughcollie'); ?> <?php echo esc_html( $name['name'] ); ?></caption>
			<tr class="screen-reader-text">
				<th><?php esc_html_e('First generation', 'roughcollie'); ?></th>
				<th><?php esc_html_e('Second generation', 'roughcollie'); ?></th>
				<th><?php esc_html_e('Third generation', 'roughcollie'); ?></th>
				<th><?php esc_html_e('Fourth generation', 'roughcollie'); ?></th>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $father, 'rowspan=8', 'F' ) ?>
				<?php rough_collie_animal_link_data( $fatherF, 'rowspan=4', 'FF' ) ?>
				<?php rough_collie_animal_link_data( $fatherFF, 'rowspan=2', 'FFF' ) ?>
				<?php rough_collie_animal_link_data( $fatherFFF, '', 'FFFF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherFFM, '', 'FFFM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherFM, 'rowspan=2', 'FFM' ) ?>
				<?php rough_collie_animal_link_data( $fatherFMF, '', 'FFMF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherFMM, '', 'FFMM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherM, 'rowspan=4', 'FM' ) ?>
				<?php rough_collie_animal_link_data( $fatherMF, 'rowspan=2', 'FMF' ) ?>
				<?php rough_collie_animal_link_data( $fatherMFF, '', 'FMFF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherMFM, '', 'FMFM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherMM, 'rowspan=2', 'FMM' ) ?>
				<?php rough_collie_animal_link_data( $fatherMMF, '', 'FMMF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $fatherMMM, '', 'FMMM' ) ?>
			</tr>

			<tr>
				<?php rough_collie_animal_link_data( $mother, 'rowspan=8', 'M' ) ?>
				<?php rough_collie_animal_link_data( $motherF, 'rowspan=4', 'MF' ) ?>
				<?php rough_collie_animal_link_data( $motherFF, 'rowspan=2', 'MFF' ) ?>
				<?php rough_collie_animal_link_data( $motherFFF, '', 'MFFF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherFFM, '', 'MFFM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherFM, 'rowspan=2', 'MFM' ) ?>
				<?php rough_collie_animal_link_data( $motherFMF, '', 'MFMF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherFMM, '', 'MFMM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherM, 'rowspan=4', 'MM' ) ?>
				<?php rough_collie_animal_link_data( $motherMF, 'rowspan=2', 'MMF' ) ?>
				<?php rough_collie_animal_link_data( $motherMFF, '', 'MMFF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherMFM, '', 'MMFM' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherMM, 'rowspan=2', 'FMM' ) ?>
				<?php rough_collie_animal_link_data( $motherMMF, '', 'MMMF' ) ?>
			</tr>
			<tr>
				<?php rough_collie_animal_link_data( $motherMMM, '', 'MMMM' ) ?>
			</tr>		</table>


		<?php
	}

}

function rough_collie_get_breeder_by_number( $number ) {

	global $wpdb;

	$number        = sanitize_text_field( $number);
	$breeder_data  = $wpdb->get_row( "SELECT * FROM rough_contact WHERE Number = $number" );

	return $breeder_data;

}

function rough_collie_get_animal_by_number( $number ) {

	if ( empty( $number ) || $number === "" ) {
		return false;
	}

	global $wpdb;

	$number      = sanitize_text_field( $number );
	$animal_data = $wpdb->get_row( "SELECT * FROM rough_animal WHERE RegistrationNumber = '$number'" );

	return $animal_data;
}

function rough_collie_compose_animal_name( $animal_data ) {

	if ( $animal_data ===  false ) {
		return "-";
	}

	$data  = array();
	$ch    = "";
	$class = "";

	if ( ! empty( $animal_data->TitleInFrontOfName ) ){
		$ch    = ( $animal_data->TitleInFrontOfName === "-"  ? "" : $animal_data->TitleInFrontOfName );
		$class = 'class=champion';
	}

	$data['name']  = $ch . " " . $animal_data->Name;
	$data['class'] = $class;

	return $data;

}

function rough_collie_animal_link_data( $animal_data, $rowspan, $level ) {

	if ( empty( $animal_data->Name  || $animal_data ===  false) ) {
		printf( '<td %s></td>',
			esc_attr( $rowspan )
		);
		return;
	}

	$name  = rough_collie_compose_animal_name( $animal_data );
	$url   = site_url() . '/collie/?rc_collienumber='  . $animal_data->RegistrationNumber;

	printf( '<td %s %s><a href="%s">%s</a></td>',
			esc_attr( $name['class'] ),
			esc_attr( $rowspan ),
			esc_url( $url ),
			esc_html( $name['name'] )
		);

	return;

}

function rough_collie_contact_data( $contact_id, $kennel ) {

	if ( $contact_id ===  "-" ) {
		return "-";
	}

	$contact_data = rough_collie_get_breeder_by_number( $contact_id );

	if ( empty ( $contact_data ) ) {

		return "-";

	} else {

		$contact_text = $contact_data->BusinessName;

		if ( $kennel ) {
			$url  = site_url() . '/kennel/?rc_kennelnumber=' . $contact_id;
			$contact_text = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				esc_html( $contact_data->BusinessName )
			);
		}
	}

	return $contact_text;
}
