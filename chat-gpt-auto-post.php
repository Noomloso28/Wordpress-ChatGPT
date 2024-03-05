<?php
/*
Plugin Name: Chat-GPT Auto Post from API
Plugin URI: http://www.example.com/auto-post-from-api
Description: Automatically posts content from an API every day.
Version: 1.0
Author: Noom
Author URI: http://www.example.com
License: GPL2
*/

require_once 'ApiData.php';
require_once 'Amz/AmzProduct.php';

use ChateGPT\ApiData;
use Amazon\Affiliate\Api\AmzProduct;

global $wpdb;
$data = new ApiData($wpdb);

//var_dump(time());



$AmzProduct = new AmzProduct();
var_dump($AmzProduct->getProduct());

// Get content from API and create a new post
//$post_title = $data->getSeoTitle();
//var_dump($post_title);


//$AmzProduct = new AmzProduct();
//var_dump($AmzProduct->getProduct());

// Get content from API and create a new post
//$image = $data->getImageByTitle('review : Hypoglycemia');
//var_dump($data->getDetailByText('Hyperglycemia'));

//var_dump(auto_post_from_api());
// Schedule a daily event to auto post from the API
//insertPost();


//var_dump(get_the_ID());

add_action('insertPost', 'insertPost');
function insertPost()
{
	$type = 'Y-m-d H:i:s';
	$sequenceTime = HOUR_IN_SECONDS * 10; //post per hrs.
//	$sequenceTime = 55; //post per hrs.
	$postTime = current_time('timestamp') + $sequenceTime;
	$timeToPost = wp_date($type, $postTime);
//
//	$new_post = array(
//		'post_title' => '$post_title',
//		'post_content' => '$content',
//		'post_author' => 1,
//		'post_status' => 'future',
//		'post_type' => 'post',
//		'post_date' => $timeToPost,
//		'post_date_gmt' => $timeToPost
//	);
//
//
//	//Here is the Magic:
//	kses_remove_filters(); //This Turns off kses
//	$post_id = wp_insert_post($new_post);
//	kses_init_filters(); //This Turns on kses again

	// Set the post content and other parameters
	$post_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
	$post_title = 'My Scheduled Post';
	$post_status = 'future'; // Set the post status to 'future' to schedule it
	$post_date = '2023-03-03 10:00:00'; // Set the date and time for the post to be published

// Set up the post data array
	$post_data = array(
		'post_content' => $post_content,
		'post_title' => $post_title,
		'post_status' => $post_status,
		'post_date' => $timeToPost,
		'post_author' => 1,
	);
	// Insert the post into the database
	$post_id = wp_insert_post($post_data);

	return $post_id;
}


function auto_post_from_api() {
	global $wpdb;
	$data = new ApiData($wpdb);

	// Get content from API and create a new post
	$array = $data->getSeoTitle();//array => title, keyword
	$post_title = $array['title'];
	$keyword = $array['keyword'];
	if(isset($post_title)){
		$content = $data->getLongDescription($post_title, $keyword);
//		$postContent = getTemplate($post_title);

		$new_post = array(
			'post_title' => $post_title,
			'post_content' => $content,
			'post_author' => 1,
			'post_status' => 'publish',
			'post_type' => 'post'
		);

		//Here is the Magic:
		kses_remove_filters(); //This Turns off kses
		$post_id = wp_insert_post($new_post);
		kses_init_filters(); //This Turns on kses again

		/** updated index keyword query */
		if(is_numeric($post_id)){
			$data->setKeywordIndex($array['index']);
		}
	}


}

function getQuestions()
{
	global $wpdb;
	$data = new ApiData($wpdb);

//	$title = $data->getSeoTitle();
	$title = 'dewalt tool saw';
	$questions = $data->getQuestions($title);
	if(count($questions) > 0){
		foreach ($questions as $key => $question){
			$time = ($key * 3600) + time();
			add_action('wp', function() use ( $time ) {
				schedule_daily_event( $time );
			});
		}
	}

}

add_action('wp', 'schedule_daily_event');
add_action('auto_post_from_api', 'auto_post_from_api');

function schedule_daily_event($time) {
	if (!wp_next_scheduled('auto_post_from_api')) {
		wp_schedule_event($time, 'every-1-day', 'auto_post_from_api');
//		wp_schedule_single_event( time() + 300, 'auto_post_from_api' );
	}
}
add_filter( 'cron_schedules', function ( $schedules ) {
	$schedules['every-1-day'] = array(
		'interval' => DAY_IN_SECONDS,
//		'interval' => 2 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every Day' )
	);
	return $schedules;
} );


//CREATE MENU
function chatgpt_content_writer_plugin() {
	add_menu_page(
		__( 'Chat GPT Panel', 'my-textdomain' ),
		__( 'Chat GPT Panel', 'my-textdomain' ),
		'manage_options',
		'chatgpt-content-writer-dashboard',
		'chatgpt_content_writer_dashboard',
		'dashicons-admin-users',
		999
	);
	add_submenu_page(
		'chatgpt-content-writer-dashboard', //parent menu name
		'Settings',
		'Settings',
		'manage_options', //
		'chatgpt-content-writer-settings', //url
		'chatgpt_content_writer_settings' //function
	);

}

//CALL CREATE MENU FUNCTION
add_action( 'admin_menu', 'chatgpt_content_writer_plugin' );


//FUNCTIONS
function chatgpt_content_writer_dashboard() {
	include "dashboard.php";
}
function chatgpt_content_writer_settings() {
	include "settings.php";
}


//CREATE TABLES
function create_table_chatgpt_content_writer() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$table_name = $wpdb->prefix . 'chatgpt_content_writer';

	$sql = "CREATE TABLE " . $table_name . " (
	id int(11) NOT NULL AUTO_INCREMENT,
	api_token tinytext NOT NULL,
	temperature tinytext NOT NULL,
	max_tokens tinytext NOT NULL,
	language tinytext NOT NULL,
	keywords longtext NOT NULL,
	questions longtext NOT NULL,
	template longtext NOT NULL,
	product_template longtext NOT NULL,
	keywords_index int(5) NOT NULL,
	PRIMARY KEY  (id)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}


register_activation_hook(__FILE__, 'create_table_chatgpt_content_writer');




