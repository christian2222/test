<?php
/*
* Plugin Name: CM CD Plugin
* Plugin URI: [none]
* Description: A new plugin to manage Cds
* Wordpress
* Version: 1.0.0
* Author: Christian Marquardt
* Author URI: [none]
* License: GPLv2
*/



// global separator
$separator = '-----';

/**
 * DATA TYPE CONSTRUCTIONS
 */

/* Create a custom posttype and a custom taxonomy
 *
 * custom posttype = cd_album
 * custom taxonomy = artists
 */
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





/**
 * DISPLAY CONTENT
 */

/*
 * Cycle trough all cd_album posts (registered as custom posttype)
 */
function cm_cd_type_loop($attr, $content) {
	// reset all data
	wp_reset_postdata();
	// create a "loop"-variable for the specific post type; here: cd_album
	$loop = new WP_Query(
		array(
			'post_type' => 'cd_album',
			// also possible:
			// 'orderby' => 'title',
			// 'order' => 'ASC',
			// 'posts_per_page' => -1
		)
	);
	// log the data with html tags to the output-variable
	$output='<br><br>Eingetragene Werke:<br>';
	// are there any posts (of type cd_album) ?
	if( $loop->have_posts() ) {
		// create table header
		$output .= '<table class="table table-hover table-striped" bgcolor="blue">';
		$output .= '<tr><th>Cd</th><th>K&uuml;nstler</th><th>Titel</th></tr>';

		// as long as we have new entries (posts)
		while ( $loop->have_posts() ){
			// load next values into all global variables
			$loop->the_post();
			// start a new row in the table
			$output .= '<tr>';
			// output the title as link
			$output .= the_title('<td><a href="' . get_permalink() . '">','</a></td>',false);
			$output .= '<td>';
			// add artists as links
			$output .= get_the_term_list(get_the_ID(), 'artists','',',','');
			$output .= '</td><td>';
			// insert all titles of current cd_album (custom post type)
			// Note: titles are stored inside the metadata of the custom posttype cd_album
			$mykey_values = get_post_custom_values('multi');
			// metakey [multi] contains 0..N-1 (key, value) pairs
			// where value has the coding: [title]-----[mp3link]
			// and ----- is the global varible $separator
			foreach ((array)$mykey_values as $key => $value) {
				$output .= ($key+1) . ')' . cm_cd_chop_title($value) .', ';
			}
			// end row
			$output .= '</td></tr>';
		}
		// create table footer
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



/*
 * Draw table after content containing
 * number title and 
 * an html5-player, iff the mp3-adress is valid
 */
function cm_cd_title_table_after_content( $content ) {
	// catch metadata
	$multiArray = get_post_custom_values('multi', get_the_ID());
	if (is_singular('cd_album') && !empty($multiArray)) {
		// start table content paragraph
		$content .= '<p><h4>Musik-Liste</h4>';
		// table header
		$content .= '<table class="table table-hover table-striped">';
		$content .= '<tr><th>Nr.</th><th>Titel</th><th>Link</th></tr>';
		// extract the (key,value)pairs, see foreach loop before
		foreach((array)$multiArray as $key => $value) {
			// check existence of separator
			if(cm_cd_has_separator($value)) {
				// start a new row containing nr|title|link
				$content .= '<tr><td>' .($key+1). '</td><td>' . cm_cd_chop_title($value) . '</td>';
				$chopped_link = cm_cd_mp3_to_player(cm_cd_chop_link($value));
				// if the link-adress is http(s), then diplay html5 player with mp3-file
				if(cm_cd_check_url_for_mp3(cm_cd_chop_link($value)))
					$content .= '<td style="width:250px">'. $chopped_link .'</td></tr>'; 
				else
					$content .= '<td></td></tr>';
			}
		}
		// table and paragraph footer
		$content .= '</table></p>';
	}
	return $content;

}

add_filter( 'the_content', 'cm_cd_title_table_after_content' );


/*
 * a custom metabox containing
 * 1) all existing titles and links
 * 2) a possiblity to add an new entry (title and link)
 */
function cm_cd_create_cd_metabox() {
	add_meta_box('mc_box', 'Cd Titel', 'cm_cd_metabox_content', 'cd_album','normal','high');
}

/* Function that contains the html code for the above metabox
 *
 */
function cm_cd_metabox_content($cd) {
	// use global separator
	global $separator;
	// catch multi-metadata
	$multiArray = get_post_custom_values('multi', $cd->ID);

	// require jQuery
	wp_enqueue_script('jquery');
	// require the Media Uploader script
	wp_enqueue_media();

	// cycle through the array (value contains the multi-entry)
	// Remind multi has the format: [title]$separator[mp3link]
	foreach((array)$multiArray as $key => $value) {
		$multi_str = $value;
		$title_str='';
		$mp3_str='';
		// check for separator
		if(cm_cd_has_separator($multi_str)) {
			// cut out title and mp3link see DOCUMENTATION
			$offset = stripos($multi_str,$separator);
			$title_str = substr($multi_str, 0, $offset);
			$mp3_str = substr($multi_str,($offset+strlen($separator)));
			//  output a checkbox and text input field for each (title,link) pair
			cm_cd_insert_cd_number_entry($key,$title_str,$mp3_str);
		}
		
	}
	// output a text input to add a new (title,link) pair
	echo '<br>Neuen Titel eingeben';
	echo '<p>Titel:<input type="text" name="neu" id="dazutitle" value="" /><br> Link:<input type="text" name="neulink" id="mp3neu" value="" size="50">';
	echo '<input type="button" class="button-secondary" id="mp3btndazu" name="buttonDazu" value="Aus Mediathek..." /></p>';
	cm_cd_insert_cd_javascript('mp3btndazu','mp3neu');
	echo '<br>';
}


/**
 * FUNCTIONAL CONTENT
 */


/* saving function when "Aktualisieren" is pressed
 * unite song and link in one string separated by global variable $separator
*/
function cm_cd_save_meta($cd_id) {
	global $separator;
	// check if metadata [number] already exists
	$number = get_post_meta($cd_id,'number',true); // only one value - no array: hence true
	if($number == '') // = not yet set? -> set to 1
		$number = 1;
	
	// read old songs from meta data [multi]
	$oldArray = get_post_custom_values('multi', $cd_id);
	// coded format for oldArray:
	// [song title]$separator[mp4 adress]

	// cycle trhough all entries and update their contents by the specific input fields
	foreach((array)$oldArray as $key => $value) {
		// $key = number, $value = multi
		
		// construct multi string out of input fields 
		$multi = $_POST[('eingabe'.$key)] . $separator . $_POST[('link'.$key)]; // use POST instead of GET, so the user does not see the values in the http-adress
		// update the input ($vaulue is out of the $oldArray)
		update_post_meta($cd_id,'multi', $multi, $value);
		// if there is no checked checkbox delete the data
		if(! isset($_POST[('haken'.$key)])) {
			// delete the multi meta data
			delete_post_meta($cd_id, 'multi', $multi);
		}
	}
	
	// read new input, if at least there is a title of a link
	if( (isset($_POST['neu'])) || (isset($_POST['neulink'])) ) {
		$newInput = strip_tags($_POST['neu']);
		$newLink = strip_tags($_POST['neulink']);
		// add new multi if the title input is not empty
		if( $newInput != '') {
			// prohibit empty link data
			$number++;
			if($newLink == '') $newLink='empty'.$number;
			// write new number back (since it was used)
			update_post_meta($cd_id,'number',$number);
			// construct new multi string
			$new_multi = $newInput . $separator . $newLink;
			// add it as post metadata
			add_post_meta($cd_id, 'multi',$new_multi);
		}
	}
}

// bind saving function on save_post action
add_action('save_post', 'cm_cd_save_meta');


/*
 *  output cd entry form with checkbox
 */
function cm_cd_insert_cd_number_entry($number, $title_string, $mp3_string) {

	echo '<p><input type="checkbox" name="haken'.$number.'" checked />' . ($number+1) .'. Titel: <input type="text" name="eingabe'. $number.'" value="'.$title_string.'" class="regular-text" /><br>';
	echo 'MP3-Link: <input type="text" id="mp3link'.$number.'" name="link'.$number.'" size="50" value="'.$mp3_string . '" class="regular-text" /><input type="button" class="button-secondary" id="mp3btn'.$number.'" name="button'.$number.'" value="Aus Mediathek..." /></p>';
	cm_cd_insert_cd_javascript('mp3btn'.$number,'mp3link'.$number);
}

/* 
 * output necessary javascript lines
 */
function cm_cd_insert_cd_javascript($button,$link) {
	echo '<script type="text/javascript">';
	echo "jQuery(document).ready(function($){";
	echo "	$('#".$button."').click(function(e) {";
	echo "		e.preventDefault();";
	echo "		var file = wp.media({ title: 'MP3-Datei aus Mediathek wählen', multiple: false }).open().on('select', function(e){";
	echo "			var uploadfile = file.state().get('selection').first();";
	echo "			console.log(uploadfile);";
	echo "			var fileurl = uploadfile.toJSON().url;";
	echo "			$('#".$link."').val(fileurl);";
	echo "		});"; //wp.media
	echo "	});"; //$(mp3btn)
	echo "});"; //jQuery
	echo "";
	echo '</script>';
}




// add the custom metabox
add_action( 'add_meta_boxes' , 'cm_cd_create_cd_metabox' );


/**
 * STRING PROCESSING FUNCTIONS
 * Mainly cutting functions for mp3link, title and existence of separator; see DOCUMENTATION
 */


/* 
 * cut the title out of multi string
 */
function cm_cd_chop_title($multi) {
	global $separator;
	$offset = strpos($multi, $separator);
	$title_str = substr($multi, 0, $offset);
	return $title_str;
}

/*
 *  check for separator - return boolean
 */
function cm_cd_has_separator($multi) {
	global $separator;
	$offset = strpos($multi, $separator);
	return ($offset !== false); // remind 0 == false but not 0 === false
	// even if there is no title, we find the seperator at position 0 (!== false)!
	// if we use != this won't work, because 0 == false (see php documentation)
}

/* 
 * cut the link out of multi string
 */
function cm_cd_chop_link($multi) {
	global $separator;
	$offset = strpos($multi, $separator);
	$mp3_str = substr($multi,($offset+strlen($separator)));
	return $mp3_str;
}

/*
 *  embed link by audio player tags (due to html5 convention)
 */
function cm_cd_mp3_to_player($mp3string) {
	$string = '<audio controls style="width:100px; display:block;"> <source src="'. $mp3string .'" type="audio/mpeg">No support for the audio(mp3) element.</audio> ';
	return $string;
}



/*
 * Checks an url to be an mp3 file,
 * ie. start with "http://" or "https://" and ends with ".mp3"
 */
function cm_cd_check_url_for_mp3( $string ) {
	// see: DOCUMENTATION
	// delete leading and ending whitespaces (trim)
	$string = trim($string);
	// ask for thhp(s) start
	$start = stripos($string, 'http');
	$sec_start= stripos($string, 'https');
	if($start === false && $sec_start === false) return false; // both do not appear
	if($start == 0 || $sec_start == 0) { // starts with one of both
		// check for "://" at position 4 or 5
		$sub_slash = strpos($string, '://');
		// if($sub_slash === false) return false; // not found; ending in return false at the end of the function
		$cor_slash = ($sub_slash == 4) || ($sub_slash == 5);
		// debugging: add_post_meta(get_the_ID(), 'error',$sub_slash);
		// check ending
		$end = substr($string,-4) == '.mp3';
		// cutting the last 4 charaters of the given string
		$big_end = substr($string,-4) == '.MP3';
		// debugging: add_post_meta(get_the_ID(),'error',substr($string,-4));
		// combine for true
		if ($cor_slash && ($end || $big_end)) return true;
		// otherwise program flow hits return false at the end
	}
	// otherwise no correct mp3-adress
	return false;
}

// DOCUMENTATION
// *************
// strstr returns the rest of the string, starting at the first occurence of '.mp3'
// strstr($string,'.mp3') == '.mp3' or '.MP3'
// strpos(string,suchstring,begin(optional)) searches for the first occurence of [suchstring] in [string] starting at [begin]
// stripos ""					does the same but ignores case
// stripos($string, 'http') == 0 not false (= nothing found) (j)
// stripos($string, 'https') == 0 not false (= nothing found) (j)
// strpos($string, '://') == 4 oder 5 (index begint bei 0) nicht false (= nothing found)


/**
 * UNCERTAIN CONTENT
 */

// for upload selection
function cm_cd_manage_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery');
}	

function cm_cd_manage_admin_styles() {
	wp_enqueue_style('thickbox');
}

add_action('admin_print_scripts', 'cm_cd_manage_admin_scripts');
add_action('admin_print_styles', 'cm_cd_manage_admin_styles');


/**
 * OUTCUTTED CODE
 */

//function cm_cd_after_content( $content ) {


	// $table_name = $wpdb->prefix . 'cm_cd_table';

//	$titleArray = get_post_custom_values('song', get_the_ID());
//	if (is_singular('cd_album') && !empty($titleArray)) {
//		$content .= '';
//		$content .= '<p><h4>Titel</h4>';
//		$content .= '<ol>';
//		foreach((array)$titleArray as $key => $value) {
//			$content .= '<li>' . $value . '</li>';
//		}
//		$content .= '</ol></p>';
//	}
//	return $content;
//}


// return all meta data
// $output .= '</td><td>' . the_meta();
// save the metadata into a two dimensional array
// $custom_fields = get_post_custom(get_the_ID());
// cycle through the metadata by $key and $value.
// foreach ( $custom_fields as $thekeys => $thevalues ) {
//	foreach ($thevalues as $key => $value) {
//		$output .= $thekeys .": " . $key . " => " . $value . "<br />";
//	}
// }


?>
