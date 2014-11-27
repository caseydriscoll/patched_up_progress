<?php
/**
 * Plugin Name: Patched Up Progress 
 * Plugin URI: http://patchedupcreative.com/plugins/progress
 * Description: A plugin that tracks goals and accomplishments.
 * Version: 1.0.0-dev
 * Author: Casey Patrick Driscoll 
 * Author URI: http://caseypatrickdriscoll.com
 * License: A short license name. Example: GPL2
 */

include 'patched-up-progress-widget.php';
include 'patched-up-progress-action-cpt.php';

class Patched_Up_Progress {
	function __construct() {
		add_action( 'wp_ajax_add_action', array( $this, 'add_action' ) );
		add_action( 'save_post_action', array( $this, 'set_current_action' ) );
	}

	function add_action() {
		if ( ! is_user_logged_in() )
			wp_send_json_error( 
				array(
					'success' => false
				)
			);
			
		wp_insert_post( 
			array( 
				'post_title' => $_POST['title'],
				'post_type' => 'action',
				'post_author' => get_current_user_id()
			)
		);

		wp_send_json_success( 
			array( 
				'success' => true,
				'title' => $_POST['title']
			)
		);
	}

	function set_current_action( $action_id ) {
		if ( $action_id == get_option( 'current_action' ) ) return;

		date_default_timezone_set( get_option( 'timezone_string' ) );
		$timestamp = date( 'G:i' );

		update_post_meta( get_option( 'current_action' ), 'end_time', $timestamp );

		update_option( 'current_action', $action_id );
	}
}

new Patched_Up_Progress();
