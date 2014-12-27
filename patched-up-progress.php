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

include 'lib/detect-mobile-browsers.php';


class Patched_Up_Progress {
	function __construct() {
		add_action( 'wp_ajax_add_action', array( $this, 'add_action' ) );
		add_action( 'wp_ajax_stop_action', array( $this, 'stop_action' ) );
		add_action( 'wp_ajax_append_log', array( $this, 'append_log' ) );

		add_action( 'save_post_action', array( $this, 'end_current_action' ), 11, 2 );
		add_action( 'save_post_action', array( $this, 'set_current_action' ), 12, 2 );

		add_action( 'admin_init', array( $this, 'register_settings' ) );
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

		 
		$idk_settings = get_option( 'idk-settings' );

		// Do actions
		$actions = explode( ', ', $idk_settings['progress']['available_actions'] );

		if ( $actions == '' ) $actions = array();

		if ( ! in_array( $_POST['action_title'], $actions ) )
			array_push( $actions, $_POST['action_title'] );

		$idk_settings['progress']['available_actions'] = implode( ', ', $actions );

		// Do determiners
		if ( isset( $_POST['determiner'] ) && $_POST['determiner'] != '' ) {
			$determiners = explode( ', ', $idk_settings['progress']['available_determiners'] );

			if ( $determiners == '' ) $determiners = array();

			if ( ! in_array( $_POST['determiner'], $determiners ) )
				array_push( $determiners, $_POST['determiner'] );

			$idk_settings['progress']['available_determiners'] = implode( ', ', $determiners );
		}

		update_option( 'idk-settings', $idk_settings );
			
		$post_id = wp_insert_post( 
			array( 
				'post_title'   => $_POST['action_title'],
				'post_type'    => 'action',
				'post_author'  => get_current_user_id(),
				'post_content' => $_POST['content']
			)
		);

		if ( isset( $_POST['determiner'] ) && $_POST['determiner'] != '' ) {
			update_post_meta( $post_id, 'determiner', $_POST['determiner'] );
		}

		if ( isset( $_POST['task'] ) && $_POST['task'] != '' ) {
			$term = get_term_by( 'name', $_POST['task'], 'task', ARRAY_A );
	
			if ( $term == '' )
				$term = wp_insert_term( $_POST['task'], 'task' );

			wp_set_post_terms( $post_id, $term['term_id'], 'task' );
		}

		wp_send_json_success( 
			array( 
				'success'    => true,
				'action'     => strtolower( $_POST['action_title'] ),
				'determiner' => strtolower( $_POST['determiner'] ),
				'task'       => $_POST['task']
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

		update_post_meta(
			$current_action,
			'end_time',
			current_time( 'Y-m-d H:i:s' )
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

	    $diff = current_time('YW') - current_time('YW', strtotime( get_option( 'idk-settings' )['progress']['birthday'] ) );
	    $post_slug = 'review-for-' . substr( $diff, 0, 1 ) . '-' . substr( $diff, 1, 1 ) . '-' . substr( $diff, 2 );

		$args = array(
		  'name' => $post_slug,
		  'post_type' => 'log',
		  'posts_per_page' => 1
		);

		$today = "\n\n<strong>" . current_time('l') . "</strong>";

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

	function end_current_action( $action_id, $post ) {
		// Only end the current action if you are creating a new one
		//
		// This is attached to 'save_post_action', but we don't want to set the end time everytime
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $post->post_title == 'Auto Draft' ) return;

		// Can't add end_time if no current action
		$current_action = get_option( 'current_action' );
		if ( empty( $current_action ) ) return;

		// Don't add end_time if resaving latest action
		//    of if we are resaving an old action
		if ( $action_id <= $current_action ) return;


		update_post_meta(
			$current_action,
			'end_time',
			current_time( 'Y-m-d H:i:s' )
		);
	}

	/**
	* Whenever an action is saved add the new current action
	* 
	* @action  save_post_action
	*
	**/
	function set_current_action( $action_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $post->post_title == 'Auto Draft' ) return;

		if ( empty( $_POST['action_end_time'] ) )
			update_option( 'current_action', $action_id );
		else
			update_option( 'current_action', null );
	}

	function add_admin_menu() {
		add_menu_page( 'Patched Up Progress', 'Progress', 'manage_options', 
			'progress', array( $this, 'settings_page' ), 'dashicons-awards', get_option( 'idk-settings' )['progress']['admin_menu'] );

		add_submenu_page( 'progress', 'Tasks', 'Tasks', 
			'edit_others_posts', 'edit-tags.php?taxonomy=task');
		add_submenu_page( 'progress', 'Settings', 'Settings', 
			'edit_others_posts', 'progress-settings', array( $this, 'settings_page' ) );
	}

	function settings_page() { ?>
		<div class="wrap">
			<h2>Settings</h2>
			<form method="post" action="options.php">
				<?php 
					settings_fields( 'idk-progress' ); 
					$settings = get_option( 'idk-settings' )['progress'];
				?>

				<table class="form-table">
					<tr valign="top">
				        <th scope="row">Admin Menu Position</th>
				        <td><input type="text" name="idk-settings[progress][admin_menu]" value="<?php echo esc_attr( $settings['admin_menu'] ); ?>" /></td>
		        	</tr>

			        <tr valign="top">
				        <th scope="row">Birthday</th>
				        <td><input type="text" name="idk-settings[progress][birthday]" value="<?php echo esc_attr( $settings['birthday'] ); ?>" /></td>
		        	</tr>

		        	<tr valign="top">
				        <th scope="row">Available actions</th>
				        <td>
				        	<textarea rows="5" name="idk-settings[progress][available_actions]" /><?php echo esc_attr( $settings['available_actions'] ); ?></textarea>
				        </td>
		        	</tr>
		        	<tr valign="top">
				        <th scope="row">Available determiners</th>
				        <td>
				        	<textarea rows="5" name="idk-settings[progress][available_determiners]" /><?php echo esc_attr( $settings['available_determiners'] ); ?></textarea>
				        </td>
		        	</tr>
		        </table>

		        <h3>Widget Defaults</h3>
		        <table class="form-table">
		        	<tr valign="top">
				        <th scope="row">Progress Bar Height</th>
				        <td><input type="text" name="idk-settings[progress][progress_bar_height]" value="<?php echo esc_attr( $settings['progress_bar_height'] ); ?>" /></td>
		        	</tr>

		        	<tr valign="top">
				        <th scope="row">Show "is currently"</th>
				        <td><input type="checkbox" name="idk-settings[progress][currently]" value="1" <?php if ( $settings['currently'] ) echo 'checked'; ?> /></td>
		        	</tr>
	        	</table>
		
				<?php submit_button(); ?>
			</form>
		</div> <?php
	}

	function register_settings() {
		register_setting( 'idk-progress', "idk-settings" );
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
