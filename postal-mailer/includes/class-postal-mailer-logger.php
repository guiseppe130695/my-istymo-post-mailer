<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Logger {
    private static $instance = null;
    private $log_directory;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_directory = $upload_dir['basedir'] . '/postal-mailer-logs';
        
        // Create log directory if it doesn't exist
        if (!file_exists($this->log_directory)) {
            wp_mkdir_p($this->log_directory);
            
            // Create .htaccess to protect log files
            file_put_contents(
                $this->log_directory . '/.htaccess',
                'deny from all'
            );
        }
    }
    
    public function log($message, $type = 'info') {
        $date = current_time('Y-m-d');
        $time = current_time('H:i:s');
        $log_file = $this->log_directory . '/postal-mailer-' . $date . '.log';
        
        $log_message = sprintf(
            "[%s] [%s] %s\n",
            $time,
            strtoupper($type),
            is_array($message) || is_object($message) ? print_r($message, true) : $message
        );
        
        error_log($log_message, 3, $log_file);
    }
    
    public function get_logs($date = null) {
        if (!$date) {
            $date = current_time('Y-m-d');
        }
        
        $log_file = $this->log_directory . '/postal-mailer-' . $date . '.log';
        
        if (file_exists($log_file)) {
            return file_get_contents($log_file);
        }
        
        return false;
    }
    
    public function clear_logs() {
        $files = glob($this->log_directory . '/*.log');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}