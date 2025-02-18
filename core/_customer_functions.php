<?php
// Insert Customer Data
function insert_customer_data($customer_data) {
    global $wpdb;
    if (empty($customer_data)) return "No customer data to insert.";
    $table_name = $wpdb->prefix . "ct_customer_records";

    // Empty table before inserting new data
    $wpdb->query("TRUNCATE TABLE {$table_name}");

    // Prepare bulk insert
    $values = [];
    $placeholders = [];
    foreach ($customer_data as $row) {
        $values = array_merge($values, [
            $row['Customer No.'],
            $row['Retail Locator Status'],
            $row['Display'],
            $row['Discount Group'],
            $row['YTD Sales ($)'],
            $row['Previous YTD Sales ($)'],
            $row['Discount Amount ($) Level'],
            $row['Account Balance ($)'],
        ]);

        $placeholders[] = "(%d, %s, %s, %s, %s, %s, %s, %s)";
    }

    // Final query execution
    if (!empty($values)) {
        $query = "INSERT INTO {$table_name} (customer_no, retail_locator_status, display, discount_group, ytd_sales, prev_ytd_sales, discount_amount_level, account_balance) VALUES " . implode(',', $placeholders);
        $wpdb->query($wpdb->prepare($query, ...$values));
        return "Successfully inserted " . count($customer_data) . " customer records.";
    }

    return false;
}

// Insert AR Open Items Data
function insert_sell_to_customer_data($sell_to_data) {
    global $wpdb;
    if (empty($sell_to_data)) return "No sell-to customer data to insert.";
    $table_name = $wpdb->prefix . "ct_sell_to_customer";

    // Empty table before inserting new data
    $wpdb->query("TRUNCATE TABLE {$table_name}");

    // Prepare bulk insert
    $values = [];
    $placeholders = [];
    foreach ($sell_to_data as $row) {
        $values = array_merge($values, [
            $row['Sell-to Customer No.'],
            $row['Document Type'],
            $row['Document No.'],
            $row['Amount'],
            $row['Remaining Amount'],
            date('Y-m-d', strtotime($row['Due Date'])), // Convert to MySQL date format
        ]);

        $placeholders[] = "(%d, %s, %s, %s, %s, %s)";
    }

    // Final query execution
    if (!empty($values)) {
        $query = "INSERT INTO {$table_name} (sell_to_customer_no, document_type, document_no, amount, remaining_amount, due_date) VALUES " . implode(',', $placeholders);
        $wpdb->query($wpdb->prepare($query, ...$values));
        return "Successfully inserted " . count($sell_to_data) . " AR Open Items records.";
    }

    return false;
}

// Validate the CSV file where to insert
function process_csv_data($csv_data) {
    if (empty($csv_data)) {
        return false;
    }

    // Get the first row headers
    $headers = array_keys($csv_data[0]);

    // Check for "Customer No." and "Retail Locator Status"
    if ($headers[0] === "Customer No." && $headers[1] === "Retail Locator Status") {
        return insert_customer_data($csv_data);
    }

    // Check for "Sell-to Customer No." and "Document Type"
    if ($headers[0] === "Sell-to Customer No." && $headers[1] === "Document Type") {
        return insert_sell_to_customer_data($csv_data);
    }

    return false;;
}

function crm_fetch_data_lists($table_name) {
    global $wpdb;
    
    $full_table_name = $wpdb->prefix . $table_name;
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") != $full_table_name) {
        return []; // Return empty array if table does not exist
    }
    $results = $wpdb->get_results("SELECT * FROM $full_table_name", ARRAY_A);
    return $results;
}

function crm_count_all_data() {
    global $wpdb;

    $tables = ['ct_customer_records', 'ct_sell_to_customer'];
    $data = [];

    foreach ($tables as $table) {
        $full_table_name = $wpdb->prefix . $table; // Add table prefix

        // Get count
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$full_table_name}");

        // Get last created_at date
        $last_created_at = $wpdb->get_var("SELECT MAX(created_at) FROM {$full_table_name}");

        $data[$table] = [
            'count' => $count,
            'last_created_at' => $last_created_at ? $last_created_at : 'No Data'
        ];
    }

    return $data;
}

function crm_save_uploaded_file($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['error' => 'No file uploaded.'];
    }

    // Get WordPress upload directory
    $upload_dir = wp_upload_dir();
    $plugin_upload_folder = $upload_dir['basedir'] . '/customer-records-plugin/';

    // Create folder if not exists
    if (!file_exists($plugin_upload_folder)) {
        wp_mkdir_p($plugin_upload_folder);
    }

    // Get original filename and extension
    $file_name = pathinfo($file['name'], PATHINFO_FILENAME);
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Generate a new filename with timestamp
    $timestamp = date('Ymd_His');
    $new_file_name = sanitize_file_name($file_name . '_' . $timestamp . '.' . $file_ext);

    // Set the full path
    $file_path = $plugin_upload_folder . $new_file_name;

    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => true, 'file_url' => $upload_dir['baseurl'] . '/customer-records-plugin/' . $new_file_name];
    } else {
        return ['error' => 'Failed to save file.'];
    }
}

// FTP Functionalities
function crm_fetch_ftp_files() {
    $ftp_config = get_ftp_config();

    // Validate FTP details
    if (empty(array_filter($ftp_config))) {
        return ['error' => 'FTP details are missing. Please configure them first.'];
    }

    $ftp_server = $ftp_config['crm_ftp_server'];
    $ftp_user = $ftp_config['crm_ftp_user'];
    $ftp_pass = $ftp_config['crm_ftp_pass'];
    $ftp_port = $ftp_config['crm_ftp_port'];
    $remote_dir = $ftp_config['crm_ftp_remote_dir'];
    $file_pattern = '*.csv';

    // WordPress upload directory
    $upload_dir = wp_upload_dir();
    $local_dir = $upload_dir['basedir'] . '/customer-records-plugin/';

    // Ensure local directory exists
    if (!file_exists($local_dir)) {
        wp_mkdir_p($local_dir);
    }

    // Connect to FTP server
    $conn_id = @ftp_connect($ftp_server, $ftp_port);
    if (!$conn_id) {
        return ['error' => 'Failed to connect to FTP server: ' . $ftp_server . ' on port ' . $ftp_port . '.'];
    }

    // Attempt FTP login
    $login_result = @ftp_login($conn_id, $ftp_user, $ftp_pass);
    if (!$login_result) {
        ftp_close($conn_id);
        return ['error' => 'FTP login failed. Please check your username and password.'];
    }

    // Enable passive mode
    ftp_pasv($conn_id, true);

    // Get list of files
    $file_list = @ftp_nlist($conn_id, $remote_dir);
    if (!$file_list) {
        ftp_close($conn_id);
        return ['error' => 'No files found in the FTP directory: ' . $remote_dir . '.'];
    }

    $downloaded_files = [];

    foreach ($file_list as $remote_file) {
        if (fnmatch($file_pattern, basename($remote_file))) {
            $timestamp = date('Ymd_His');
            $local_file = $local_dir . basename($remote_file, ".csv") . "_{$timestamp}.csv";

            if (@ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY)) {
                $downloaded_files[] = $local_file;
            }
        }
    }

    // Close FTP connection
    ftp_close($conn_id);

    return !empty($downloaded_files) ? $downloaded_files : ['error' => 'No matching files were downloaded.'];
}

function crm_process_csv_ftp_files($files) {
    foreach ($files as $file) {
        $customer_data = trim_csv_as_array($file);

        if($customer_data){
            $has_inserted = process_csv_data($customer_data);
            if($has_inserted){
                echo '<div class="updated"><p>'.$has_inserted.'. Please refresh your page.</p></div>';
            }else{
                echo '<div class="error"><ul><li>There was a problem inserting your data, maybe the CSV format headers is invalid, empty or unable to be found from the FTP server you are fetching.</li></ul></div>';
            }
        }
    }
}
