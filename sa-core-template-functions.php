<?php
/**
 * Template Functions
 *
 * Template functions for listings
 *
 * @author 		Lukasz Girek
 * @version     1.0
 */

/**
 * Gets a number of posts and displays them as options
 * @param  array $query_args Optional. Overrides defaults.
 * @return array             An array of options that matches the CMB2 options array
 */
function sa_core_get_post_options( $query_args ) {

	$args = wp_parse_args( $query_args, array(
		'post_type'   => 'post',
		'numberposts' => -1,
	) );

	$posts = get_posts( $args );

	$post_options = array();
	$post_options[0] = esc_html__('--Choose page--','sa_core');
	if ( $posts ) {
		foreach ( $posts as $post ) {
          $post_options[ $post->ID ] = $post->post_title;
		}
	}

	return $post_options;
}

/**
 * Gets 5 posts for your_post_type and displays them as options
 * @return array An array of options that matches the CMB2 options array
 */
function sa_core_get_pages_options() {
	return sa_core_get_post_options( array( 'post_type' => 'page' ) );
}

/*
 * Pagination
 */
function sa_number_pagination( $total_pages ) {
	$paged = (isset($_GET['document_paged'])) ? $_GET['document_paged'] : 1;
	
	$big = 999999999; 
	echo paginate_links( array(
		'base'      => add_query_arg('document_paged','%#%'),
		'format' 	=> '?document_paged=%#%',
		'current' 	=> $paged,
		'total' 	=> $total_pages,
		'type' 		=> 'list',
		'prev_next'    => true,
		'prev_text'    => '<i class="sl sl-icon-arrow-left"></i>',
		'next_text'    => '<i class="sl sl-icon-arrow-right"></i>',
		 'add_args'        => false,
		 'add_fragment'    => ''
	) );
}

function sa_count_documents_by_user($post_author=null,$post_status=null) {
	global $wpdb;

	if(empty($post_author))
		return 0;

	if ($post_status == 'upcoming') {
		$count = $wpdb -> get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "user_email_data_new` As doc WHERE doc.`user_id` = '$post_author' AND doc.`invoice_dueDate` > NOW() ORDER BY doc.`invoice_dueDate`" );
	} else {
		$count = $wpdb -> get_var( "SELECT COUNT(*) FROM `" . $wpdb->prefix . "user_email_data_new` As doc WHERE doc.`user_id` = '$post_author' AND doc.`invoice_dueDate` < NOW() ORDER BY doc.`invoice_dueDate`" );
	}
	return $count;
} 

add_action( 'wp_ajax_nopriv_remove_file', 'remove_downloaded_file' );
add_action( 'wp_ajax_remove_file', 'remove_downloaded_file' );
function remove_downloaded_file() {
	if (isset($_POST['file'])) {
		unlink(REMINDER_LOG_SHEET_PATH . basename($_POST['file']));
	}
}

add_action( 'wp_ajax_nopriv_download_documents_reminder_data', 'download_documents_reminder_data' );
add_action( 'wp_ajax_download_documents_reminder_data', 'download_documents_reminder_data' );
function download_documents_reminder_data() {
	global $wpdb;
	$results = $wpdb -> get_results( "SELECT log.*,email_data.* FROM `" . $wpdb->prefix . "document_reminder_log` AS log INNER JOIN `". $wpdb->prefix ."user_email_data_new` AS email_data ON email_data.gsuite_emails_id = log.gsuite_emails_id", "ARRAY_A" );
	$reminder_types = array(
		'1' => 'Email Only',
		'2' => 'SMS Only',
		'3' => 'Email And SMS'
	);
	
	$filename = 'reminder_log-'. time() .'.csv';
	$csv = fopen(REMINDER_LOG_SHEET_PATH . $filename, 'w');
	$sheetHeader = array('Supplier Name', 'Bill Due Date', 'Amount to be Paid', 'Customer First Name', 'Customer Surname', 'Customer Email Address', 'Customer Phone Number', 'Reminder Type', 'Is Sent Email', 'Is Sent SMS');
	fputcsv($csv, $sheetHeader);
	if (!empty($results)) {
		foreach($results as $key => $value) {
			$user_data = get_userdata($value['user_id']);
			$csv_line = array(
				$value['supplier_name'],
				$value['invoice_dueDate'],
				$value['invoice_amountDue'],
				$user_data->first_name,
				$user_data->last_name,
				$user_data->user_email,
				get_user_meta( $value['user_id'], 'mepr_mobile_phone', true ),
				$reminder_types[$value['reminder_type']],
				ucfirst($value['is_sent_email']),
				ucfirst($value['is_sent_sms'])
			);
			fputcsv($csv, $csv_line);
		}
	}
	fclose($csv);
	header("Content-type: text/csv");
	header("Content-disposition: attachment; filename = ". REMINDER_LOG_SHEET_PATH . $filename);
	echo json_encode(array(
		'success' => 1,
		'csvurl' => REMINDER_LOG_SHEET_URL . '/' . $filename
	));
	wp_die();
}