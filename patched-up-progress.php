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
include 'patched-up-progress-log-cpt.php';

class Patched_Up_Progress {
	function __construct() {
		add_action( 'wp_ajax_add_action', array( $this, 'add_action' ) );
		add_action( 'wp_ajax_stop_action', array( $this, 'stop_action' ) );
		add_action( 'wp_ajax_append_log', array( $this, 'append_log' ) );

		add_action( 'save_post_action', array( $this, 'set_current_action' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );

		add_action( 'init', array( $this, 'register_styles_and_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
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

		if ( $actions == '' ) $actions = array();

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
			$term = get_term_by( 'name', $_POST['task'], 'task', ARRAY_A );
	
			if ( $term == '' )
				$term = wp_insert_term( $_POST['task'], 'task' );

			wp_set_post_terms( $post_id, $term['term_id'], 'task' );
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

	function append_log() {
		if ( ! is_user_logged_in() )
			wp_send_json_error( 
				array(
					'success' => false
				)
			);

	    $diff = date('YW') - date('YW', strtotime( '1986-04-14' ) );
	    $post_slug = 'review-for-' . substr( $diff, 0, -3 ) . '-' . substr( $diff, 1, -2 ) . '-' . substr( $diff, 2 );


		$args = array(
		  'name' => $post_slug,
		  'post_type' => 'log',
		  'posts_per_page' => 1
		);

		$today = "\n\n<strong>" . date('l') . "</strong>";

		$content = "\n\n" . $_POST['content'];

		$log = new WP_Query( $args );		

		if ( $log->have_posts() ) {
		 	while ( $log->have_posts() ) : $log->the_post();

		 	if ( strpos( get_the_content(), $today ) === false )
		 		$content = $today . $content;

		  	wp_update_post(
		  		array(
		  			'ID'           => get_the_ID(),
		  			'post_content' => get_the_content() . $content
		  		)
		  	);

		  endwhile;
		} else {
			$content = $today . $content;

			$post_id = wp_insert_post( 
				array( 
					'post_title'   => 'Review for',
					'post_type'    => 'log',
					'post_author'  => get_current_user_id(),
					'post_content' => $content
				)
			);
		}

		wp_reset_query();

		wp_send_json_success( $post_id );

	}

	function set_current_action( $action_id ) {
		if ( isset( $_POST['action_end_time'] ) )
			update_option( 'current_action', null );
		else
			update_option( 'current_action', $action_id );
	}

	function add_admin_menu() {
		add_menu_page( 'Patched Up Progress', 'Progress', 'manage_options', 
			'progress', array( $this, 'settings_page' ), 'dashicons-awards', 4 );

		add_submenu_page( 'progress', 'Tasks', 'Tasks', 
			'edit_others_posts', 'edit-tags.php?taxonomy=task');
		add_submenu_page( 'progress', 'Settings', 'Settings', 
			'edit_others_posts', 'progress-settings', array( $this, 'settings_page' ) );
	}

	function settings_page() {
		echo '<div class="wrap">';
		echo 	'<h2>Settings</h2>';
		echo '</div>';
	}

	function add_dashboard_widgets() {
		wp_add_dashboard_widget( 'patched_up_progress', 'Progess', function(){
			$instance = array_shift( get_option( 'widget_patched_up_progress' ) );
			the_widget( 'Patched_Up_Progress_Widget', $instance );
		} );
	}

	function register_styles_and_scripts() {
		wp_register_style( 'vex', 
			plugins_url('css/vex.css', __FILE__) );
		wp_register_style( 'vex-wireframe', 
			plugins_url('css/vex-theme-wireframe.css', __FILE__) );

		wp_register_script( 'vex', 
			plugins_url('js/vex.combined.min.js', __FILE__), array( 'jquery' ) );
		wp_register_script( 'log', 
			plugins_url('js/log.js', __FILE__), array( 'jquery' ) );
	}

	function enqueue_styles_and_scripts() {
		wp_enqueue_style( 'vex' );
		wp_enqueue_style( 'vex-wireframe' );

		wp_enqueue_script( 'vex' );
		wp_enqueue_script( 'log' );
	}
}

new Patched_Up_Progress();
