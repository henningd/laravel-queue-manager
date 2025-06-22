<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Manager Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Laravel Queue Manager
    | package. You can customize the behavior and appearance of the queue
    | management dashboard here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the route settings for the queue manager dashboard.
    |
    */
    'route' => [
        'prefix' => 'queue-manager',
        'middleware' => ['web'],
        'name' => 'queue-manager.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    |
    | Configure the dashboard appearance and behavior.
    |
    */
    'dashboard' => [
        'title' => 'Queue Manager',
        'auto_refresh_interval' => 30, // seconds
        'items_per_page' => 20,
        'show_debug_info' => env('APP_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Management
    |--------------------------------------------------------------------------
    |
    | Configure default settings for worker management.
    |
    */
    'workers' => [
        'default_timeout' => 60,
        'default_sleep' => 3,
        'default_tries' => 3,
        'default_memory' => 128,
        'max_processes' => 10,
        'auto_restart_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default settings for queue management.
    |
    */
    'queues' => [
        'default_priority' => 50,
        'max_jobs_per_minute' => 0, // 0 = unlimited
        'default_retry_delay' => 0,
        'seed_default_queues' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings.
    |
    */
    'security' => [
        'require_confirmation_for_destructive_actions' => true,
        'log_all_actions' => true,
        'allowed_ips' => [], // Empty array means all IPs are allowed
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | Configure monitoring and alerting settings.
    |
    */
    'monitoring' => [
        'enable_worker_monitoring' => true,
        'monitoring_interval' => 30, // seconds
        'alert_on_worker_failure' => false,
        'alert_on_queue_overflow' => false,
        'max_queue_size_alert' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance-related settings.
    |
    */
    'performance' => [
        'cache_statistics' => true,
        'cache_duration' => 60, // seconds
        'batch_size_for_operations' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    |
    | Customize the user interface appearance.
    |
    */
    'ui' => [
        'theme' => 'default', // default, dark
        'show_worker_pids' => true,
        'show_job_payloads' => false,
        'truncate_job_names' => 50,
        'show_timestamps' => true,
    ],
];