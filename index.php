<?php
/**
 * Plugin Name: Custom Customer Records Manager
 * Description: A plugin to manage a custom customer records.
 * Version: 1.0
 * Author: Jewelry Store Marketing
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


function create_customer_records() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ct_customer_records';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_no BIGINT(20) NOT NULL,
            retail_locator_status TEXT NOT NULL,
            display TEXT NOT NULL,
            discount_group TEXT NOT NULL,
            ytd_sales TEXT NOT NULL,
            prev_ytd_sales TEXT NOT NULL,
            discount_amount_level TEXT NOT NULL,
            account_balance TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

function create_sell_to_customer() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ct_sell_to_customer';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sell_to_customer_no BIGINT(20) NOT NULL,
            document_type TEXT NOT NULL,
            document_no TEXT NOT NULL,
            amount TEXT NOT NULL,
            remaining_amount TEXT NOT NULL,
            due_date DATE DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

// Ensure tables exist on every load
function ensure_tables_exist() {
    create_customer_records();
    create_sell_to_customer();
}

// Run on activation
register_activation_hook(__FILE__, 'ensure_tables_exist');

// Run on admin initialization (so tables are created if missing)
add_action('admin_init', 'ensure_tables_exist');


require_once plugin_dir_path(__FILE__) . 'core/_common_functions.php';
require_once plugin_dir_path(__FILE__) . 'core/_customer_functions.php';


/* Add this plugin to Tools.php menu */
// Hook to add a menu item under Tools
add_action('admin_menu', 'customer_records_manager_menu');

function customer_records_manager_menu() {
    add_management_page(
        'Customer Records Manager', // Page Title
        'Customer Records Manager', // Menu Title
        'manage_options',           // Capability (Admin only)
        'customer-records-manager', // Menu Slug
        'customer_records_manager_page' // Callback Function
    );
}

// Callback function for the plugin page
function customer_records_manager_page() {
    // Main Panel Section HTML
    require_once plugin_dir_path(__FILE__) . 'includes/_main_panel_section.php';
}

// Include CSS and JS files ................................................................................................
function customer_records_manager_enqueue_assets_styles($hook) {
    if ($hook !== 'tools_page_customer-records-manager') { return; }
    wp_enqueue_style('customer-records-manager-css', plugins_url('assets/css/crm-styles.css', __FILE__), array(), '1.0.0');
    wp_enqueue_script('dashicons');
    wp_enqueue_script('jquery');
    wp_enqueue_script('customer-records-script', plugins_url('assets/js/crm-script.js', __FILE__),  array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'customer_records_manager_enqueue_assets_styles');

// AUTOMATIC FETCH OF DATA BY USING WP CRON JOBS
// Schedule the cron job if it is not already scheduled
function crm_schedule_ftp_cron() {
    if (!wp_next_scheduled('crm_daily_ftp_fetch_event')) {
        wp_schedule_event(time(), 'daily', 'crm_daily_ftp_fetch_event');
    }
}
add_action('wp', 'crm_schedule_ftp_cron');

function crm_fetch_ftp_files_cron() {
    $files = crm_fetch_ftp_files();
    if (!empty($files) && !isset($files['error'])) {
        $result = crm_process_csv_ftp_files($files);
        error_log("FTP Files fetched successfully: " . json_encode($result));
    } else {
        error_log("FTP Fetch Error: " . $result['error']); // Log errors
    }
}
add_action('crm_daily_ftp_fetch_event', 'crm_fetch_ftp_files_cron');

register_activation_hook(__FILE__, 'crm_schedule_ftp_cron');
register_deactivation_hook(__FILE__, 'crm_remove_ftp_cron');

function crm_remove_ftp_cron() {
    wp_clear_scheduled_hook('crm_daily_ftp_fetch_event');
}

if (defined('DOING_CRON') && DOING_CRON) {
    $stats = crm_count_all_data();
    $today_date = date('Y-m-d');

    // Ensure the keys exist before accessing them to avoid warnings
    $ct_date_ctm = !empty($stats['ct_customer_records']['last_created_at']) 
        ? date('Y-m-d', strtotime($stats['ct_customer_records']['last_created_at'])) : null;
    $ct_date_ar_opt = !empty($stats['ct_sell_to_customer']['last_created_at']) 
        ? date('Y-m-d', strtotime($stats['ct_sell_to_customer']['last_created_at'])) : null;

    // Run cron job only if BOTH dates are NOT equal to today
    if ($ct_date_ctm !== $today_date && $ct_date_ar_opt !== $today_date) { 
        do_action('crm_daily_ftp_fetch_event');
    }
}
