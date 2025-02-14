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