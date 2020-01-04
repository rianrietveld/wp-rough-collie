<?php
/**
 * The function for displaying the kennel information.
 *
 * @package WordPress
 * @subpackage Rougcollie
 * @since Rougcollie 1.0
 */


function rough_collie_get_kennelvars() {
	$kennelvars = array();
	$kennelvars['rc_kennelname']    = get_query_var( 'rc_kennelname' );
	$kennelvars['rc_kennelletter']  = get_query_var( 'rc_kennelletter' );
	$kennelvars['rc_kennelcountry'] = get_query_var( 'rc_kennelcountry' );
	$kennelvars['rc_kennelnumber']  = get_query_var( 'rc_kennelnumber' );
	return $kennelvars;
}

function rough_collie_get_kennel_title( $kennelvars ) {

	$title = get_the_title();

	if ( ! empty( $kennelvars['rc_kennelname'] )  ) {
		$title = __("You searched for the kennel name: ", 'roughcollie') . $kennelvars['rc_kennelname'];
	}
	if ( ! empty( $kennelvars['rc_kennelletter'] )  ) {
		$title = __("You searched for the kennel name begin letter: ", 'roughcollie') . $kennelvars['rc_kennelletter'];
	}

	if ( ! empty( $kennelvars['rc_kennelcountry'] )  ) {
		$title = __("You searched for the kennels in: ", 'roughcollie') . $kennelvars['rc_kennelcountry'];
	}

	return $title;
}

function rough_collie_show_kennel_search_forms() {

	$kennel_data     = rough_collie_kennel_data();
	$country_options = array();
	$letter_options  = array();


	// Options for countries and letters selects.
	foreach( $kennel_data as $key => $value ) {

		 if ( strlen( $value->Country ) > 1 ) {
				$country_options[ $value->Country ] = $value->Country;
		 }
		 if ( strlen ( $value->BusinessName ) > 1 ) {
			    $letter                    = html_entity_decode( $value->BusinessName );
				$letter                    = mb_substr ( $letter, 0, 1 );
			    $letter_options[ $letter ] = $letter;
		 }

	}

	uasort($letter_options, 'rough_collie_compareASCII');
	asort( $country_options );

	?>

	<form action="/kennel/" method="post" class="rc-form">
		<div>
			<label for="rc_kennelname"><?php esc_html_e('Search a kennel or breeder by name', 'roughcollie'); ?></label><br />
			<input type="text" name="rc_kennelname" id="rc_kennelname"><br />
			<input type="submit" name="submit" value="<?php esc_html_e('Search by name', 'roughcollie'); ?>" /><br />
		</div>
	</form>

	<form action="/kennel/" method="post" class="rc-form">
		<div>
			<label for="rc_kennelletter"><?php esc_html_e('Search a kennel or breeder by begin letter', 'roughcollie'); ?></label><br />
			<select name="rc_kennelletter" id="rc_kennelletter">
				<?php

				foreach( $letter_options as $key => $value ) {
					printf( '<option value="%s">%s</option>',
						esc_attr( $key ),
						esc_html( $value )
					);
				}
				?>
			</select><br />
			<input type="submit" name="submit" value="<?php esc_html_e('Search by begin letter', 'roughcollie'); ?>" /><br />
		</div>
	</form>

	<form action="/kennel/" method="post" class="rc-form">
		<div>
			<label for="rc_kennelcountry"><?php esc_html_e('Search a kennel or breeder by country', 'roughcollie'); ?></label><br />
			<select name="rc_kennelcountry" id="rc_kennelcountry">
				<?php

				foreach( $country_options as $key => $value ) {
					printf( '<option value="%s">%s</option>',
						esc_attr( $key ),
						esc_html( rough_collie_first_uppercase( $value ) )
					);
				}
				?>
			</select><br />
			<input type="submit" name="submit" value="<?php esc_html_e('Search by country', 'roughcollie'); ?>" /><br />
		</div>
	</form>

	<?php

}

function rough_collie_kennel_data() {

	global $wpdb;

	$kennel_data = $wpdb->get_results( "SELECT BusinessName, Country FROM rough_contact" );

	return $kennel_data;

}

function rough_collie_show_kennels( $kennelvars ) {

	$kennel_data = array();

	if ( ! empty( $kennelvars['rc_kennelname'] ) ) {
		$kennel_data = rough_collie_get_kennel_by_name( $kennelvars['rc_kennelname'] );
	} elseif( ! empty( $kennelvars['rc_kennelletter'] ) ) {
		$kennel_data = rough_collie_get_kennel_by_letter( $kennelvars['rc_kennelletter'] );
	} elseif( ! empty( $kennelvars['rc_kennelcountry'] ) ) {
		$kennel_data = rough_collie_get_kennel_by_country( $kennelvars['rc_kennelcountry'] );
	}

	if ( empty ( $kennel_data ) ) {
		esc_html_e('No kennels found.', 'roughcollie');
		return;
	}

	if ( count( $kennel_data ) === 1 ) {
		rough_collie_show_single_kennel_data( 0, $kennel_data  );
	} else {
		rough_collie_show_kennels_data( $kennel_data );
	}



}


function rough_collie_get_kennel_by_name( $name ) {

	global $wpdb;

	$name      = sanitize_text_field( $name );
	$kennel_data = $wpdb->get_results( "SELECT * FROM rough_contact WHERE BusinessName = '$name'" );

	return $kennel_data;

}

function rough_collie_get_kennel_by_letter( $letter ) {

	global $wpdb;

	$data = $wpdb->get_results( "SELECT * FROM rough_contact" );

	foreach ( $data as $key => $value ) {

		$first = html_entity_decode( $value->BusinessName );
		$first = mb_substr( $first, 0, 1 );

		if ( $first === $letter ) {
			$kennel_data[ $key ] = $value;
		}
	}

	return $kennel_data;

}

function rough_collie_get_kennel_by_country( $country ) {

	global $wpdb;

	$country      = sanitize_text_field( $country );
	$kennel_data = $wpdb->get_results( "SELECT * FROM rough_contact WHERE Country = '$country'" );

	return $kennel_data;

}

function rough_collie_show_kennels_data( $kennel_data ) {

	//todo: nog sorteren op kennelnaam
	?><ul><?php
	foreach ( $kennel_data as $key => $value ) {

		$country = rough_collie_first_uppercase( $value->Country );

		printf( '<li><a href="%s">%s</a>, %s</li>',
		esc_url('/kennel/?rc_kennelnumber=' . $value->Number ),
		esc_html( $value->BusinessName ),
		esc_html( $country ) );
	};
	?></ul><?php
}

/**
 * @param int $number
 * @param string $data
 */
function rough_collie_show_single_kennel_data( $number , $kennel_data=array() ) {

	global $wpdb;

	if ( $number ) {
		$number      = sanitize_text_field( $number );
		$kennel_data = $wpdb->get_row( "SELECT * FROM rough_contact WHERE Number = $number" );
		$name        = $kennel_data->BusinessName;
		$country     = rough_collie_first_uppercase( $kennel_data->Country );
		$url         = $kennel_data->Homepage;
	} else {
		$name       = $kennel_data[0]->BusinessName;
		$country    = rough_collie_first_uppercase( $kennel_data[0]->Country );
		$url        = $kennel_data[0]->Homepage;
	}

	?>

	<dl>
		<dt><?php esc_html_e('Name', 'roughcollie'); ?></dt><dd><?php echo esc_html( $name ); ?></dd>
		<dt><?php esc_html_e('Country', 'roughcollie'); ?></dt><dd><?php echo esc_html( $country ); ?></dd>

		<dt>Website</dt>
		<?php

		if ( $url === "http://www." ||  $url === "" ) {
			?><dd>- </dd><?php
		} else {
			// Cleanup url
			$input = trim( $url, '/' );
			if ( !preg_match('#^http(s)?://#', $input) ) {
				$input = 'http://' . $input;
			}
			$urlParts = parse_url( $input );
			$domain   = preg_replace('/^www\./', '', $urlParts['host']);
			printf( '<dd><a href="%s">%s</a></dd>',
				esc_url( $url ),
				esc_html( $domain )
			);
		}
		?>
	</dl>

	<?php
}

function rough_collie_compareASCII( $a, $b ) {
	$at = iconv( 'UTF-8', 'ASCII//TRANSLIT', $a );
	$bt = iconv( 'UTF-8', 'ASCII//TRANSLIT', $b );
	return strcmp( $at, $bt );
}
