<?php

function set_log_title( $post_id, $post, $update ) {
    if ( $post->post_title == 'Auto Draft' ) return;
    if ( $post->post_status == 'trash' ) return;

    date_default_timezone_set( get_option( 'timezone_string' ) );
    $timestamp = date( '-Ymd' );

    $then = date('YW', strtotime( '1986-04-14'));
    $diff = date('YW') - $then;
    $timestamp = ' ' . substr( $diff, 0, -3 ) . '.' . substr( $diff, 1, -2 ) . '.' . substr( $diff, 2 );

    $post->post_status = 'publish';
    $post->post_title = $post->post_title . $timestamp;    

    if ( $post->post_date == $post->post_modified ) {
    
        remove_action( 'save_post_log', 'set_log_title' );
    
        wp_update_post( $post );

        add_action( 'save_post_log', 'set_log_title' );
    }   

}
add_action( 'save_post_log', 'set_log_title', 10, 3 );



function add_logs_to_loop( $query ) {
    if ( is_home() && $query->is_main_query() )
        $query->set( 'post_type', array( 'post', 'log') );
    
    return $query;
}
add_filter( 'pre_get_posts', 'add_logs_to_loop' );



function log_init() {
    register_post_type( 'log', array(
        'hierarchical'      => false,
        'public'            => true,
        'show_in_nav_menus' => true,
        'show_in_menu'      => 'progress',
        'show_ui'           => true,
        'supports'          => array( 'title', 'editor' ),
        'has_archive'       => true,
        'query_var'         => true,
        'rewrite'           => true,
        'labels'            => array(
            'name'                => __( 'Logs', 'YOUR-TEXTDOMAIN' ),
            'singular_name'       => __( 'Log', 'YOUR-TEXTDOMAIN' ),
            'all_items'           => __( 'Logs', 'YOUR-TEXTDOMAIN' ),
            'new_item'            => __( 'New Log', 'YOUR-TEXTDOMAIN' ),
            'add_new'             => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
            'add_new_item'        => __( 'Add New Log', 'YOUR-TEXTDOMAIN' ),
            'edit_item'           => __( 'Edit Log', 'YOUR-TEXTDOMAIN' ),
            'view_item'           => __( 'View Log', 'YOUR-TEXTDOMAIN' ),
            'search_items'        => __( 'Search Logs', 'YOUR-TEXTDOMAIN' ),
            'not_found'           => __( 'No logs found', 'YOUR-TEXTDOMAIN' ),
            'not_found_in_trash'  => __( 'No logs found in trash', 'YOUR-TEXTDOMAIN' ),
            'parent_item_colon'   => __( 'Parent log', 'YOUR-TEXTDOMAIN' ),
            'menu_name'           => __( 'Logs', 'YOUR-TEXTDOMAIN' ),
        ),
    ) );

}
add_action( 'init', 'log_init' );


function log_updated_messages( $messages ) {
    global $post;

    $permalink = get_permalink( $post );

    $messages['log'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf( __('Log updated. <a target="_blank" href="%s">View log</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
        2 => __('Custom field updated.', 'YOUR-TEXTDOMAIN'),
        3 => __('Custom field deleted.', 'YOUR-TEXTDOMAIN'),
        4 => __('Log updated.', 'YOUR-TEXTDOMAIN'),
        /* translators: %s: date and time of the revision */
        5 => isset($_GET['revision']) ? sprintf( __('Log restored to revision from %s', 'YOUR-TEXTDOMAIN'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => sprintf( __('Log published. <a href="%s">View log</a>', 'YOUR-TEXTDOMAIN'), esc_url( $permalink ) ),
        7 => __('Log saved.', 'YOUR-TEXTDOMAIN'),
        8 => sprintf( __('Log submitted. <a target="_blank" href="%s">Preview action</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
        9 => sprintf( __('Log scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview log</a>', 'YOUR-TEXTDOMAIN'),
        // translators: Publish box date format, see http://php.net/date
        date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
        10 => sprintf( __('Log draft updated. <a target="_blank" href="%s">Preview lgo</a>', 'YOUR-TEXTDOMAIN'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
    );

    return $messages;
}
add_filter( 'post_updated_messages', 'action_updated_messages' );

function add_log_columns( $log_columns ) {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title'),
        'content' => __('Content'),
        'date' => __('Date'),
    );
}
add_filter( 'manage_log_posts_columns', 'add_log_columns' );

function manage_log_columns( $column_name, $id ) {
    global $wpdb;

    switch ( $column_name ) {
    case 'content':
        the_content( $id );
        break;
 
    default:
        break;
    } 
}
add_action( 'manage_log_posts_custom_column', 'manage_log_columns', 10, 2 );
