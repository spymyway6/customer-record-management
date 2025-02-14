<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_customer_records'])) {
        $allowed_extensions = ['csv', 'xls', 'xlsx'];
        $errors = [];

        $customer_file_uploaded = !empty($_FILES['customer_record_files']['name']);

        // Ensure at least one file is uploaded
        if (!$customer_file_uploaded) {
            $errors[] = "Please upload at least one file: 'Customer Data' or 'AR Open Items'.";
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
                    
                    // Apply Logic for saving data to database
                    echo "<pre>";
                    print_r($customer_data);
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
            echo '<div class="updated"><p>Files uploaded successfully.</p></div>';
        }
    }
?>

<div class="crm-tools-container">
    <div class="crmt-heading">
        <h4 class="crmt-title">File Uploader and FTP tools</h4>
    </div>
    <div class="crm-tools-content">
        <div class="crm-status-badge">
            <span class="cbadge crm-success" title="Done uploaded today"><i class="dashicons dashicons-yes"></i> Customers</span>
            <span class="cbadge crm-danger" title="No Customer Records uploaded today"><i class="dashicons dashicons-no"></i> AR Open Items</span>
        </div>
        <div class="crm-form-content">
            
            <div class="crm-tab-container">
                <div class="crm-tab-selection">
                    <select name="crm_select_table" id="crm_select_table" class="crm-form-control">
                        <option value="file-uploader">File Uploader</option>
                        <option value="ftp-customer-record">FTP: Customer Records</option>
                        <option value="ftp-ar-open-items">FTP: AR Open Items</option>
                    </select>
                </div>
                <div class="crm-tab-content">
                    <div class="crm-warning">
                        <strong>Note:</strong> Please upload 1 file at a time only to avoid server timeout issues.
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="columns">
                            <div class="crm-form-group">
                                <label for="customer_record_files">Select file to upload</label>
                                <input type="file" name="customer_record_files" id="customer_record_files" class="crm-upload-file" accept=".csv,.xls,.xlsx" required>
                                <small>Accepts .csv, .xls, and .xlsx files only</small>
                            </div>
                        </div>

                        <div class="crm-form-group">
                            <?php submit_button('Submit Files', 'primary crm-main-btn', 'upload_customer_records'); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>