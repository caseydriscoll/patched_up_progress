<?php

add_action( 'widgets_init', function(){
	register_widget( 'Patched_Up_Progress_Widget' );
});

class Patched_Up_Progress_Widget extends WP_Widget {

	public function __construct() {
		wp_register_style( 'patchedUpProgressStyles', 
			plugins_url('css/widget.css', __FILE__) );
		wp_register_style( 'tipped', 
			plugins_url('css/tipped.css', __FILE__) );
		wp_register_style( 'typeahead', 
			plugins_url('css/typeahead.css', __FILE__) );

		wp_register_script( 'patchedUpProgressScripts', 
			plugins_url('js/widget.js', __FILE__), array( 'jquery' ) );
		wp_register_script( 'tipped', 
			plugins_url('js/tipped.js', __FILE__), array( 'jquery' ) );
		wp_register_script( 'typeahead', 
			plugins_url('js/typeahead.js', __FILE__), array( 'jquery' ) );

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
		wp_enqueue_style( 'patchedUpProgressStyles' );
		wp_enqueue_style( 'tipped' );
		wp_enqueue_style( 'typeahead' );

		wp_enqueue_script( 'patchedUpProgressScripts' );
		wp_enqueue_script( 'tipped' );
		wp_enqueue_script( 'typeahead' );

		$data = array(
			'beg_time' => $instance['beg_time'],
			'end_time' => $instance['end_time'],
			'actions'  => get_option( 'available_actions' ),
			'tasks'    => get_terms( 'task', 
							array( 
								'fields' => 'names', 
								'hide_empty' => false 
							) )
		);


		wp_localize_script( 'patchedUpProgressScripts', 'progressWidget', $data );


		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( !empty($title) )
			echo $args['before_title'] . $title . $args['after_title'];

		echo '<div id="patched_up_progress_bar">';

		date_default_timezone_set( get_option( 'timezone_string' ) );
		$today = getdate();
		$query_args = array(
			'post_type'  => 'action',
			'date_query' => array(
				array(
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday'],
				),
			),
			'order' => 'ASC',
		);

		$actions = new WP_Query( $query_args );

		echo '<ul id="patched_up_actions">';
		while ( $actions->have_posts() ) {
			$actions->next_post();

			$id = $actions->post->ID;
			
			$title = get_the_title( $id );
			$beg_time = get_the_time( 'G:i', $id ); 
			$end_time = get_post_meta( $id, 'end_time', true );

			$classes = '';
			if ( $end_time == '' ) $classes = 'current';

			if ( $title == 'Development' ) $classes .= ' one';
			if ( $title == 'Eating' ) $classes .= ' two';

			echo '<li 
					data-time="' . $beg_time . '" data-end="' . $end_time . '" 
					title="' . $title . '" data-tipped-options="position: \'top\'"
					class="' . $classes .'"></li>';
		}
		echo '</ul>';

		wp_reset_postdata();

		echo '<div id="patched_up_progress_current_time">';
		echo 	'<div id="patched_up_progress_add_btn" class="btn">+</div>';
		echo 	'<div id="patched_up_progress_stop_btn" class="btn">-</div>';
		echo 	'<div id="patched_up_progress_close_btn" class="btn">x</div>';
		echo 	'<div id="patched_up_progress_current_time_display"></div>';
		echo '</div>';

		echo '<div id="patched_up_progress_cursor_time">';
		echo 	'<div id="patched_up_progress_cursor_time_display"></div>';
		echo '</div>';

		echo '</div>';

		echo '<input type="text" id="patched_up_progress_action" />';
		echo '<input type="text" id="patched_up_progress_task" />';
		echo '<textarea id="patched_up_progress_content"></textarea>';
		echo '<img class="load" src="/wp-includes/js/thickbox/loadingAnimation.gif" />';
		echo '<div id="patched_up_progress_response"></div>';


		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) { 

		if ( isset($instance) ) extract($instance); ?>

		<h3>Settings</h3>
		<p><?php // Standard Title form ?>
		  <label for="<?php echo $this->get_field_id('title');?>">Title:</label> 
		  <input  type="text"
				  class="widefat"
				  id="<?php echo $this->get_field_id('title'); ?>"
				  name="<?php echo $this->get_field_name('title'); ?>"
				  value="<?php if ( isset($title) ) echo esc_attr($title); ?>" />
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id('beg_time');?>">Beginning Time:</label> 
				<br />
		  <input  type="text"
				  id="<?php echo $this->get_field_id('beg_time'); ?>"
				  name="<?php echo $this->get_field_name('beg_time'); ?>"
				  value="<?php if ( isset($beg_time) ) echo esc_attr($beg_time); ?>" />
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id('end_time');?>">End Time:</label> 
				<br />
		  <input  type="text"
				  id="<?php echo $this->get_field_id('end_time'); ?>"
				  name="<?php echo $this->get_field_name('end_time'); ?>"
				  value="<?php if ( isset($end_time) ) echo esc_attr($end_time); ?>" />
		</p> <?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
   
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['beg_time'] = strip_tags($new_instance['beg_time']);
		$instance['end_time'] = strip_tags($new_instance['end_time']);
	  
		return $instance;
	}
}

new Patched_Up_Progress_Widget(); 
