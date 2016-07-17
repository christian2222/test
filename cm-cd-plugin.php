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

add_filter( 'the_content', 'after_content' );

function after_content( $content ) {


	// $table_name = $wpdb->prefix . 'cm_cd_table';

	if (is_singular('cd_album')) {
		$content .= '';
		$content .= '<p><h4>Titel</h4>';
		$titleArray = get_post_custom_values('song', get_the_ID());
		$content .= '<ol>';
		foreach((array)$titleArray as $key => $value) {
			$content .= '<li>' . $value . '</li>';
		}
		$content .= '</ol></p>';
	}
	return $content;
}

// prints all called hooks
// add_action( 'all', 'print_current_hook' );

// function print_current_hook() {
//     echo '<p>' . current_filter() . '</p>';
// }
//
//
//
// add_shortcode('liste', 'cd_list');
//
// function cd_list($attr, $content) {
// 	return '<table class="table table-hover">' . do_shortcode($content) . '</table>';
// }
//

//
// add_shortcode('werk','cd_artist');
//
//
//
// function cd_artist($attr, $content) {
// 	$string = '<tr><td>';
// 	$string .= $attr['name'];
// 	$string .= '</td><td>';
// 	$string .= $attr['titel'];
// 	$string .= "</td>";
// 	$string .= "</tr>";
// 	return $string;
// }
//


// installation of databases
// function cm_cd_install() {
	// global $wpdb;

	// $table_name = $wpdb->prefix . 'cm_cd_table';

	// $table_name = 'wp_cm_cd_table';
	// $charset_collate = $wpdb->get_charset_collate();

	// $esists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;

	// checking for existence of table
	// if(!$exists) {
		// create table
		// $sql = 'CREATE TABLE ' . $table_name . '(
		// 					id IDENTITY(1) PRIMARY KEY, 
		// 					artist VARCHAR(50) NOT NULL,
		// 					cd VARCHAR(50) NOT NULL
		// 					)';
		// $wpdb->query($sql);
		// // dbDelta($sql);
		// // insert some data
		// $sql = 'INSERT INTO '. $table_name . '(artist,cd) VALUES ("Wolfgang Amadeus Mozart","Requiem")';
		// $wpdb->query($sql);
		// $sql = "INSERT INTO ". $table_name . "(artist,cd) VALUES ('Johann Sebastian Bach','Cello Suite No. 1')";
		// $wpdb->query($sql);
		// $sql = "INSERT INTO ". $table_name . "(artist,cd) VALUES ('Ludwig van Beethoven','Mondschein Sonate')";
		// $wpdb->query($sql);
		// $sql = "INSERT INTO ". $table_name . "(artist,cd) VALUES ('Giuseppe Verdi','Reqiuem')";
		// $wpdb->query($sql);
		// $sql = "INSERT INTO ". $table_name . "(artist,cd) VALUES ('Chopin','Nocturne op.9 No.2 ')";
		// $wpdb->query($sql);
		// $sql = "INSERT INTO ". $table_name . "(artist,cd) VALUES ('Schubert','Serenade')";
		// $wpdb->query($sql);
	// }
//}

# register_activation_hook(__FILE__,'cm_cd_install');


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
	// boolean to ensure some things only happen once at a time
	$only_once = false;
	// are there any posts (of type cd_album) ?
	if( $loop->have_posts() ) {
		$output .= '<table class="table table-hover">';
		$output .= '<tr><th>Titel</th><th>K&uuml;nstler</th><th>Test</th><th>Songs</th></tr>';

		// as long as we have new entries (posts)
		while ( $loop->have_posts() ){
			// load next values into all global variables
			$loop->the_post();
			if( $only_once ) {
				add_post_meta(get_the_ID(),'song', 'I would do anything for love', false);
				add_post_meta(get_the_ID(),'song', 'Life is a lemon', false);
				add_post_meta(get_the_ID(),'song', 'Rock and Roll dreams come through', false);
				add_post_meta(get_the_ID(),'song', 'It just won t quit', false);
				add_post_meta(get_the_ID(),'song', 'Out of the frying pan', false);
				add_post_meta(get_the_ID(),'song', 'Objects in the rear view mirrormay appear closer than they are', false);
				$only_once = false;
			}		
			$output .= '<tr>';
			// output the title as link
			$output .= the_title('<td><a href="' . get_permalink() . '">','</a></td>',false);
			$output .= '<td>';
			//
			$output .= get_the_term_list(get_the_ID(), 'artists','',',','');
			// return all meta data
			$output .= '</td><td>' . the_meta();
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
	// cycle through the array (value contains the song)
	foreach((array)$titleArray as $key => $value) {
		?>
		<!--  output a checkbox and text input field for each song-->
		<p><input type="checkbox" name="haken<?php echo $key; ?>" checked /><?php echo ($key+1); ?>. Titel: <input tyüe="text" name="eingabe<?php echo $key; ?>" value="<?php echo $value; ?>"/></p>
	<?php
	}
	// output a text input to add a new song
	echo 'Neuen Titel eingeben';
	?>
	<p>Titel:<input type="text" name="neu" value="" /></p>
<?php
}
// add the custom metabox
add_action( 'add_meta_boxes' , 'my_custom_box_create' );


// saving function when "Aktualisieren" is pressed
function cm_cd_save_meta($cd_id) {
	// read old songs from meta data
	$oldArray = get_post_custom_values('song', $cd_id);
	//$oldLength = count($oldArray);


	// cycle trhough all entries and update their contents by the specific input field
	foreach((array)$oldArray as $key => $value) {
		// $key = number, $value = song
		update_post_meta($cd_id,'song', $_POST[('eingabe'.$key)],$value);
		// if there is no checked checkbox delete the data
		if(! isset($_POST[('haken'.$key)])) {
			delete_post_meta($cd_id, 'song', $value);
		}
	}
	
	// read new input	
	$newInput = strip_tags($_POST['neu']);
	// add new song if the input is not empty
	if( $newInput != '') {
		add_post_meta($cd_id, 'song', $newInput, false);
	}
}

add_action('save_post', 'cm_cd_save_meta');

?>
