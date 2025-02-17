<?php
    $get_params     = get_all_get_params();
    $current_url    = get_clean_url();
    $selected_table = isset($get_params['selectedTable']) ? sanitize_text_field($get_params['selectedTable']) : '';
    $stats          = crm_count_all_data();
    $today_date     = date('Y-m-d');

    if ($selected_table === 'ar-open-items') {
        $customer_data = crm_fetch_data_lists('ct_sell_to_customer');
        $table_name = 'AR Open Items Table';
    } else {
        $customer_data = crm_fetch_data_lists('ct_customer_records');
        $table_name = 'Customer Record Table';
    }
?>

<div class="crm-plugin-wrapper wrap">
    <h2 class="crm-panel-heading">Customer Records Manager </h2>
    <div class="crm-widgets">
        <?php
            $ct_date_ctm = date('Y-m-d', strtotime($stats['ct_customer_records']['last_created_at']));
            $ct_date_ar_opt = date('Y-m-d', strtotime($stats['ct_sell_to_customer']['last_created_at']));
        ?>
        <ul>
            <li>
                <div class="crm-widg-heading">
                    <i class="dashicons dashicons-groups"></i>
                    <span><?=$stats['ct_customer_records']['count'];?></span>
                </div>
                <p>Total Customer Records</p>
                <small>Last Update: <?=date('M d, Y, h:i A', strtotime($stats['ct_customer_records']['last_created_at']));?></small>
            </li>
            <li>
                <div class="crm-widg-heading">
                    <i class="dashicons dashicons-groups"></i>
                    <span><?=$stats['ct_sell_to_customer']['count'];?></span>
                </div>
                <p>Total AR Open Items</p>
                <small>Last Update: <?=date('M d, Y, h:i A', strtotime($stats['ct_sell_to_customer']['last_created_at']));?></small>
            </li>
            <li class="<?=($today_date==$ct_date_ctm) ? 'widget-success' : 'widget-danger';?>">
                <div class="crm-widg-heading">
                <i class="dashicons dashicons-<?=($today_date==$ct_date_ctm) ? 'yes' : 'no';?>"></i>
                    <span><?=($today_date==$ct_date_ctm) ? 'Uploaded' : 'No Uploads';?> </span>
                </div>
                <p>Customer Records</p>
                <small>Last Upload: <?=date('M d, Y, h:i A', strtotime($stats['ct_customer_records']['last_created_at']));?></small>
            </li>
            <li class="<?=($today_date==$ct_date_ar_opt) ? 'widget-success' : 'widget-danger';?>">
                <div class="crm-widg-heading">
                    <i class="dashicons dashicons-<?=($today_date==$ct_date_ar_opt) ? 'yes' : 'no';?>"></i>
                    <span><?=($today_date==$ct_date_ar_opt) ? 'Uploaded' : 'No Uploads';?></span>
                </div>
                <p>AR Open Items</p>
                <small>Last Upload: <?=date('M d, Y, h:i A', strtotime($stats['ct_sell_to_customer']['last_created_at']));?></small>
            </li>
        </ul>
    </div>

    <div class="crm-container">
        <div class="crm-table-container">
            <div class="crm-tbl-wrapper">
                <div class="crmt-heading crmt-for-tbl">
                    <h4 class="crmt-title"><?=$table_name;?></h4>
                    <div class="crm-field-filter-grp">
                        <input type="search" id="searchInput" placeholder="Search..." class="crm-form-control">
                        <select name="crm_select_table" id="crm_select_table" class="crm-form-control" onchange="changeTable('<?=$current_url;?>?page=customer-records-manager', this.value)">
                            <option value="customer-record" <?=($selected_table =='customer-record') ? 'selected' : '';?>>Customer Record Table</option>
                            <option value="ar-open-items" <?=($selected_table =='ar-open-items') ? 'selected' : '';?>>AR Open Items</option>
                        </select>
                    </div>
                </div>
                <div class="crm-tbl-content">
                    <?php if($selected_table =='ar-open-items'){ ?>
                        <table class="crm-table" id="crmTable">
                            <thead>
                                <tr>
                                    <th>ID No.</th>
                                    <th>Sell-to Customer No.</th>
                                    <th>Document Type</th>
                                    <th>Document No.</th>
                                    <th class="price-field">Amount</th>
                                    <th class="price-field">Remaining Amount</th>
                                    <th class="price-field">Due Date</th>
                                    <th class="price-field">Date Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customer_data)) : ?>
                                    <?php foreach ($customer_data as $cd) : ?>
                                        <tr>
                                            <th scope="row"><?= esc_html($cd['id'] ?? ''); ?></th>
                                            <td><?= esc_html($cd['sell_to_customer_no'] ?? ''); ?></td>
                                            <td><?= esc_html($cd['document_type'] ?? ''); ?></td>
                                            <td class="price-field"><?= esc_html($cd['document_no'] ?? ''); ?></td>
                                            <td class="price-field"><?= esc_html($cd['amount'] ?? '0.00'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['remaining_amount'] ?? '0.00'); ?></td>
                                            <td class="price-field"><?= esc_html(!empty($cd['due_date']) ? date('Y-m-d', strtotime($cd['due_date'])) : ''); ?></td>
                                            <td class="price-field"><?= esc_html(!empty($cd['created_at']) ? date('Y-m-d', strtotime($cd['created_at'])) : ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <th scope="row" colspan="8">No data available</th>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php }else{ ?>
                        <table class="crm-table" id="crmTable">
                            <thead>
                                <tr>
                                    <th>ID No.</th>
                                    <th>Customer No.</th>
                                    <th>Retail Locator Status</th>
                                    <th>Display</th>
                                    <th>Discount Group</th>
                                    <th class="price-field">YTD Sales ($)</th>
                                    <th class="price-field">Previous YTD Sales ($)</th>
                                    <th class="price-field">Discount Amount ($) Level</th>
                                    <th class="price-field">Account Balance ($)</th>
                                    <th class="price-field">Date Uploaded</th>
                                </tr>
                            </thead>
                            <tbody\>
                                <?php if (!empty($customer_data)) : ?>
                                    <?php foreach ($customer_data as $cd) : ?>
                                        <tr>
                                            <th scope="row"><?= esc_html($cd['id'] ?? '-'); ?></th>
                                            <td><?= esc_html($cd['customer_no'] ?? '-'); ?></td>
                                            <td><?= esc_html($cd['retail_locator_status'] ?? '-'); ?></td>
                                            <td><?= esc_html($cd['display'] ?? '-'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['discount_group'] ?? '-'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['ytd_sales'] ?? '0'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['prev_ytd_sales'] ?? '0'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['discount_amount_level'] ?? '0'); ?></td>
                                            <td class="price-field"><?= esc_html($cd['account_balance'] ?? '0'); ?></td>
                                            <td class="price-field"><?= esc_html(isset($cd['created_at']) ? date('Y-m-d', strtotime($cd['created_at'])) : '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><th scope="row" colspan="10">No data available</th></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    <?php } ?>
                </div>
            </div>

        </div>
        <!-- File Uploader and FTP tools -->
        <?php include 'components/_file_uploader_ftp.php'; ?>
    </div>
</div>