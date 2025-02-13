<?php


// Function to Add a New Row
function add_new_row($name, $value) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';

    $wpdb->insert(
        $table_name,
        [
            'name'  => $name,
            'value' => $value
        ],
        ['%s', '%s']
    );
}

// Function to Update a Row Based on ID
function update_row($id, $new_name, $new_value) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_table';

    $wpdb->update(
        $table_name,
        [
            'name'  => $new_name,
            'value' => $new_value
        ],
        ['id' => $id],
        ['%s', '%s'],
        ['%d']
    );
}

// Example Usage (For Testing)
// Uncomment these to run inside a function or hook
// add_new_row('Test Name', 'Test Value');
// update_row(1, 'Updated Name', 'Updated Value');
