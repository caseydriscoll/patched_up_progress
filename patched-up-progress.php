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
		add_action( 'wp_ajax_stop_action', array( $this, 'stop_action' ) );
		add_action( 'save_post_action', array( $this, 'set_current_action' ) );
	}

	function add_action() {
		if ( ! is_user_logged_in() )
			wp_send_json_error( 
				array(
					'success' => false
				)
			);

		if ( get_option( 'current_action' ) !== null )
			$this->stop_action( true );

		$actions = get_option( 'available_actions' );

		if ( ! in_array( $_POST['action_title'], $actions ) )
			array_push( $actions, $_POST['action_title'] );

		update_option( 'available_actions', $actions );
			
		$post_id = wp_insert_post( 
			array( 
				'post_title' => $_POST['action_title'],
				'post_type' => 'action',
				'post_author' => get_current_user_id(),
				'post_content' => $_POST['content']
			)
		);

		if ( isset( $_POST['task'] ) ) {
			$term = get_term_by( 'name', $_POST['task'], 'task' );

			wp_set_post_terms( $post_id, $term->term_id, 'task' );
		}

		wp_send_json_success( 
			array( 
				'success' => true,
				'action' => $_POST['action_title'],
				'task' => $_POST['task']
			)
		);
	}

	function stop_action( $return = false ) {
		if ( ! is_user_logged_in() )
			wp_send_json_error( 
				array(
					'success' => false
				)
			);

		$current_action = get_option( 'current_action' );

		date_default_timezone_set( get_option( 'timezone_string' ) );
		update_post_meta(
			$current_action,
			'end_time',
			date( 'G:i' )
		);

		update_option( 'current_action', '' );

		if ( $return )
			return;
		else
			wp_send_json_success( 
				array( 
					'success' => true,
					'title' => get_the_title( $current_action )
				)
			);
	}

	function set_current_action( $action_id ) {
		if ( isset( $_POST['action_end_time'] ) )
			update_option( 'current_action', null );
		else
			update_option( 'current_action', $action_id );
	}
}

new Patched_Up_Progress();
