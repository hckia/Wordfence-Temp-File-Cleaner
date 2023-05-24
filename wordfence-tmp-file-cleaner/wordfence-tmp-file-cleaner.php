<?php
/**
 * Plugin Name: Wordfence Temporary File Cleaner
 * Description: Deletes temporary files older than a day from wp-content/wflogs/ directory and logs the deleted files to PHP error logs.
 * Version: 1.1
 * Author: Cyrus Kia
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Schedule the cleanup task
function wf_tmp_file_cleanup_schedule() {
    if (!wp_next_scheduled('wf_tmp_file_cleanup_event')) {
        wp_schedule_event(time(), 'twicedaily', 'wf_tmp_file_cleanup_event');
    }
}
add_action('wp', 'wf_tmp_file_cleanup_schedule');

// Perform the cleanup task
function wf_tmp_file_cleanup_task() {
    $directory = WP_CONTENT_DIR . '/wflogs/';
    $files = glob($directory . '*.tmp.*');
    $deleted_files = [];

    foreach ($files as $file) {
        if (filemtime($file) < strtotime('-1 day')) {
            if (unlink($file)) {
                $deleted_files[] = $file;
            }
        }
    }

    if (!empty($deleted_files)) {
        error_log('Deleted temporary files: ' . implode(', ', $deleted_files));
    }
}
add_action('wf_tmp_file_cleanup_event', 'wf_tmp_file_cleanup_task', 10);

// Schedule the cleanup task upon plugin activation
function wf_tmp_file_cleanup_activate() {
    // Schedule the cleanup task on plugin activation
    wf_tmp_file_cleanup_schedule();

    // Log a message to indicate successful scheduling
    error_log('Wordfence Temporary File Cleaner cron event scheduled upon plugin activation.');
}
register_activation_hook(__FILE__, 'wf_tmp_file_cleanup_activate');



