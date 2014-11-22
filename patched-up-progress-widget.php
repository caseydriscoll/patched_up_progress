<?php

add_action( 'widgets_init', function(){
	register_widget( 'Patched_Up_Progress_Widget' );
});

class Patched_Up_Progress_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'patched_up_progress',
			'Patched Up Progress'
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		wp_register_style( 'patchedUpProgressStyles', plugins_url('css/widget.css', __FILE__) );
		wp_enqueue_style( 'patchedUpProgressStyles' );

		wp_enqueue_script( 'patchedUpProgressScripts', plugins_url('js/widget.js', __FILE__), array( 'jquery' ) );


		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( !empty($title) )
			echo $args['before_title'] . $title . $args['after_title'];

		echo '<div id="patched_up_progress_bar">';
	
		date_default_timezone_set( get_option('timezone_string') );
		$time = date( 'g:i' );

		echo '<div id="patched_up_progress_current_time">';
		echo 	'<div id="patched_up_progress_current_time_display">' . $time . '</div>';
		echo '</div>';

		echo '<div id="patched_up_progress_cursor_time">';
		echo 	'<div id="patched_up_progress_cursor_time_display"></div>';
		echo '</div>';

		echo '</div>';


		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

new Patched_Up_Progress_Widget(); 
