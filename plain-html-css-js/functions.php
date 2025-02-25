<?php

if (! function_exists('my_property_chatbot')) {
	function my_property_chatbot()
	{
		wp_enqueue_style('property-chatbot-style', HOUZEZ_CSS_DIR_URI . 'chatbot.css');
		wp_enqueue_script(
			'property-chatbot-script',
			HOUZEZ_JS_DIR_URI . 'chatbot.js',
			array(),
			null,
			true
		);

		ob_start();
		get_template_part('property-details/partials/chatbot');
		return ob_get_clean();
	}

	add_shortcode("my_property_chatbot", "my_property_chatbot");
}

if (!function_exists('chatbot_ajax_request')) {
	function chatbot_ajax_request()
	{
		$query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';
		$api_url = "https://api.openai.com/v1/chat/completions";
		$api_key = "";

		$request_body = [
			"model" => "gpt-4o",
			"messages" => [
				[
					"role" => "system",
					"content" => "You are a helpful assistant."
				],
				[
					"role" => "user",
					"content" => "Parse the following string and map it to the specified keys in JSON format: \"$query\"\n\nKeys:\n- chat_title (Title of the overall response)\n- bedrooms\n- bathrooms\n- min_area\n- max_area\n- sale_rent\n- min_price\n- max_price\n- property_type\n- publish_date\n- location\n- country\n- state\n- property_area\n- latitude\n- longitude\n- radius\n- knowledge_based\n- irrelevant\n- sample_questions\n- description\n\nInstructions:\n\n1. Property Type: Use one of the following values for the 'property_type' key: 'Commercial Lease', 'commercial-lease', 'Commercial Sale', 'commercial-sale', 'Land', 'land', 'Residential', 'residential', 'Residential Income', 'residential-income', 'Residential Lease', 'residential-lease'.\n\n2. City Deduction: If a city name is mentioned without specifying the country and state, deduce and assign the appropriate values to 'country', 'state', and 'location'. Use full names of cities and countries only. If a city is given, assign it to the 'location' key.\n\n3. Empty Keys: For any keys not found in the string, return the key with an empty string.\n\n4. General Inquiries: For general real estate inquiries, commuting questions, property trends, market analysis, or advice on buying and selling homes, provide detailed and comprehensive HTML-formatted content for the 'description' key. Include comparisons and tables where relevant for analytics data. Use HTML tags for formatting, such as <h3>, <h4>, <strong>, </br>, <span>, <ul>, <ol>, <li>, <table>, <th>, <tr>, <td>, and <p>. Avoid simple paraphrasing and aim for high-quality, descriptive content. Use internet resources and available data.\n\n5. Irrelevant Strings: If the string is irrelevant, return empty strings for all keys and set 'irrelevant' to true. For greetings and other irrelevant questions, provide a humanaoid short answer and assign sample questions related to properties in USA cities.\n\n6. Knowledge-Based Questions: If the string is knowledge-based and not related to property searches, set 'knowledge_based' to true. Ensure the 'description' key is filled with detailed HTML content.\n\n7. Sample Questions: If the string is not knowledge-based, provide two to three knowledge-based questions related to the string content. If the string is knowledge-based, provide two to three property-related questions, such as '2 bedrooms or 4 bedrooms 1 bathroom house in the city user provided or nearby.'\n\n8. Language Handling: If the user query is in Chinese, provide the 'description' and 'sample_questions' in Chinese.\n\n9. ZIP Code: If the user adds a ZIP code, assume it is in the USA, and search based on the ZIP code. Assign the city to 'location'.\n\n10. Arrays: Ensure 'property_type', 'property_area', 'location', 'state', and 'sale_rent' are arrays. The 'sale_rent' values must be 'for-rent' or 'for-sale'.\n\n11. Extended View Trigger: If the response is too long, contains structured data (e.g., large tables, multiple lists, in-depth analytics), or needs more space to be properly displayed, set 'requires_extended_view': true.\n - When requires_extended_view is true, should generate a concise summary (max 2 sentences) of the user query and store it in 'short_description', Otherwise short_description should be empty.\n\nOutput:\nReturn only the JSON object as valid JSON."
				]
			]
		];

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $api_key
		];

		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));

		$response = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_errno($ch)) {
			error_log('cURL error: ' . curl_error($ch));
			echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
			die();
		}

		if ($http_status !== 200) {
			error_log('API request failed with status ' . $http_status . ' Response: ' . $response);
			echo json_encode(['error' => 'API Request Failed', 'status' => $http_status, 'response' => $response]);
			die();
		}

		$result = json_decode($response, true);
		if ($result === null) {
			echo json_encode(['error' => 'Invalid JSON Response', 'raw_response' => $response]);
			die();
		}

		$content = $result['choices'][0]['message']['content'] ?? null;
		if (!$content) {
			echo json_encode(['error' => 'Missing AI response']);
			die();
		}

		$json_string = trim($content, '`');
		$json_string = trim($json_string, 'json');
		$json_array = json_decode($json_string, true);

		if ($json_array === null) {
			echo json_encode(['error' => 'Failed to parse JSON content', 'content' => $content]);
			die();
		}

		$query_args = array(
			'post_type' => 'property',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'date',
			'order' => 'DESC'
		);
		$tax_query = array();
		$meta_query = array();
		$search_data = array();
		$chat_title = isset($json_array['chat_title']) ? $json_array['chat_title'] : '';
		$bedrooms_filter = isset($json_array['bedrooms']) ? (int) $json_array['bedrooms'] : '';
		$bathrooms_filter = isset($json_array['bathrooms']) ? (int) $json_array['bathrooms'] : '';
		$min_area = isset($json_array['min_area']) ? (float) $json_array['min_area'] : '';
		$max_area = isset($json_array['max_area']) ? (float) $json_array['max_area'] : '';
		$sale_rent = isset($json_array['sale_rent']) ? $json_array['sale_rent'] : array();
		$min_price = isset($json_array['min_price']) ? (float) $json_array['min_price'] : '';
		$max_price = isset($json_array['max_price']) ? (float) $json_array['max_price'] : '';
		$property_type = isset($json_array['property_type']) ? $json_array['property_type'] : array();
		$location = isset($json_array['location']) ? $json_array['location'] : array();
		$property_area = isset($json_array['property_area']) ? $json_array['property_area'] : array();
		$zipcode = isset($json_array['zipcode']) ? $json_array['zipcode'] : array();
		$country = isset($json_array['country']) ? $json_array['country'] : '';
		$state = isset($json_array['state']) ? $json_array['state'] : array();
		$latitude = isset($json_array['latitude']) ? (float) $json_array['latitude'] : '';
		$longitude = isset($json_array['longitude']) ? (float) $json_array['longitude'] : '';
		$radius = isset($json_array['radius']) ? (int) $json_array['radius'] : '';
		$publish_date = isset($json_array['publish_date']) ? $json_array['publish_date'] : '';
		$irrelevant = isset($json_array['irrelevant']) ? (bool) $json_array['irrelevant'] : '';
		$knowledge_based = isset($json_array['knowledge_based']) ? (bool) $json_array['knowledge_based'] : '';
		$description = isset($json_array['description']) ? $json_array['description'] : '';
		$sample_questions = isset($json_array['sample_questions']) ? $json_array['sample_questions'] : array();
		$requires_extended_view = isset($json_array['requires_extended_view']) ? (bool) $json_array['requires_extended_view'] : '';
		$response_overview = isset($json_array['short_description']) ? $json_array['short_description'] : '';

		if (!empty($bedrooms_filter)) {
			$meta_query[] = array(
				'key' => 'fave_property_bedrooms',
				'value' => $bedrooms_filter,
				'compare' => '='
			);
		}

		if (!empty($bathrooms_filter)) {
			$meta_query[] = array(
				'key' => 'fave_property_bathrooms',
				'value' => $bathrooms_filter,
				'compare' => '='
			);
		}

		if ($min_area && !empty($max_area)) {
			$meta_query[] = array(
				'key' => 'fave_property_size',
				'value' => array($min_area, $max_area),
				'type' => 'NUMERIC',
				'compare' => 'BETWEEN',
			);
		} elseif (!empty($min_area)) {
			$meta_query[] = array(
				'key' => 'fave_property_size',
				'value' => $min_area,
				'type' => 'NUMERIC',
				'compare' => '>=',
			);
		} elseif (!empty($max_area)) {
			$meta_query[] = array(
				'key' => 'fave_property_size',
				'value' => $max_area,
				'type' => 'NUMERIC',
				'compare' => '<=',
			);
		}

		if (!empty($min_price) && !empty($max_price)) {
			$min_price = doubleval(houzez_clean($min_price));
			$max_price = doubleval(houzez_clean($max_price));

			if ($min_price >= 0 && $max_price > $min_price) {
				$meta_query[] = array(
					'key' => 'fave_property_price',
					'value' => array($min_price, $max_price),
					'type' => 'NUMERIC',
					'compare' => 'BETWEEN',
				);
			}
		} else if (!empty($min_price)) {
			$min_price = doubleval(houzez_clean($min_price));
			if ($min_price >= 0) {
				$meta_query[] = array(
					'key' => 'fave_property_price',
					'value' => $min_price,
					'type' => 'NUMERIC',
					'compare' => '>=',
				);
			}
		} else if (!empty($max_price)) {
			$max_price = doubleval(houzez_clean($max_price));
			if ($max_price >= 0) {
				$meta_query[] = array(
					'key' => 'fave_property_price',
					'value' => $max_price,
					'type' => 'NUMERIC',
					'compare' => '<=',
				);
			}
		}

		// $keyword = stripcslashes($keyword);
		// if ($keyword != '') {
		// 		$keyword = trim($keyword);
		// 		if (!empty($keyword)) {
		// 			$query_args['s'] = $keyword;
		// 		}
		// }

		if (!empty($property_type)) {
			$tax_query[] = array(
				'taxonomy' => 'property_type',
				'field' => 'slug',
				'terms' => $property_type,
			);
		}

		if (!empty($sale_rent)) {
			$tax_query[] = array(
				'taxonomy' => 'property_status',
				'field' => 'slug',
				'terms' => $sale_rent
			);
		}

		if (!empty($country) || !empty($state) || !empty($property_area) || !empty($location)) {
			$_tax_query = array();
			$_tax_query['relation'] = 'OR';

			if (!empty($country)) {
				$_tax_query[] = array(
					'taxonomy' => 'property_country',
					'field' => 'slug',
					'terms' => $country,
				);
			}

			if (!empty($state)) {
				$_tax_query[] = array(
					'taxonomy' => 'property_state',
					'field' => 'slug',
					'terms' => $state,
				);
			}

			if (!empty($property_area)) {
				$_tax_query[] = array(
					'taxonomy' => 'property_area',
					'field' => 'slug',
					'terms' => $property_area
				);
			}

			if (!empty($location)) {
				$_tax_query[] = array(
					'taxonomy' => 'property_city',
					'field' => 'slug',
					'terms' => $location
				);
			}
			$tax_query[] = $_tax_query;
		}

		if (!empty($publish_date)) {
			$publish_date = explode('/', $publish_date);
			$query_args['date_query'] = array(
				array(
					'year' => $publish_date[2],
					'compare'   => '>=',
				),
				array(
					'month' => $publish_date[1],
					'compare'   => '>=',
				),
				array(
					'day' => $publish_date[0],
					'compare'   => '>=',
				)
			);
		}

		$query_args = apply_filters('houzez_radius_filter', $query_args, $latitude, $longitude, $radius, false, $location);

		$tax_count = count($tax_query);
		if ($tax_count > 0) {
			$tax_query['relation'] = 'AND';
			$query_args['tax_query']  = $tax_query;
		}

		$meta_count = count($meta_query);
		if ($meta_count > 0 || !empty($keyword_array)) {
			$query_args['meta_query'] = array(
				'relation' => 'AND',
				$keyword_array,
				array(
					'relation' => 'AND',
					$meta_query
				),
			);
		}

		if (!empty($bedrooms_filter)) {
			$search_data['bedrooms'] = $bedrooms_filter;
		}

		if (!empty($bathrooms_filter)) {
			$search_data['bathrooms'] = $bathrooms_filter;
		}

		if (!empty($min_area)) {
			$search_data['min_area'] = $min_area;
		}
		if (!empty($max_area)) {
			$search_data['max_area'] = $max_area;
		}

		if (!empty($sale_rent)) {
			$search_data['sale_rent'] = $sale_rent;
		}

		if (!empty($min_price)) {
			$search_data['min_price'] = $min_price;
		}

		if (!empty($max_price)) {
			$search_data['max_price'] = $max_price;
		}

		if (!empty($property_type)) {
			$search_data['property_type'] = $property_type;
		}

		if (!empty($radius)) {
			$search_data['radius'] = $radius;
		}

		if (!empty($location)) {
			$search_data['location'] = $location;
		}

		if (!empty($country)) {
			$search_data['country'] = $country;
		}

		if (!empty($state)) {
			$search_data['state'] = $state;
		}

		if (!empty($latitude)) {
			$search_data['latitude'] = $latitude;
		}

		if (!empty($longitude)) {
			$search_data['longitude'] = $longitude;
		}

		if (!empty($property_area)) {
			$search_data['property_area'] = $property_area;
		}

		if (!empty($zipcode)) {
			$search_data['zipcode'] = $zipcode;
		}


		curl_close($ch);

		$all_properties = array();
		$map_properties_data = array();
		$wp_args = new WP_Query($query_args);
		$total_properties = $wp_args->found_posts;

		$default_image_url = 'https://www.linkhomeai.com/wp-content/uploads/2022/01/home-header-2.jpg';

		if ($wp_args->have_posts()) {
			$counter = 0;
			while ($wp_args->have_posts()): $wp_args->the_post();
				$post_id = get_the_ID();
				$property_location = get_post_meta($post_id, 'fave_property_location', true);
				$lat_lng = explode(',', $property_location);
				$prop_images = get_post_meta($post_id, 'fave_property_images', false);
		
				// Add data to the map_properties_data array for all properties
				$map_properties_data[] = array(
					'id' => $post_id,
					'title' => get_the_title(),
					'lat' => $lat_lng[0],
					'lng' => $lat_lng[1],
					'url' => get_permalink(),
					'pricePin' => houzez_listing_price_map_pins(),
				);
		
				// Add only the first 10 properties to the main list
				if ($counter < 10) {
					$prop = new stdClass();
		
					$prop->id = $post_id;
					$prop->title = get_the_title();
					$prop->sanitizetitle = sanitize_title(get_the_title());
					$prop->lat = $lat_lng[0];
					$prop->lng = $lat_lng[1];
					$prop->bedrooms = get_post_meta($post_id, 'fave_property_bedrooms', true);
					$prop->bathrooms = get_post_meta($post_id, 'fave_property_bathrooms', true);
					$prop->garages = get_post_meta($post_id, 'fave_property_garage', true);
					$prop->size = get_post_meta($post_id, 'fave_property_size', true);
					$prop->address = get_post_meta($post_id, 'fave_property_map_address', true);
					$prop->thumbnail = get_the_post_thumbnail($post_id, 'houzez-property-thumb-image');
					$property_image = get_the_post_thumbnail_url($post_id, 'houzez-item-image-1') ?: $default_image_url;
					$prop->imageUrl = $property_image;
					$prop->url = get_permalink();
					$prop->prop_meta = houzez_listing_meta_v1();
					$prop->type = houzez_taxonomy_simple('property_type');
					$prop->images_count = count($prop_images);
					$prop->price = houzez_listing_price_v1();
					$prop->pricePin = houzez_listing_price_map_pins();
		
					$all_properties[] = $prop;
				}
		
				$counter++;
			endwhile;
		}

		wp_reset_postdata();

		// Return JSON response
		wp_send_json_success(array(
			'total_properties' => $total_properties,
			'properties' => $all_properties, // First 10 properties for the main list
			'map_properties_data' => $map_properties_data, // All properties for the map
			'description' => $description,
			'sample_questions' => $sample_questions,
			'knowledge_based' => $knowledge_based,
			'irrelevant' => $irrelevant,
			'search_data' => $search_data,
			'chat_title' => $chat_title,
			'requires_extended_view' => $requires_extended_view,
			'response_overview' => $response_overview,
		));
	}

	// Register AJAX actions
	add_action('wp_ajax_chatbot_ajax_request', 'chatbot_ajax_request');
	add_action('wp_ajax_nopriv_chatbot_ajax_request', 'chatbot_ajax_request');
}

//School Fuction Start

if (!function_exists('schoolapi_ajax_request')) {
	function schoolapi_ajax_request()
	{
		// Check if address is provided
		$address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';

		if (empty($address)) {
			wp_send_json_error(array('message' => 'Address is required.'));
			return;
		}

		$school_api_key = '';
		$school_api_url = "https://api.greatschools.org/schools/nearby?key=$school_api_key&address=" . urlencode($address) . "&radius=5"; // Adjust radius as needed

		$response = wp_remote_get($school_api_url);

		if (is_wp_error($response)) {
			wp_send_json_error(array('message' => 'Failed to retrieve school information.'));
			return;
		}

		$body = wp_remote_retrieve_body($response);
		$result = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(array('message' => 'Error decoding JSON response.'));
			return;
		}

		// Process and format the GreatSchools API response
		$schools = isset($result['schools']) ? $result['schools'] : [];

		$formatted_schools = array();
		foreach ($schools as $school) {
			$formatted_schools[] = array(
				'name' => isset($school['name']) ? $school['name'] : 'N/A',
				'address' => isset($school['address']) ? $school['address'] : 'N/A',
				'rating' => isset($school['rating']) ? $school['rating'] : 'N/A',
				'distance' => isset($school['distance']) ? $school['distance'] . ' miles' : 'N/A',
			);
		}

		// Return JSON response
		wp_send_json_success(array(
			'schools' => $formatted_schools
		));
	}

	// Register AJAX actions
	add_action('wp_ajax_schoolapi_ajax_request', 'schoolapi_ajax_request');
	add_action('wp_ajax_nopriv_schoolapi_ajax_request', 'schoolapi_ajax_request');
}
