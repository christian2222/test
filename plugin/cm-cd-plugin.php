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



// global $wpdb;
$separator = 'XXX';
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


function chop_title($multi) {
	global $separator;
	$offset = stripos($multi, $separator);
	$title_str = substr($multi, 0, $offset);
	return $title_str;
}

function hasSeparator($multi) {
	global $separator;
	$offset = stripos($multi, $separator);
	return ($offset != false);
}

function chop_link($multi) {
	global $separator;
	$offset = stripos($multi, $separator);
	$mp3_str = substr($multi,($offset+strlen($separator)));
	return $mp3_str;
}

function mp3_to_player($mp3string) {
	$string = '<audio controls style="width:100px; display:block;"> <source src="'. $mp3string .'" type="audio/mpeg">No support for the audio(mp3) element.</audio> ';
	return $string;
}

function table_after_content( $content ) {

	$titleArray = get_post_custom_values('multi', get_the_ID());
	if (is_singular('cd_album') && !empty($titleArray)) {
		$content .= '';
		$content .= '<p><h4>Musik-Liste</h4>';
		$content .= '<table class="table table-hover table-striped">';
		$content .= '<tr><th>Nr.</th><th>Titel</th><th>Link</th></tr>';
		foreach((array)$titleArray as $key => $value) {
			if(hasSeparator($value)) {
				$content .= '<tr><td>' .($key+1). '</td><td>' . chop_title($value) . '</td>';
				$content .= '<td style="width:250px">'. mp3_to_player(chop_link($value)) .'</td></tr>'; 
			}
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



function insert_cd_number_entry($number, $title_string, $mp3_string) {

	echo '<p><input type="checkbox" name="haken'.$number.'" checked />' . ($number+1) .'. Titel: <input type="text" name="eingabe'. $number.'" value="'.$title_string.'" class="regular-text" /><br>';
	echo 'MP3-Link: <input type="text" id="mp3link'.$number.'" name="link'.$number.'" size="50" value="'.$mp3_string . '" class="regular-text" /><input type="button" class="button-secondary" id="mp3btn'.$number.'" name="button'.$number.'" value="Aus Mediathek..." /></p>';
	insert_cd_javascript('mp3btn'.$number,'mp3link'.$number);
}

function insert_cd_javascript($button,$link) {
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


// function that contains the html ocde for the metabox
function my_custom_box_function($cd) {
	global $separator;
	// $myArray = get_post_meta($cd->ID, 'song', false);
	// read all songs from meta data
	// $titleArray = get_post_custom_values('song', $cd->ID);
	// $linkArray = get_post_custom_values('link', $cd->ID);
	$multiArray = get_post_custom_values('multi', $cd->ID);
	// $linkArray[0] = 'Hello';
	// cycle through the array (value contains the song)
	// strstr returns the rest of the string, starting at the first occurence of '.mp3'
	// strstr($string,'.mp3') == '.mp3' or '.MP3'
	// strpos(string,suchstring,begin(optional)) searches for the first occurence of [suchstring] in [string] starting at [begin]
	// stripos ""					does the same but ignores case
	// stripos($string, 'http') == 0 not false (= nothing found) (j)
	// stripos($string, 'https') == 0 not false (= nothing found) (j)
	// strpos($string, '://') == 4 oder 5 (index begint bei 0) nicht false (= nothing found)
	//
	// enter javascript lines
	// jQuery
	wp_enqueue_script('jquery');
	// This will enqueue the Media Uploader script
	wp_enqueue_media();

	foreach((array)$multiArray as $key => $value) {
		$multi_str = $value;
		$title_str='';
	       	$mp3_str='';
		if(stripos($multi_str,$separator) != false) {
			$offset = stripos($multi_str,$separator);
			$title_str = substr($multi_str, 0, $offset);
			$mp3_str = substr($multi_str,($offset+strlen($separator)));

		}
		//  output a checkbox and text input field for each song-->
		insert_cd_number_entry($key,$title_str,$mp3_str);
		
	}
	// output a text input to add a new song
	echo '<br>Neuen Titel eingeben';
	echo '<p>Titel:<input type="text" name="neu" id="dazutitle" value="" /><br> Link:<input type="text" name="neulink" id="mp3neu" value="" size="50"><input type="button" class="button-secondary" id="mp3btndazu" name="buttonDazu" value="Aus Mediathek..." /></p>';
	insert_cd_javascript('mp3btndazu','mp3neu');
	echo '<br>';
}

// add the custom metabox
add_action( 'add_meta_boxes' , 'my_custom_box_create' );


// saving function when "Aktualisieren" is pressed
// unite song and link in one string separated by global variable separator
function cm_cd_save_meta($cd_id) {
	global $separator;
	// check if metadata already exists
	$number = get_post_meta($cd_id,'number',true);
	if($number == '') // = not yet set?
		$number = 1;


	// read old songs from meta data
	$oldArray = get_post_custom_values('multi', $cd_id);
	// $linkArray = get_post_custom_values('link', $cd_id);
	// $allaRRAY = get_post_custom_values('multi',$cd_id);
	// coded format for allArray:
	// [song title]separator[mp4 adress]
	//$oldLength = count($oldArray);


	// cycle trhough all entries and update their contents by the specific input field
	foreach((array)$oldArray as $key => $value) {
		// $key = number, $value = song
		$multi = $_POST[('eingabe'.$key)] . $separator . $_POST[('link'.$key)];
		// update_post_meta($cd_id,'song', $_POST[('eingabe'.$key)],$value);
		// update_post_meta($cd_id,'link', $_POST[('link'.$key)],$linkArray[$key]);
		update_post_meta($cd_id,'multi', $multi, $value);
		// if there is no checked checkbox delete the data
		if(! isset($_POST[('haken'.$key)])) {
			// delete_post_meta($cd_id, 'song', $value);
			// delete_post_meta($cd_id, 'link', $linkArray[$key]);
			// delete the multi meta data
			// generate multi string out of the input fields
			// $del_multi = $_POST[('eingabe'.$key)] . $separator . $_POST[('link'.$key)];
			delete_post_meta($cd_id, 'multi', $multi);
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
			// add_post_meta($cd_id, 'song', $newInput, false);
			// add_post_meta($cd_id, 'link', $newLink, false);
			// write new number back
			$new_multi = $newInput . $separator . $newLink;
			add_post_meta($cd_id, 'multi',$new_multi);
		}
	}
}

// checks an url to be an mp3 file, ie. start with "http://" or "https://" and ends with ".mp3"
function checkUrlForMp3( $string ) {
	$string = trim($string);
	// strstr returns the rest of the string, starting at the first occurence of '.mp3'
	// strstr($string,'.mp3') == '.mp3' or '.MP3'
	// strpos(string,suchstring,begin(optional)) searches for the first occurence of [suchstring] in [string] starting at [begin]
	// stripos ""					does the same but ignores case
	// stripos($string, 'http') == 0 not false (= nothing found) (j)
	// stripos($string, 'https') == 0 not false (= nothing found) (j)
	// strpos($string, '://') == 4 oder 5 (index begint bei 0) nicht false (= nothing found)
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
