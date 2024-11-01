<?php
/**
 * Hook Performance Monitor
 * 
 * A WordPress plugin to monitor and log hook execution performance.
 * 
 * @author    Yousaf Qamar <hi@yousafqamar.com>
 * @copyright 2024 Yousaf Qamar
 * @license   GPL v2 or later
 * @version   1.0.0
 * 
 * @github    https://github.com/yousafqamar/wp-performance-monitor
 */
class Hook_Performance_Monitor {
    private $start_times = [];
    private $log_file;
    private $request_start_time;
    private $first_hook_time;
    private $last_hook_time;
    private $request_id;
    
    public function __construct() {
        // Set up log file in wp-content/hook-performance.log
        $this->log_file = WP_CONTENT_DIR . '/hook-performance.log';
        
        // Register our monitoring for all default WordPress hooks
        $this->request_start_time = microtime(true);
        $this->first_hook_time = null;
        $this->last_hook_time = null;
        
        // Generate unique request ID
        $this->request_id = uniqid('req_', true);
        // Register shutdown function to log total time
        add_action('shutdown', [$this, 'log_total_time'], 999999);
        
        $this->monitor_default_hooks();
    }

    private function monitor_default_hooks() {
        // List of common WordPress hooks to monitor
        $hooks = [
            'init',
            'wp_loaded',
            'wp',
            'wp_head',
            'wp_footer',
            'admin_init',
            'admin_menu',
            'admin_head',
            'admin_footer',
            'wp_enqueue_scripts',
            'admin_enqueue_scripts',
            'template_redirect',
            'widgets_init',
            'wp_loaded',
            'parse_request',
            'send_headers',
            'setup_theme',
            'after_setup_theme'
        ];

        foreach ($hooks as $hook) {
            // Add start time recorder at priority -999999
            add_action($hook, [$this, 'start_timer'], -999999, 0);
            
            // Add end time recorder at priority 999999
            add_action($hook, [$this, 'end_timer'], 999999, 0);
        }
    }

    public function start_timer() {
        $current_hook = current_filter();
        $this->start_times[$current_hook] = microtime(true);
        
        // Track first hook execution
        if ($this->first_hook_time === null) {
            $this->first_hook_time = microtime(true);
        }
    }

    private function log_request_start() {
        $log_message = sprintf(
            "\n====== New Request %s ======\n",
            $this->request_id
        );
        
        error_log($log_message, 3, $this->log_file);
    }
    public function end_timer() {
        $current_hook = current_filter();
        
        if (isset($this->start_times[$current_hook])) {
            $end_time = microtime(true);
            $this->last_hook_time = $end_time;
            $execution_time = ($end_time - $this->start_times[$current_hook]) * 1000;
            
            $memory_usage = memory_get_usage(true);
            $memory_peak = memory_get_peak_usage(true);
            
            $new_req = "";
            if('init' == $current_hook){
                $log_message = sprintf(
                    "\n====== New Request %s ======\n",
                    $this->request_id
                );
            }
            
            $log_message = sprintf(
                "%s [%s] Hook: %s | Time: %.2fms | Memory: %.2fMB | Peak Memory: %.2fMB\r\n",
                $new_req,
                date('Y-m-d H:i:s'),
                $current_hook,
                $execution_time,
                $memory_usage / 1024 / 1024,
                $memory_peak / 1024 / 1024
            );

            error_log($log_message, 3, $this->log_file);
            
            unset($this->start_times[$current_hook]);
        }
    }

    public function log_total_time() {
        if ($this->first_hook_time !== null && $this->last_hook_time !== null) {
            $total_hook_time = ($this->last_hook_time - $this->first_hook_time) * 1000;
            $total_request_time = (microtime(true) - $this->request_start_time) * 1000;
            
            $summary_message = sprintf(
                "\n=== Request Summary %s ===\n" .
                "Total Hook Execution Time: %.2fms\n" .
                "Total Request Time: %.2fms\n" .
                "================================\n\n",
                $this->request_id,
                $total_hook_time,
                $total_request_time
            );
            
            error_log($summary_message, 3, $this->log_file);
        }
    }
    public function get_log_file() {
        return $this->log_file;
    }
}

// Initialize the monitor
function init_hook_performance_monitor() {
    // Only run if WP_DEBUG is true
    if (defined('WP_DEBUG') && WP_DEBUG) {
        global $hook_monitor;
        $hook_monitor = new Hook_Performance_Monitor();
    }
}
add_action('plugins_loaded', 'init_hook_performance_monitor', 1);

// Optional: Add admin notice if WP_DEBUG is not enabled
function hook_monitor_debug_notice() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        echo '<div class="notice notice-warning"><p>Hook Performance Monitor requires WP_DEBUG to be enabled in wp-config.php</p></div>';
    }
}
add_action('admin_notices', 'hook_monitor_debug_notice');
