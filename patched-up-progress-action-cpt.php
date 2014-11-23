<?php

function add_action_timestamp( $post_id, $post, $update ) {
	if ( $post->post_title == 'Auto Draft' ) return;
	if ( $post->post_status == 'trash' ) return;

	date_default_timezone_set( get_option( 'timezone_string' ) );
	$timestamp = date( '-Ymd-His' );

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
		'show_ui'           => true,
		'supports'          => array( 'title', 'editor' ),
		'has_archive'       => true,
		'query_var'         => true,
		'rewrite'           => true,
		'labels'            => array(
			'name'                => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
			'singular_name'       => __( 'Action', 'YOUR-TEXTDOMAIN' ),
			'all_items'           => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
			'new_item'            => __( 'New action', 'YOUR-TEXTDOMAIN' ),
			'add_new'             => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'        => __( 'Add New action', 'YOUR-TEXTDOMAIN' ),
			'edit_item'           => __( 'Edit action', 'YOUR-TEXTDOMAIN' ),
			'view_item'           => __( 'View action', 'YOUR-TEXTDOMAIN' ),
			'search_items'        => __( 'Search actions', 'YOUR-TEXTDOMAIN' ),
			'not_found'           => __( 'No actions found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'  => __( 'No actions found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'   => __( 'Parent action', 'YOUR-TEXTDOMAIN' ),
			'menu_name'           => __( 'Actions', 'YOUR-TEXTDOMAIN' ),
		),
	) );

}
add_action( 'init', 'action_init' );

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
