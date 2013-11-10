<?php 
/*
Plugin Name: Generic Leaflet Map
Description: A demo WordPress plugin that shows how to create a Leaflet map using data from a custom post type.
Version: 0.1
Author: Alicia Duffy & Ben Bond
Author URI: http://google.com
License: GPLv2+
* To Do:
* - Create options page for refreshing geoJSON document, adding cloudmade and google maps API,
*   map ID and zoom options, map icon colors.
*/


$generic_map_css = plugin_dir_url(__FILE__) . 'styles/';
$generic_map_js = plugin_dir_url(__FILE__) . 'js/';

/* Register styles and scripts */
wp_register_style( 'font_awesome', $generic_map_css . 'font-awesome.min.css', '', '0.6.3' );
wp_register_style( 'leaflet_markers', $generic_map_css . 'leaflet.awesome-markers.css', '', '0.6.3' );
wp_register_style( 'leaflet_style', $generic_map_css . 'leaflet-0.6.3.css', '', '0.6.3' );

wp_register_script( 'leaflet-js', $generic_map_js . 'leaflet-0.6.3.js', '', '0.6.3', true );
wp_register_script( 'leaflet-awesome-markers', $generic_map_js . 'leaflet.awesome-markers.js', '', '0.6.3', true );

wp_register_script( 'geojson', get_site_url() . '/wp-content/uploads/geojson.php', array( 'jquery' ), '', true );
wp_register_script( 'leaflet-config', $generic_map_js . 'leaflet-config.js', '', '', true );


/* Enqueue styles */
add_action( 'wp_enqueue_scripts', 'leaflet_styles' );
function leaflet_styles() {
	wp_enqueue_style( 'font_awesome' );
	wp_enqueue_style( 'leaflet_markers' );
	wp_enqueue_style( 'leaflet_style' );
    // Register stylesheet
    wp_register_style( "leaflet-ie", get_stylesheet_directory_uri() . "/styles/leaflet.ie.css" );
    // Apply IE conditionals
    $GLOBALS["wp_styles"]->add_data( "leaflet-ie", "conditional", "lte IE 8" );
    // Enqueue stylesheet
    wp_enqueue_style( "leaflet-ie" );
}

/* Register shortcode, on use enqueues scripts */ 
add_shortcode('leaflet-map', 'map_shortcode');
function map_shortcode($atts) {
	wp_enqueue_script( 'leaflet-js' );
	wp_enqueue_script( 'leaflet-awesome-markers' );
	wp_enqueue_script( 'geojson' );
	wp_enqueue_script( 'leaflet-config' );

	echo '<div class="map-inner" style="width: 100%; height: 500px;">
			<div id="map" style="width: 100%; height: 100%;"></div>
		  </div>';
}

/* Register location post type */
add_action( 'init', 'generic_map_post_types' );
function generic_map_post_types() {

	$labels = array(
		'name'               => _x( 'Locations', 'post type general name' ),
		'singular_name'      => _x( 'Location', 'post type singular name' ),
		'add_new'            => _x( 'Add Location', 'Test' ),
		'add_new_item'       => __( 'Add Location' ),
		'edit_item'          => __( 'Edit Location' ),
		'new_item'           => __( 'New Location' ),
		'all_items'          => __( 'Locations' ),
		'view_item'          => __( 'View Location' ),
		'search_items'       => __( 'Search Locations' ),
		'not_found'          =>  __( 'No Locations found' ),
		'not_found_in_trash' => __( 'No Locations found in Trash' ),
		'parent_item_colon'  => '',
		'menu_name'          => 'Locations'
	);
	
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'with_front' => false, 'slug' => 'location' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 25,
		'supports'           => array( 'title', 'thumbnail', 'comments', 'editor' )
	);
	
	register_post_type( 'location', $args );	
}

/* Register location symbol custom taxonomy */
add_action( 'init', 'generic_map_add_custom_taxonomy', 0 );
function generic_map_add_custom_taxonomy() {
	register_taxonomy('location_symbol', 'location', array(
		'labels' => array(
			'name'          => _x( 'Location Symbols', 'taxonomy general name' ),
			'singular_name' => _x( 'Location Symbol', 'taxonomy singular name' ),
			'search_items'  =>  __( 'Search Location Symbols' ),
			'all_items'     => __( 'All Location Symbols' ),
			'edit_item'     => __( 'Edit Location Symbol' ),
			'update_item'   => __( 'Update Location Symbol' ),
			'add_new_item'  => __( 'Add New Location Symbol' ),
			'new_item_name' => __( 'New Location Symbol Name' ),
			'menu_name'     => __( 'Location Symbols' ),
		),
		'rewrite' => array(
			'slug'       => 'location-symbols', 
			'with_front' => false,
		),
	));
}

/* Create ZIP code and coordinates custom meta fields */	
add_action( 'add_meta_boxes', 'location_meta_box_add' );
function location_meta_box_add() {
	add_meta_box( 'location_address', 'Location Address', 'location_meta_box', 'location', 'normal', 'high' );
}

function location_meta_box( $post ) {
	$values = get_post_custom( $post->ID );
	$zip_code = isset( $values['zip_code'] ) ? esc_attr( $values['zip_code'][0] ) : '';
	$coordinates = isset( $values['coordinates'] ) ? esc_attr( $values['coordinates'][0] ) : '';
	wp_nonce_field( 'location_address_nonce', 'meta_box_nonce' );
	?>
	<p>
		<label for="zip_code">Zip Code</label>
		<input type="text" name="zip_code" id="zip_code" value="<?php echo $zip_code; ?>" />
	</p>
	<p>
		<label for="coordinates">Coordinates</label>
		<input type="text" name="coordinates" id="coordinates" value="<?php echo $coordinates; ?>" />
	</p>
	<?php	
}

/* Save custom meta */
add_action( 'save_post', 'location_meta_box_save' );
function location_meta_box_save( $post_id ) {
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'location_address_nonce' ) ) return;
	
	if( !current_user_can( 'edit_post' ) ) return;

	$allowed = array( 
		'a' => array(
			'href' => array()
		)
	);
	
	if( isset( $_POST['zip_code'] ) )
		update_post_meta( $post_id, 'zip_code', wp_kses( $_POST['zip_code'], $allowed ) );
	
	if( isset( $_POST['coordinates'] ) )
		update_post_meta( $post_id, 'coordinates', wp_kses( $_POST['coordinates'], $allowed ) );
}
		

/* Calculate coordinates based on zip code using Google Maps API */	
add_action( 'load-post.php', 'generic_map_location_admin_init' );
add_action( 'load-post-new.php', 'generic_map_location_admin_init' );

function generic_map_location_admin_init() {
	$generic_map_css = plugin_dir_url(__FILE__) . 'styles/';
	$generic_map_js = plugin_dir_url(__FILE__) . 'js/';
	
    $screen = get_current_screen();
	wp_register_script( 'googlemaps', 'http://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyBrp6oUKq1reA5Pu_5ebqHh7Gdvibqg0tE&sensor=false' );

	wp_register_script( 'field-automator', $generic_map_js . 'field-automator.js', array( 'jquery', 'googlemaps'  ), '', true );
					
	if ($screen->id == 'location' ) {
		wp_enqueue_script( 'googlemaps' );
		wp_enqueue_script( 'field-automator' );
	}
}

/* Add refresh/create feed button */
function plugin_add_settings_link( $links ) {
    if( file_exists('../wp-content/uploads/geojson.php' )){
      $settings_link = '<b><a href="../location/?geo" style="color:#0ca500;">Refresh Cache File</a></b>';
    }
    else{
      $settings_link = '<b><a href="../location/?geo" style="color:#ff0000;">Create Cache File</a></b>';
    }
  	array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

add_action('template_redirect' , 'jsonrequest_template_redirect');


/* Create GeoJSON file */
function jsonrequest_template_redirect() {
	
	if ( get_post_type( get_the_ID() ) == "location" && is_archive() ) {
	    global $query_string, $post;
		query_posts( $query_string . '&order=DEC&posts_per_page=2000' );
	}
	$post_id = $post->ID;
	
	if(isset($_REQUEST['geo'])) :  
	    if( have_posts() ) : 
	        while( have_posts() ) : the_post();
	            $output[]= array( 
	            	'title' => get_the_title(), 
	            	'meta' => get_post_custom(), 
	            	'id'=>get_the_id()
	            );
	        endwhile;
	    endif; 
	  
		//define the file
		$upload_dir = wp_upload_dir();
		$theFile = $upload_dir['basedir'].'/geojson.php';
		$expiration_date = strtotime("+1 day"); 
		$now = strtotime("now"); 
		//Add time trigger to file
		$callFile = get_site_url().'/location/?geo';
		$callFile_withlogin = 'http://admin:password@';
		$callFile_withlogin .= preg_replace('#^https?://#', '', $callFile);
		$fileContents .= '
			<?php 
	        
	        $expiration_date = '.$expiration_date.';
	        $now = strtotime("now");
	        
	        if ( $expiration_date < $now ) {
				echo "expired";
				$refres_file = file_get_contents("'.$callFile.'");
				if ( $refres_file ) {
					echo "<meta http-equiv=\"refresh\" content=\"5\"> Refreshing...";
				} else {
					$refres_file = file_get_contents("'.$callFile_withlogin.'");
					if($refres_file) {
				    	echo "<meta http-equiv=\"refresh\" content=\"5\"> Refreshing...";
					}
				}
	        } else {
	
			?>
	    ';
	   
	
	$fileContents .= '
	var locationMap = {
	"type": "FeatureCollection",
	"features": [
	    ';
	$count = 1;
	foreach ($output as $key => $value) {
		$terms = get_the_terms( $value['id'], 'location_symbol' );
		$term = array_pop($terms);
		$fileContents .='
			{"geometry": {
		            "type": "Point",
		            "coordinates": ['.$value['meta']['coordinates'][0].']
		        },
		        "type": "Feature",
		        "properties": {
		            "name": "' . $value['title'] . '",
		            "link": "' . get_permalink( $value['id'] ) . '",
		            "symbol": "' . $term->name . '"
		        },
		        "id": '.$count.'
		    },
		    ';
		$count++;
	}
	
	   //writefile
	   $fileContents .='
	        ]
	};
	    <?php } ?>';
	   
	   
	     file_put_contents("$theFile", $fileContents);
	     
	     echo "A new cache file has been created at <b>".site_url()."/wp-content/uploads/geojson.php</b>. ";
	    die();
	else: return;
	endif;
} // end template_redirect