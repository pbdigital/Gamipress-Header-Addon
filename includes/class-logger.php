<?php
// File: includes/class-logger.php

if (!defined('WPINC')) {
    die;
}

class Logger {
    /**
     * Singleton instance
     *
     * @var Logger
     */
    private static $instance = null;

    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;

    /**
     * Get the singleton instance
     *
     * @return Logger
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . '../pb_digital.log';
    }

    /**
     * Log a message with timestamp
     *
     * @param string $message The message to log
     */
    public static function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log('[PB Digital Gamipress Header Addon] ' . $message);
        }
    }
}
