<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_customer_records'])) {
        $allowed_extensions = ['csv', 'xls', 'xlsx'];
        $errors = [];
        $message = '';

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
                $customer_data = trim_csv_as_array($customer_file_tmp);

                if($customer_data){
                    $has_inserted = process_csv_data($customer_data);
                    $upload_result = crm_save_uploaded_file($_FILES['customer_record_files']);
                    if($has_inserted){
                        echo '<div class="updated"><p>'.$has_inserted.'.Please refresh your page.</p></div>';
                    }else{
                        echo '<div class="error"><ul><li>There was a problem inserting your data, maybe the CSV format headers is invalid or empty. Please check your file and try again.</li></ul></div>';
                    }
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
        }
    }

    // Get saved values
    $ftp_config = get_ftp_config();
?>

<div class="crm-tools-container">
    <div class="crmt-heading">
        <h4 class="crmt-title">File Uploader and FTP tools</h4>
    </div>
    <div class="crm-tools-content">
        <div class="crm-form-content">
            <div class="crm-tab-container">
                <div class="crm-tab-selection">
                    <select name="crm_select_table" id="crm_select_table" class="crm-form-control" onchange="chnageTools(this.value)">
                        <option value="ftp-fetch-record-content">FTP Fetch Record</option>
                        <option value="file-uploader-content">File Uploader</option>
                    </select>
                </div>

                <!-- FTP Fetch Record -->
                <div class="crm-tab-content" id="ftp-fetch-record-content">
                    <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crm_save_ftp_config'])) {
                            update_option('crm_ftp_server', sanitize_text_field($_POST['crm_ftp_server']));
                            update_option('crm_ftp_user', sanitize_text_field($_POST['crm_ftp_user']));
                            update_option('crm_ftp_pass', sanitize_text_field($_POST['crm_ftp_pass']));
                            update_option('crm_ftp_port', sanitize_text_field($_POST['crm_ftp_port']));
                            update_option('crm_ftp_remote_dir', sanitize_text_field($_POST['crm_ftp_remote_dir']));
                            echo '<div class="updated"><p>FTP Details saved successfully. Please refresh your page.</p></div>';
                        }
                    ?>
                    <form method="post">
                        <div class="columns">
                            <div class="crm-form-group">
                                <label for="crm_ftp_server">FTP Server *</label>
                                <input type="text" name="crm_ftp_server" id="crm_ftp_server" class="crm-upload-file crm-form-control" placeholder="https://" value="<?=esc_attr($ftp_config['crm_ftp_server']); ?>" required>
                            </div>
                            <div class="crm-form-group">
                                <label for="crm_ftp_user">FTP User *</label>
                                <input type="text" name="crm_ftp_user" id="crm_ftp_user" class="crm-upload-file crm-form-control" placeholder="FTP User" value="<?=esc_attr($ftp_config['crm_ftp_user']); ?>" required>
                            </div>
                            <div class="crm-form-group">
                                <label for="crm_ftp_pass">FTP User *</label>
                                <input type="password" name="crm_ftp_pass" id="crm_ftp_pass" class="crm-upload-file crm-form-control" placeholder="FTP Password" value="<?=esc_attr($ftp_config['crm_ftp_pass']); ?>" required>
                            </div>
                            <div class="crm-form-group">
                                <label for="crm_ftp_port">FTP Port *</label>
                                <input type="number" name="crm_ftp_port" id="crm_ftp_port" class="crm-upload-file crm-form-control" placeholder="FTP Port" value="<?=esc_attr($ftp_config['crm_ftp_port']); ?>" required>
                            </div>
                            <div class="crm-form-group">
                                <label for="crm_ftp_remote_dir">FTP Remote Directory *</label>
                                <textarea name="crm_ftp_remote_dir" id="crm_ftp_remote_dir" cols="30" rows="5" class="crm-upload-file crm-form-control" placeholder="Enter Remote Directory" required><?=esc_attr($ftp_config['crm_ftp_remote_dir']); ?></textarea>
                            </div>
                        </div>

                        <div class="crm-form-group crm-btn-grp">
                            <?php submit_button('Save Details', 'primary crm-main-btn', 'crm_save_ftp_config'); ?>
                        </div>
                    </form>

                    <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crm_fetch_ftp'])) {
                            $files = crm_fetch_ftp_files();
                            if (!empty($files) && !isset($files['error'])) {
                                $result = crm_process_csv_ftp_files($files);
                            } else {
                                echo '<div class="error"><ul><li>There was a problem fetching your data. FTP details are incorrect or unable to find the files.</li></ul></div>';
                            }
                        }
                    ?>
                    <?php if (!empty(array_filter($ftp_config))) { ?>
                        <form method="post">
                            <div class="crm-form-group crm-btn-grp full-width">
                                <label for="crm_ftp_remote_dir">Or Start fetching files manually.</label>
                                <?php submit_button('Fetch Files Manually', 'warning crm-main-btn', 'crm_fetch_ftp'); ?>
                            </div>
                        </form>
                    <?php } ?>
                </div>
                <!-- Manuel File Upload -->
                <div class="crm-tab-content d-none" id="file-uploader-content">
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