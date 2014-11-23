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
}

new Patched_Up_Progress();
