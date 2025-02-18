<?php

function get_all_get_params() {
    return $_GET; // Returns an associative array of all GET parameters
}
function get_clean_url() {
    return strtok((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '?');
}
function trim_csv_as_array($file){
    $customer_data = [];

    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle); // Get the first row as headers
        $header_count = count($headers);
        
        while (($row = fgetcsv($handle)) !== false) {
            // Skip completely empty rows
            if (!array_filter($row)) {
                continue;
            }
    
            // Ensure row has exactly the same number of columns as headers
            if (count($row) < $header_count) {
                $row = array_pad($row, $header_count, '');
            } elseif (count($row) > $header_count) {
                $row = array_slice($row, 0, $header_count); 
            }
            $customer_data[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return $customer_data;
}

function get_ftp_config(){
    $crm_ftp_server = get_option('crm_ftp_server', '');
    $crm_ftp_user = get_option('crm_ftp_user', '');
    $crm_ftp_pass = get_option('crm_ftp_pass', '');
    $crm_ftp_port = get_option('crm_ftp_port', '');
    $crm_ftp_remote_dir = get_option('crm_ftp_remote_dir', '');

    return array(
        'crm_ftp_server' => $crm_ftp_server,
        'crm_ftp_user' => $crm_ftp_user,
        'crm_ftp_pass' => $crm_ftp_pass,
        'crm_ftp_port' => $crm_ftp_port,
        'crm_ftp_remote_dir' => $crm_ftp_remote_dir,
    );
}