<?php
/*
* Plugin Name: CM CD Plugin
* Plugin URI: [one]
* Description: A new plugin to manage Cds
* Wordpress
* Version: 1.0.0
* Author: Christian Marquardt
* Author URI: [none]
* License: GPL2
*/


// global $wpdb;

add_filter( 'the_content', 'table_after_content' );

function after_content( $content ) {


	// $table_name = $wpdb->prefix . 'cm_cd_table';

	$titleArray = get_post_custom_values('song', get_the_ID());
	if (is_singular('cd_album') && !empty($titleArray)) {
		$content .= '';
		$content .= '<p><h4>Titel</h4>';
		$content .= '<ol>';
		foreach((array)$titleArray as $key => $value) {
			$content .= '<li>' . $value . '</li>';
		}
		$content .= '</ol></p>';
	}
	return $content;
}

function table_after_content( $content ) {

	$titleArray = get_post_custom_values('song', get_the_ID());
	if (is_singular('cd_album') && !empty($titleArray)) {
		$content .= '';
		$content .= '<p><h4>Musik-Liste</h4>';
		$content .= '<table class="table table-hover table-striped">';
		$content .= '<tr><th>Nr.</th><th>Titel</th></tr>';
		foreach((array)$titleArray as $key => $value) {
			$content .= '<tr><td>' .($key+1). '</td><td>' . $value . '</td></tr>';
		}
		$content .= '</table></p>';
	}
	return $content;

}


function cm_cd_createCd() {

    register_post_type( 'cd_album',
	array(
		// reset some labels
            'labels' => array(
                'name' => 'Cds',
                'singular_name' => 'Cd',
                'add_new' => 'Add Cd',
                'add_new_item' => 'Add New Cd',
                'edit' => 'Edit',
                'edit_item' => 'Edit Cd',
                'new_item' => 'New Cd',
                'view' => 'View',
                'view_item' => 'View Cd',
                'search_items' => 'Search Cd',
                'not_found' => 'No Cds found',
                'not_found_in_trash' => 'No Cds found in Trash',
                'parent' => 'Parent Cd'
            ),

	    // accessible for the user
	    'public' => true,
	    // position in menu
	    'menu_position' => 100,
	    // containing data 
	    'supports' => array( 'title', 'thumbnail','custom-fields' ),
	    // register current type for special categories
            'taxonomies' => array( '' ),
            // create an own archive page
            'has_archive' => true
        )
	);
	register_taxonomy('artists',array('cd_album'),
		array(
			// non-hirarchical taxonomy
			'hierarchical' => false,
			// shows the artists as a tag cloud
			'show_tagcloud' => true,
			// label
			'label' => __('Künstler'),
			// html subdirectory
			'rewrite' => array('slug' => 'album/artist'),
			// reset some labels
			'labels' => array(
				'name' => 'Künstler',
				'singular_name' => 'Künstler',
				'edit_item' => 'Künstler bearbeiten',
				'update_item' => 'Künstler aktualisieren',
				'add_new_item' => 'Neuer Künstler',
				'new_item_name' => 'Neuer Künstlername',
				'all_items' => 'Alle Künstler',
				'search_items' => 'Künstler suchen',
				'popular_items' => 'Berühmte Künstler',
				'choose_from_most_used' => 'Meist gebrauchte Künstler',
				'separate_items_with_commas' => 'Künstler durch Kommata trennen'
			)
		));
}

// create posttype and taxonomy during initialization
add_action( 'init', 'cm_cd_createCd' );

// cycles through all cds (registered as a custom posttype)
function cm_cd_type_loop($attr, $content) {
	// reset all data
	wp_reset_postdata();
	// create a "loop"-variable for the specific post type; here: cd_album
	$loop = new WP_Query(
		array(
			'post_type' => 'cd_album',
			// 'orderby' => 'title',
			// 'order' => 'ASC',
			// 'posts_per_page' => -1
		)
	);
	// log the data with html tags to the output-variable
	$output='<br><br>Eingetragene Werke:<br>';
	// are there any posts (of type cd_album) ?
	if( $loop->have_posts() ) {
		$output .= '<table class="table table-hover table-striped" bgcolor="blue">';
		$output .= '<tr><th>Titel</th><th>K&uuml;nstler</th><th>Songs</th></tr>';

		// as long as we have new entries (posts)
		while ( $loop->have_posts() ){
			// load next values into all global variables
			$loop->the_post();
			$output .= '<tr>';
			// output the title as link
			$output .= the_title('<td><a href="' . get_permalink() . '">','</a></td>',false);
			$output .= '<td>';
			//
			$output .= get_the_term_list(get_the_ID(), 'artists','',',','');
			// return all meta data
			// $output .= '</td><td>' . the_meta();
			// save the metadata into a two dimensional array
			$custom_fields = get_post_custom(get_the_ID());
			// cycle through the metadata by $key and $value.
			foreach ( $custom_fields as $thekeys => $thevalues ) {
				foreach ($thevalues as $key => $value) {
    					$output .= $thekeys .": " . $key . " => " . $value . "<br />";
				}
			}
			$output .= '</td><td>';
			$mykey_values = get_post_custom_values('song');
			foreach ((array)$mykey_values as $key => $value) {
				$output .= $key . ':' . $value .', ';
			}
			//foreach ( $mykey_values as $key2 => $value2 ) {
			//	$output .= $key2 .':'.$value2 .', ';
			//}
			$output .= '</td></tr>';
		}

		$output .= '</table>';
	}
	else {
		// case: no cd_album found
		$output .= '<p>Nix gefunden...</p>';
	}
	
	// reset all data
	wp_reset_postdata();
	return $output;

}

// add a shortcode for the loop (cycling through the custom posttype)
add_shortcode('allAlbum', 'cm_cd_type_loop');




// tests for a custom metabox
function my_custom_box_create() {
	add_meta_box('mc_box', 'Cd Titel', 'my_custom_box_function', 'cd_album','normal','high');
}

// function that contains the html ocde for the metabox
function my_custom_box_function($cd) {
	
	// $myArray = get_post_meta($cd->ID, 'song', false);
	// read all songs from meta data
	$titleArray = get_post_custom_values('song', $cd->ID);
	$linkArray = get_post_custom_values('link', $cd->ID);
	// $linkArray[0] = 'Hello';
	// cycle through the array (value contains the song)
	foreach((array)$titleArray as $key => $value) {
		
		//  output a checkbox and text input field for each song-->
		echo '<p><input type="checkbox" name="haken'.$key.'" checked />' . ($key+1) .'. Titel: <input tyüe="text" name="eingabe'. $key.'" value="'.$value.'"/>';
		echo 'Link: <input type="text" name="link'.$key.'" size="70" value="'.$linkArray[$key].'"</p>';
	}
	// output a text input to add a new song
	echo '<br>Neuen Titel eingeben';
	echo '<p>Titel:<input type="text" name="neu" value="" /> Link:<input type="text" name="neulink" value="" size="70"></p>';
}
// add the custom metabox
add_action( 'add_meta_boxes' , 'my_custom_box_create' );


// saving function when "Aktualisieren" is pressed
function cm_cd_save_meta($cd_id) {
	// check if metadata already exists
	$number = get_post_meta($cd_id,'number',true);
	if($number == '') // = not yet set?
		$number = 1;


	// read old songs from meta data
	$oldArray = get_post_custom_values('song', $cd_id);
	$linkArray = get_post_custom_values('link', $cd_id);
	//$oldLength = count($oldArray);


	// cycle trhough all entries and update their contents by the specific input field
	foreach((array)$oldArray as $key => $value) {
		// $key = number, $value = song
		update_post_meta($cd_id,'song', $_POST[('eingabe'.$key)],$value);
		update_post_meta($cd_id,'link', $_POST[('link'.$key)],$linkArray[$key]);
		// if there is no checked checkbox delete the data
		if(! isset($_POST[('haken'.$key)])) {
			delete_post_meta($cd_id, 'song', $value);
			delete_post_meta($cd_id, 'link', $linkArray[$key]);
		}
	}
	
	// read new input	
	if( (isset($_POST['neu'])) || (isset($_POST['neulink'])) ) {
		$newInput = strip_tags($_POST['neu']);
		$newLink = strip_tags($_POST['neulink']);
		// add new song if the input is not empty
		if( $newInput != '') {
			// prohibit empty link data
			$number++;
			if($newLink == '') $newLink='empty'.$number;
			add_post_meta($cd_id, 'song', $newInput, false);
			add_post_meta($cd_id, 'link', $newLink, false);
			// write new number back
			update_post_meta($cd_id, 'number',$number);
		}
	}
}

// checks an url to be an mp3 file, ie. start with "http://" or "https://" and ends with ".mp3"
function checkUrlForMp3( $string ) {
	$string = trim($string);
	// strstr returns the rest of the string, starting at the first occurence of '.mp3'
	// strstr($string,'.mp3') == '.mp3' or '.MP3'
	// strpos(string,suchstring,beginn(optional)) searches for the first occurence of [suchstring] in [string] starting at [beginn]
	// stripos ""					does the same but ignores case
	// stripos($string, 'http') == 0 nicht false (= nichts gefunden) (j)
	// stripos($string, 'https') == 0 nicht false (= nichts gefunden) (j)
	// strpos($string, '://') == 4 oder 5 (index begint bei 0) nicht false (= nichts gefunden)
	$start = stripos($string, 'http');
	$sec_start= stripos($string, 'https');
	if($start === false && $sec_start === false) return false; // both do not appear
	if($start == 0 || $sec_start == 0) { // starts with one of both
		// check for "://" at position 4 or 5
		$sub_slash = strpos($string, '://');
		if($sub_slash === false) return false; // not found
		$cor_slash = ($sub_slash == 4) || ($sub_slash == 5);
		// check ending
		$end = strstr($sring,'.') == '.mp3';
		$big_end = strstr($string,'.') == '.MP3';
		// combine for true
		if ($cor_slash && ($end || $big_end)) return true;
		// otherwise program flow hits return false at the end
	}
	// otherwise no correct mp3-adress
	return false;
}

add_action('save_post', 'cm_cd_save_meta');

?>
