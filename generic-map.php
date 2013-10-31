<?php 
/*
Plugin Name: Generic Leaflet Map
Description: A WordPress plugin to create a generic Leaflet map using data from a location post type.
Version: 0.1
Author: Alicia Duffy & Ben Bond
Author URI: http://google.com
License: GPLv2+
*/
$generic_map_css = plugin_dir_url(__FILE__) . 'styles/';
$generic_map_js = plugin_dir_url(__FILE__) . 'js/';

wp_register_style( 'leaflet_css', $generic_map_css . 'leaflet-0.6.3.css', '', '0.6.3' );
wp_register_script( 'leaflet_js', $generic_map_js . 'leaflet-0.6.3.js', '', '0.6.3', true );
wp_register_script( 'geojson', get_site_url() . '/wp-content/uploads/geojson.php', array( 'jquery' ), '', true );
wp_register_script( 'leaflet_config', $generic_map_js . 'leaflet-config.js', '', '', true );

add_action( 'init', 'generic_map_post_types' );
function generic_map_post_types() {

	$labels = array(
		'name' => _x( 'Locations', 'post type general name' ),
		'singular_name' => _x( 'Location', 'post type singular name' ),
		'add_new' => _x( 'Add Location', 'Test' ),
		'add_new_item' => __( 'Add Location' ),
		'edit_item' => __( 'Edit Location' ),
		'new_item' => __( 'New Location' ),
		'all_items' => __( 'Locations' ),
		'view_item' => __( 'View Location' ),
		'search_items' => __( 'Search Locations' ),
		'not_found' =>  __( 'No Locations found' ),
		'not_found_in_trash' => __( 'No Locations found in Trash' ),
		'parent_item_colon' => '',
		'menu_name' => 'Locations'
	);
	
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => array( 'with_front' => false, 'slug' => 'location' ),
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => 25,
		'supports' => array( 'title', 'thumbnail', 'comments', 'editor' )
	);
	
	register_post_type( 'location', $args );
	
}

	
add_action( 'add_meta_boxes', 'cd_meta_box_add' );
function cd_meta_box_add() {
	add_meta_box( 'location_address', 'Location Address', 'location_meta_box_cb', 'location', 'normal', 'high' );
}

function location_meta_box_cb( $post ) {
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


add_action( 'save_post', 'cd_meta_box_save' );
function cd_meta_box_save( $post_id ) {
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'location_address_nonce' ) ) return;
	
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// now we can actually save the data
	$allowed = array( 
		'a' => array( // on allow a tags
			'href' => array() // and those anchords can only have href attribute
		)
	);
	
	if( isset( $_POST['zip_code'] ) )
		update_post_meta( $post_id, 'zip_code', wp_kses( $_POST['zip_code'], $allowed ) );
	
	if( isset( $_POST['coordinates'] ) )
		update_post_meta( $post_id, 'coordinates', wp_kses( $_POST['coordinates'], $allowed ) );
}
		
		
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



	
add_action( 'wp_enqueue_scripts', 'leaflet_css' );
function leaflet_css() {
	
	wp_enqueue_style( 'leaflet_css' );
    // Register stylesheet
    wp_register_style( "leaflet-ie", get_stylesheet_directory_uri() . "/styles/leaflet.ie.css" );
    // Apply IE conditionals
    $GLOBALS["wp_styles"]->add_data( "leaflet-ie", "conditional", "lte IE 8" );
    // Enqueue stylesheet
    wp_enqueue_style( "leaflet-ie" );
}


add_shortcode('leaflet_map', 'map_shortcode');
function map_shortcode($atts) {
	wp_enqueue_script( 'leaflet_js' );
	wp_enqueue_script( 'geojson' );
	wp_enqueue_script( 'leaflet_config' );
			
	add_action( 'wp_enqueue_scripts', 'leaflet_css' );

	echo '<div class="map-inner">
			<div id="map" style="width: 100%; height: 100%;"></div>
		  </div>';
}