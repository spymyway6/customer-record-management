<?php
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