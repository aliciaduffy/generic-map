<?php
// Create GeoJSON file
function jsonrequest_template_redirect() {
	//Setup the and verify post type exist
	if ( get_post_type( get_the_ID() ) == "location" && is_archive() )
	{
	    global $query_string, $post;
	    //Set maximum number of post to 2000. This can be changed
		query_posts( $query_string . '&order=DEC&posts_per_page=2000' );
	}

	$post_id = $post->ID;
	
	//Receive the geo request call from the "Create Cache File" option 
	if(isset($_REQUEST['geo']))
	{ 
	    if( have_posts() )
	    { 
	        while( have_posts() )
	        {
	        	the_post();
	            $output[]= array( 
	            	'title' => get_the_title(), 
	            	'meta' => get_post_custom(), 
	            	'id'=>get_the_id()
	            );
	        }
	    }
	  
		//define the file an place in the uploads directory
		$upload_dir = wp_upload_dir();
		$theFile = $upload_dir['basedir'].'/geojson.php';

		/**
		* This is where you specify how frequently you would like the change file to update
		* +1 month = every month, +1 week = weekly,  +1 day = every day, +1 hour = every hour etc..
		* more info @ http://www.php.net/manual/en/function.strtotime.php  
		*/
		$expiration_date = strtotime("+1 day"); 
		$now = strtotime("now");

		//Add time trigger to file. Once file is visited we check for expiration.
		$callFile = get_site_url().'/location/?geo';
		if(!empty($output))
		{
			$fileContents .= '
				<?php 
		        
		        $expiration_date = '.$expiration_date.';
		        $now = strtotime("now");
		        
		        if ( $expiration_date < $now ) {
					echo "expired";
					$refres_file = file_get_contents("'.$callFile.'");
					if ( $refres_file ) {
						echo "<meta http-equiv=\"refresh\" content=\"5\"> Refreshing...";
					} 
		        } else {
		
				?>
		    ';

			//Creating the geo JSON feed within the PHP file
			$fileContents .= '
			var locationMap = {
			"type": "FeatureCollection",
			"features": [
			    ';
			$count = 1;

			foreach ($output as $key => $value) 
			{
				$terms = get_the_terms( $value['id'], 'location_symbol' );
				$term = array_pop($terms);
				$fileContents .='
				{
					"geometry": {
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
	
	   		
	   		$fileContents .='
	        	]
				};
	    		<?php } ?>';

	   		//writefile
	   		file_put_contents("$theFile", $fileContents);
	    	echo "A new cache file has been created at <b>".site_url()."/wp-content/uploads/geojson.php</b>. ";
	    	die();
		}

		else
		{
			//Only write a file if we have created location post
			echo "No locations added. Please add a location before creating a cache file";
			die();
		}
	}
} // end template_redirect