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

// Activation Hook: Create Table if it doesn’t exist
register_activation_hook(__FILE__, 'create_customer_records');
register_activation_hook(__FILE__, 'create_sell_to_customer');

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
            ytd_sales DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            prev_ytd_sales DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount_amount_level DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            account_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
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
            document_no BIGINT(20) NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            remaining_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            due_date DATE DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
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








?>
