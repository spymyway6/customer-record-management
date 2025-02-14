
<div class="crm-plugin-wrapper wrap">
    <h2 class="crm-panel-heading">Customer Records Manager</h2>
    <div class="crm-widgets">
        <p>Widgets here</p>
    </div>

    <div class="crm-container">
        <div class="crm-table-container">
            <div class="crm-tbl-wrapper">
                <div class="crmt-heading crmt-for-tbl">
                    <h4 class="crmt-title">Customer Record Table</h4>
                    <select name="crm_select_table" id="crm_select_table" class="crm-form-control">
                        <option value="customer-record">Customer Record Table</option>
                        <option value="ar-open-items">AR Open Items</option>
                    </select>
                </div>
                <div class="crm-tbl-content">
                    <table class="crm-table">			
                        <thead>
                            <tr>
                                <th>Customer No.</th>
                                <th>Display</th>
                                <th>Discount Group</th>
                                <th class="price-field">YTD Sales</th>
                                <th class="price-field">Previous YTD Sales ($)</th>
                                <th class="price-field">Discount Amount ($) Level</th>
                                <th class="price-field">Account Balance ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=1; $i<=20; $i++){ ?>
                                <tr>
                                    <th scope="row">1000300</th>
                                    <td>InActive</td>
                                    <td>Keystone + %10</td>
                                    <td class="price-field">334.09</td>
                                    <td class="price-field">0</td>
                                    <td class="price-field">4829.95</td>
                                    <td class="price-field">420.45</td>
                                </tr>
                            <?php }; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- File Uploader and FTP tools -->
        <?php include 'components/_file_uploader_ftp.php'; ?>
    </div>
</div>