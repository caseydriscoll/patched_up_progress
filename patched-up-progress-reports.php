<?php

class Patched_Up_Progress_Reports {
    function __construct() {
        wp_enqueue_script( 'reports' );

        $this->render_page();
    }

    function render_page() { 
        $data = array(
                    'admin_url' => admin_url( 'admin.php?page=progress-reports' )
                );

        wp_localize_script( 'reports', 'progressReportData', $data );

        if ( isset( $_GET['task'] ) )
            $task_id = $_GET['task'];
        else
            $task_id = 0;

        ?>
        <div class="wrap">
            <h2>Reports</h2>
            <h3>Report by Task</h3> 

            <?php

                $tasks = get_terms( 'task', 
                            array( 
                                'fields' => 'id=>name', 
                                'hide_empty' => false 
                            ) );
            ?>

            <select id="tasks">  <?php

                foreach ( $tasks as $id => $taskname ) {
                    if ( $task_id == $id ) $selected = 'selected';
                    else $selected = null;

                    echo '<option value="' . $id . '" ' . $selected . '>' . $taskname . '</option>';
                }

                ?>
            </select>

            <?php

                if ( $task_id ) :
                    $args = array(
                            'post_type'   => array( 'action' ),
                            'post_status' => array( 'publish' ),                            
                            'nopaging'    => true,
                            'order' => 'ASC',
                            
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'task',
                                    'field'    => 'term_id',
                                    'terms'    => $task_id
                                ),
                            ),
                            
                        );

                    $actions = new WP_Query( $args );

                    if ( $actions->have_posts() ) {

                        $rows = '';

                        $now = new DateTime( current_time( 'mysql' ) );
                        $then = new DateTime( current_time( 'mysql' ) );

                        while ( $actions->have_posts() ) {
                            $actions->next_post();

                            $id = $actions->post->ID;

                            $start_time = new DateTime( get_the_time( 'Y-m-d H:i:s', $id ) );
                            $end_time = new DateTime( get_post_meta( $id, 'end_time' )[0] );


                            if ( empty( get_post_meta( $id, 'end_time' )[0] ) )
                                $end_time = new DateTime( current_time( 'mysql' ) );

                            $diff = date_diff( $start_time, $end_time );

                            if ( $diff->h > 0 )
                                $out = $diff->format( '%h:%I:%S' ) . ' hours';
                            else if ( $diff->i > 0 )
                                $out = $diff->format( '%i:%S' ) . ' minutes';
                            else
                                $out = $diff->format( '%s' ) . ' seconds';

                            $rows .= '<tr>';
                            $rows .=     '<td>' . get_the_title( $id ) . '</td>';
                            $rows .=     '<td>' . get_the_date( '', $id ) . '</td>';
                            $rows .=     '<td>' . $out . '</td>';
                            $rows .= '</tr>';

                            $then->add( $diff );
                        } 

                        $rows .= '<tr>';
                        $rows .=     '<td></td>';
                        $rows .=     '<td>Total:</td>';
                        $rows .=     '<td>' . $now->diff( $then )->format( '%h:%I:%S' ) . '</td>';
                        $rows .= '</tr>';
                    }
                
            ?>

            <table>
                <?php echo $rows; ?>
            </table>

            <?php  ?>

            <?php endif; ?>
        </div> <?php
    }

}