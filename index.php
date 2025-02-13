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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_customer_records'])) {
        $allowed_extensions = ['csv', 'xls', 'xlsx'];
        $errors = [];

        $customer_file_uploaded = !empty($_FILES['customer_record_files']['name']);
        $sell_to_file_uploaded = !empty($_FILES['sell_to_customer_file']['name']);

        // Ensure at least one file is uploaded
        if (!$customer_file_uploaded && !$sell_to_file_uploaded) {
            $errors[] = "Please upload at least one file: 'Customer Data' or 'AR Open Items'.";
        }

        // Validate 'customer_record_files' if uploaded
        if ($customer_file_uploaded) {
            $customer_file_ext = pathinfo($_FILES['customer_record_files']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($customer_file_ext), $allowed_extensions)) {
                $errors[] = "Invalid file type for 'Customer Data'. Only CSV, XLS, and XLSX files are allowed.";
            }
        }

        if ($customer_file_uploaded) {
            $customer_file_ext = pathinfo($_FILES['customer_record_files']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($customer_file_ext), $allowed_extensions)) {
                $errors[] = "Invalid file type for 'Customer Data'. Only CSV, XLS, and XLSX files are allowed.";
            }else{
                $customer_file_tmp = $_FILES['customer_record_files']['tmp_name'];
                $customer_data = [];

                if (($handle = fopen($customer_file_tmp, 'r')) !== false) {
                    $headers = fgetcsv($handle); // Get the first row as headers
                    $header_count = count($headers);
                    
                    while (($row = fgetcsv($handle)) !== false) {
                        // Skip completely empty rows
                        if (!array_filter($row)) {
                            continue;
                        }
                
                        // Ensure row has exactly the same number of columns as headers
                        if (count($row) < $header_count) {
                            $row = array_pad($row, $header_count, ''); // Fill missing columns with empty string
                        } elseif (count($row) > $header_count) {
                            $row = array_slice($row, 0, $header_count); // Trim excess columns
                        }
                        $customer_data[] = array_combine($headers, $row);
                    }
                    fclose($handle);
                }

                if($customer_data){
                    echo "<pre>";
                    print_r($customer_data);
                    echo "</pre>";
                }
            }
        }

        // Validate 'sell_to_customer_file' if uploaded
        if ($sell_to_file_uploaded) {
            $sell_to_file_ext = pathinfo($_FILES['sell_to_customer_file']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($sell_to_file_ext), $allowed_extensions)) {
                $errors[] = "Invalid file type for 'AR Open Items'. Only CSV, XLS, and XLSX files are allowed.";
            }else{
                $sell_to_file_tmp = $_FILES['sell_to_customer_file']['tmp_name'];
                $sell_to_data = [];

                if (($handle = fopen($sell_to_file_tmp, 'r')) !== false) {
                    $headers = fgetcsv($handle); // Get the first row as headers
                    while (($row = fgetcsv($handle)) !== false) {
                        // Remove empty values and check if the row is completely empty
                        if (!array_filter($row)) {
                            continue; // Skip empty row
                        }
                        $sell_to_data[] = array_combine($headers, $row);
                    }
                    fclose($handle);
                }

                if($sell_to_data){
                    echo "<pre>";
                    print_r($sell_to_data);
                    echo "</pre>";
                }
            }
        }

        // Display errors if any
        if (!empty($errors)) {
            $err_list = '';
            foreach ($errors as $error) {
                $err_list .= "<li>$error</li>";
            }
            echo '<div class="error"><ul>'.$err_list.'</ul></div>';
        } else {
            // Apply Logic here
            echo '<div class="updated"><p>Files uploaded successfully.</p></div>';
        }
    } 
    ?>
        <div class="crm-plugin-wrapper wrap">
            <h1>
                <span>Custom Records Manager</span>
                <small>Upload a .csv file for your "AR Open Items" and "Customer Records". It will be saved inside the database of your website.</small>
            </h1>
            <form method="post" enctype="multipart/form-data">
                <div class="crm-2-columns">
                    <div class="crm-form-group">
                        <label for="customer_record_files">Upload Customer Data:</label>
                        <input type="file" name="customer_record_files" id="customer_record_files" class="crm-form-control" accept=".csv,.xls,.xlsx">
                        <small>Upload your Web-Customer.csv file</small>
                    </div>
                    <div class="crm-form-group">
                        <label for="sell_to_customer_file">Upload AR Open Items:</label>
                        <input type="file" name="sell_to_customer_file" id="sell_to_customer_file" class="crm-form-control" accept=".csv,.xls,.xlsx">
                        <small>Upload your Web-AROpenItems.csv file</small>
                    </div>
                </div>

                <div class="crm-form-group">
                    <?php submit_button('Submit Files', 'primary woo-gold-btn', 'upload_customer_records'); ?>
                </div>
            </form>
        </div>
    <?php
}

// Include CSS file ................................................................................................
function customer_records_manager_enqueue_styles($hook) {
    if ($hook !== 'tools_page_customer-records-manager') {
        return;
    }
    wp_enqueue_style('customer-records-manager-css', plugins_url('assets/styles.css', __FILE__), array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'customer_records_manager_enqueue_styles');








?>
