<?php

function add_action_timestamp( $post_id, $post, $update ) {
	if ( $post->post_title == 'Auto Draft' ) return;
	if ( $post->post_status == 'trash' ) return;

	$timestamp = current_time( '-Ymd-His' );

	$post->post_status = 'publish';
	$post->post_name = sanitize_title_with_dashes( $post->post_title . $timestamp );	

	if ( $post->post_date == $post->post_modified ) {
	
		remove_action( 'save_post_action', 'add_action_timestamp' );
	
		wp_update_post( $post );

		add_action( 'save_post_action', 'add_action_timestamp' );
	}	

}
add_action( 'save_post_action', 'add_action_timestamp', 10, 3 );

function action_init() {
	register_post_type( 'action', array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_in_menu'		=> 'progress',
		'show_ui'           => true,
		'supports'          => array( 'title', 'editor' ),
		'has_archive'       => true,
		'query_var'         => true,
		'rewrite'           => true,
		'labels'            => array(
			'name'                => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
			'singular_name'       => __( 'Action', 'YOUR-TEXTDOMAIN' ),
			'all_items'           => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
			'new_item'            => __( 'New Action', 'YOUR-TEXTDOMAIN' ),
			'add_new'             => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'        => __( 'Add New Action', 'YOUR-TEXTDOMAIN' ),
			'edit_item'           => __( 'Edit Action', 'YOUR-TEXTDOMAIN' ),
			'view_item'           => __( 'View Action', 'YOUR-TEXTDOMAIN' ),
			'search_items'        => __( 'Search Actions', 'YOUR-TEXTDOMAIN' ),
			'not_found'           => __( 'No actions found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'  => __( 'No actions found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'   => __( 'Parent action', 'YOUR-TEXTDOMAIN' ),
			'menu_name'           => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
		),
	) );

}
add_action( 'init', 'action_init' );

function init_action_end_time() {
	wp_register_script( 'patchedUpActionMetaScripts', 
		plugins_url('js/action_meta.js', __FILE__), array( 'jquery' ) );
	wp_register_script( 'moment', 
		plugins_url('js/moment.js', __FILE__), array( 'jquery' ) );

	add_meta_box( 'action_end_time_meta_box',
        'Action End Time',
        'display_action_end_time_meta_box',
        'action', 'side', 'low'
    );
}
add_action( 'admin_init', 'init_action_end_time' );

function display_action_end_time_meta_box( $action ) {
	wp_enqueue_script( 'patchedUpActionMetaScripts' );
	wp_enqueue_script( 'moment' );

    $end_time = esc_html( get_post_meta( $action->ID, 'end_time', true ) );
	
	if ( $end_time == '' )	
		echo '<a class="button end-time">Stop</a>';
    ?>
	<input type="text" class="widefat" style="width:auto;" name="action_end_time" value="<?php echo $end_time; ?>" />
		
    <?php
}

function add_action_fields( $action_id, $action ) {
	if ( isset( $_POST['action_end_time'] ) ) {
		update_post_meta( $action_id, 'end_time', $_POST['action_end_time'] );
	}
}
add_action( 'save_post_action', 'add_action_fields', 10, 2 );

function action_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['action'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Action updated. <a target="_blank" href="%s">View action</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'YOUR-TEXTDOMAIN'),
		3 => __('Custom field deleted.', 'YOUR-TEXTDOMAIN'),
		4 => __('Action updated.', 'YOUR-TEXTDOMAIN'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Action restored to revision from %s', 'YOUR-TEXTDOMAIN'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Action published. <a href="%s">View action</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
		7 => __('Action saved.', 'YOUR-TEXTDOMAIN'),
		8 => sprintf( __('Action submitted. <a target="_blank" href="%s">Preview action</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Action scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview action</a>', 'YOUR-TEXTDOMAIN'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Action draft updated. <a target="_blank" href="%s">Preview action</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'action_updated_messages' );

function create_task_custom_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Tasks', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Task', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Tasks', 'text_domain' ),
		'all_items'                  => __( 'All Tasks', 'text_domain' ),
		'parent_item'                => __( 'Parent Task', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Task:', 'text_domain' ),
		'new_item_name'              => __( 'New Task Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Task', 'text_domain' ),
		'edit_item'                  => __( 'Edit Task', 'text_domain' ),
		'update_item'                => __( 'Update Task', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate tasks with commas', 'text_domain' ),
		'search_items'               => __( 'Search tasks', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove tasks', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used tasks', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);

	register_taxonomy( 'task', 'action', $args );

	register_taxonomy_for_object_type( 'task', 'action' );

}

add_action( 'init', 'create_task_custom_taxonomy' );

function add_action_columns( $action_columns ) {
	return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title'),
		'taxonomy-task' => __('Task'),
		'content' => __('Content'),
        'start_time' => __('Start'),
        'end_time' => __('End'),
        'total_time' => __('Total')
    );
}
add_filter( 'manage_action_posts_columns', 'add_action_columns' );

function manage_action_columns( $column_name, $id ) {
    global $wpdb;

    switch ( $column_name ) {
    case 'content':
        the_content( $id );
		break;
	case 'start_time':
		echo get_the_time( 'Y-m-d H:i:s', $id );
		break;
	case 'end_time':
		if ( empty( get_post_meta( $id, 'end_time' )[0] ) )
			echo '<i>current</i>';
		else
			echo get_post_meta( $id, 'end_time' )[0];
		break;
	case 'total_time':
		$start_time = new DateTime( get_the_time( 'Y-m-d H:i:s', $id ) );
		$end_time = new DateTime( get_post_meta( $id, 'end_time' )[0] );


		if ( empty( get_post_meta( $id, 'end_time' )[0] ) )
			$end_time = new DateTime( current_time( 'mysql' ) );

		$diff = date_diff( $start_time, $end_time );
		
		if ( $diff->h > 0 )
			$out = $diff->format( '%h:%I:%s' ) . ' hours';
		else if ( $diff->i > 0 )
			$out = $diff->format( '%i:%s' ) . ' minutes';
		else
			$out = $diff->format( '%s' ) . ' seconds';
		
		echo $out;

		break;
 
    default:
        break;
    } 
}
add_action( 'manage_action_posts_custom_column', 'manage_action_columns', 10, 2 );
