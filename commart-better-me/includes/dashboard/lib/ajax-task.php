<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle add or update task.
 */
add_action('wp_ajax_commart_add_update_task', 'commart_add_update_task');
add_action('wp_ajax_nopriv_commart_add_update_task', 'commart_add_update_task');
function commart_add_update_task(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    $data_str   = wp_unslash( $_POST['form_data'] );
    parse_str( $data_str, $form_data );
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    $current_user = wp_get_current_user();

    // Use the correct form keys based on the task form names.
    $data = array(
        'user_id'         => $current_user->ID,
        'projects_id'     => intval($form_data['betterme-task_projects_id']),
        'tasks_title'     => sanitize_text_field($form_data['betterme-task_title']),
        'tasks_deadline'  => sanitize_text_field($form_data['betterme-task_deadline']),
        'tasks_status'    => 'pending',
        'tasks_created_at'=> current_time('mysql')
    );
    // Use the correct hidden field name for task ID.
    $task_id = intval($form_data['betterme-task_id']);

    if($task_id){
        unset($data['tasks_created_at']);
        $result = $wpdb->update($tasks_table, $data, array('id' => $task_id));
        if(false !== $result){
            $project = $wpdb->get_row(
              $wpdb->prepare("SELECT projects_title FROM {$wpdb->prefix}commart_better_me_projects WHERE id = %d", $data['projects_id'])
            );
            $response = array(
                'id'                => $task_id,
                'projects_id'       => $data['projects_id'],
                'project_title'     => $project ? $project->projects_title : '',
                'tasks_title'       => $data['tasks_title'],
                'tasks_deadline'    => $data['tasks_deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to update task.');
        }
    } else {
        $result = $wpdb->insert($tasks_table, $data);
        if($result){
            $insert_id = $wpdb->insert_id;
            $project = $wpdb->get_row(
              $wpdb->prepare("SELECT projects_title FROM {$wpdb->prefix}commart_better_me_projects WHERE id = %d", $data['projects_id'])
            );
            $response = array(
                'id'                => $insert_id,
                'projects_id'       => $data['projects_id'],
                'project_title'     => $project ? $project->projects_title : '',
                'tasks_title'       => $data['tasks_title'],
                'tasks_deadline'    => $data['tasks_deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to add new task.');
        }
    }
}

/**
 * Handle delete task.
 */
add_action('wp_ajax_commart_delete_task', 'commart_delete_task');
add_action('wp_ajax_nopriv_commart_delete_task', 'commart_delete_task');
function commart_delete_task(){
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    if( empty($_POST['task_id']) ){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $result = $wpdb->delete($tasks_table, array('id' => $task_id));
    if($result){
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete task.');
    }
}

/**
 * Handle start task timer.
 */
add_action('wp_ajax_commart_start_task', 'commart_start_task');
add_action('wp_ajax_nopriv_commart_start_task', 'commart_start_task');
function commart_start_task(){
    global $wpdb;
    if ( empty($_POST['task_id']) ) {
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    $task_id = intval($_POST['task_id']);
    $current_time = current_time('mysql');
    $result = $wpdb->update(
        $tasks_table,
        array(
            'tasks_timer_start'      => $current_time,
            'tasks_status'           => 'in_progress',
            'tasks_container_status' => 'play'
        ),
        array('id' => $task_id)
    );
    if( false !== $result ){
        wp_send_json_success(array(
            'message'                => 'Task timer started',
            'tasks_timer_start'      => $current_time,
            'tasks_container_status' => 'play'
        ));
    } else {
        wp_send_json_error('Failed to start task.');
    }
}

/**
 * Handle pause task timer.
 */
add_action('wp_ajax_commart_pause_task', 'commart_pause_task');
add_action('wp_ajax_nopriv_commart_pause_task', 'commart_pause_task');
function commart_pause_task(){
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    if( empty($_POST['task_id']) ){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $task = $wpdb->get_row(
      $wpdb->prepare("SELECT tasks_timer_start, tasks_elapsed_time FROM $tasks_table WHERE id = %d", $task_id)
    );
    if( !$task || empty($task->tasks_timer_start) ){
        wp_send_json_error('No active timer to pause for this task.');
        wp_die();
    }
    $start_time = strtotime($task->tasks_timer_start);
    $now = current_time('timestamp');
    $diff = $now - $start_time;
    $new_elapsed = intval($task->tasks_elapsed_time) + $diff;
    $result = $wpdb->update(
        $tasks_table,
        array(
            'tasks_elapsed_time'    => $new_elapsed,
            'tasks_timer_start'     => null,
            'tasks_status'          => 'paused',
            'tasks_container_status'=> 'pause'
        ),
        array('id' => $task_id)
    );
    if( false !== $result ){
        wp_send_json_success(array(
            'message'                => 'Task timer paused',
            'elapsed'                => $new_elapsed,
            'tasks_container_status' => 'pause'
        ));
    } else {
        wp_send_json_error('Failed to pause task.');
    }
}

/**
 * Handle stop task timer.
 */
add_action('wp_ajax_commart_stop_task', 'commart_stop_task');
add_action('wp_ajax_nopriv_commart_stop_task', 'commart_stop_task');
function commart_stop_task(){
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    if( empty($_POST['task_id']) ){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $report = isset($_POST['report']) ? sanitize_textarea_field($_POST['report']) : '';
    $task = $wpdb->get_row(
      $wpdb->prepare("SELECT tasks_timer_start, tasks_elapsed_time FROM $tasks_table WHERE id = %d", $task_id)
    );
    if( !$task ){
        wp_send_json_error('Task not found.');
        wp_die();
    }
    $new_elapsed = intval($task->tasks_elapsed_time);
    if( ! empty($task->tasks_timer_start) ){
        $start_time = strtotime($task->tasks_timer_start);
        $now = current_time('timestamp');
        $diff = $now - $start_time;
        $new_elapsed += $diff;
    }
    $result = $wpdb->update(
        $tasks_table,
        array(
            'tasks_elapsed_time'    => $new_elapsed,
            'tasks_timer_start'     => null,
            'tasks_status'          => 'completed',
            'tasks_report'          => $report,
            'tasks_container_status'=> 'completed'
        ),
        array('id' => $task_id)
    );
    if( false !== $result ){
        $hrs = floor($new_elapsed / 3600);
        $mins = floor(($new_elapsed % 3600) / 60);
        $secs = $new_elapsed % 60;
        $formatted = sprintf("%02d:%02d:%02d", $hrs, $mins, $secs);
        wp_send_json_success(array(
            'message'           => 'Task timer stopped',
            'elapsed'           => $new_elapsed,
            'elapsed_formatted' => $formatted,
            'report'            => $report,
            'tasks_container_status'  => 'completed'
        ));
    } else {
        wp_send_json_error('Failed to stop task.');
    }
}

/**
 * Handle update task report.
 */
add_action('wp_ajax_commart_update_task_report', 'commart_update_task_report');
add_action('wp_ajax_nopriv_commart_update_task_report', 'commart_update_task_report');
function commart_update_task_report(){
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    if( empty($_POST['task_id']) ){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    if( ! isset($_POST['report']) ){
        wp_send_json_error('Missing report content.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $report = sanitize_textarea_field($_POST['report']);
    $result = $wpdb->update(
        $tasks_table,
        array('tasks_report' => $report),
        array('id' => $task_id)
    );
    if( false !== $result ){
        wp_send_json_success(array('report' => $report));
    } else {
        wp_send_json_error('Failed to update report.');
    }
}
?>