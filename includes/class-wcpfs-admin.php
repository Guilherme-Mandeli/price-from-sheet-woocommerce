<?php
/**
 * Plugin admin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCPFS_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_wcpfs_import_prices', array($this, 'handle_import_ajax'));
        add_action('wp_ajax_wcpfs_download_template', array($this, 'handle_download_template'));
        add_action('wp_ajax_wcpfs_download_template_excel', array($this, 'handle_download_template_excel'));
    }
    
    /**
     * Adds menu in admin
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Price From Sheet', 'price-from-sheet-woocommerce'),
            __('Price From Sheet', 'price-from-sheet-woocommerce'),
            'manage_woocommerce',
            'wcpfs-import',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">            
            <!-- Import Section - Fixed -->
            <div class="wcpfs-import-section">
                <div class="wcpfs-import-card">
                    <h2><?php _e('Import Prices', 'price-from-sheet-woocommerce'); ?></h2>
                    <form id="wcpfs-import-form" method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field('wcpfs_import', 'wcpfs_nonce'); ?>
                        
                        <div class="wcpfs-form-row">
                            <div class="wcpfs-form-col">
                                <label for="wcpfs_file"><?php _e('Spreadsheet File', 'price-from-sheet-woocommerce'); ?></label>
                                <input type="file" id="wcpfs_file" name="wcpfs_file" accept=".csv,.xlsx,.xls" required>
                                <small><?php _e('Accepted formats: CSV (.csv), Excel (.xlsx, .xls) - Maximum: 10MB', 'price-from-sheet-woocommerce'); ?></small>
                            </div>
                            <div class="wcpfs-form-col">
                                <label for="wcpfs_update_mode"><?php _e('Update Mode', 'price-from-sheet-woocommerce'); ?></label>
                                <select id="wcpfs_update_mode" name="wcpfs_update_mode">
                                    <option value="update"><?php _e('Update existing prices', 'price-from-sheet-woocommerce'); ?></option>
                                    <option value="add"><?php _e('Only add new prices', 'price-from-sheet-woocommerce'); ?></option>
                                </select>
                            </div>
                            <div class="wcpfs-form-col wcpfs-submit-col">
                                <input type="submit" class="button-primary wcpfs-import-btn" value="<?php _e('Import Now', 'price-from-sheet-woocommerce'); ?>" disabled="true">
                            </div>
                        </div>
                    </form>
                    
                    <div id="wcpfs-import-results" style="display: none;">
                        <button type="button" class="wcpfs-close-btn" aria-label="Close">&times;</button>
                        <h3><?php _e('Import Results', 'price-from-sheet-woocommerce'); ?></h3>
                        <div id="wcpfs-results-content"></div>
                    </div>
                </div>
            </div>
            
            <!-- User Guide -->
            <div class="wcpfs-guide-container">
                <div class="wcpfs-guide-header">
                    <div class="wcpfs-guide-header_content-wrapper">
                        <h1 style="display: inline-block;"><?php echo esc_html(get_admin_page_title()); ?></h1>
                        <span style="display: inline-block; position: relative; top: -2px; margin-inline: 5px 3px;">|</span>
                        <h2 style="display: inline-block;"><?php _e('Complete Guide', 'price-from-sheet-woocommerce'); ?></h2>
                    </div>
                    <p style="margin-top: 4px;"><?php _e('Learn how to update your product prices in bulk.', 'price-from-sheet-woocommerce'); ?></p>
                </div>
                
                <div class="wcpfs-guide-content">
                    <!-- O que o Plugin Faz -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('What does the plugin do?', 'price-from-sheet-woocommerce'); ?></h3>
                        <p><?php _e('This plugin allows you to update prices for hundreds or thousands of WooCommerce products at once, using CSV or Excel files. Instead of changing products one by one manually, you can do everything in just a few clicks!', 'price-from-sheet-woocommerce'); ?></p>
                        
                        <h4><?php _e('Update modes:', 'price-from-sheet-woocommerce'); ?></h4>
                        <ul>
                            <li><?php _e('<strong>Update existing prices:</strong> will set the new value for all products listed in the file.', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('<strong>Only add new prices:</strong> will set the new value only for products without a defined price that are listed in the file.', 'price-from-sheet-woocommerce'); ?></li>
                        </ul>
                    </div>
                    
                    <!-- Preparando a Planilha -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Preparing your spreadsheet', 'price-from-sheet-woocommerce'); ?></h3>
                        <!-- Botão para baixar modelo CSV -->
                        <div class="wcpfs-template-download" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                            <a href="<?php echo admin_url('admin-ajax.php?action=wcpfs_download_template&nonce=' . wp_create_nonce('wcpfs_template_nonce')); ?>" class="button button-secondary" style="margin-right: 5px;">
                                <span class="dashicons dashicons-download" style="margin-right: 2px; padding-top: 5px"></span>
                                <?php _e('Download Template | CSV', 'price-from-sheet-woocommerce'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin-ajax.php?action=wcpfs_download_template_excel&nonce=' . wp_create_nonce('wcpfs_template_nonce')); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-download" style="margin-right: 2px; padding-top: 5px"></span>
                                <?php _e('Download Template | Excel', 'price-from-sheet-woocommerce'); ?>
                            </a>
                            <small style="display: block; margin-top: 5px; color: #666;">
                                <?php _e('Download a template file with the correct structure for import.', 'price-from-sheet-woocommerce'); ?>
                            </small>
                        </div>
                        <div class="wcpfs-format-example">
                            <h4><?php _e('Required Format:', 'price-from-sheet-woocommerce'); ?></h4>
                            <div class="wcpfs-table-example">
                                <table>
                                    <thead>
                                        <tr><th>sku</th><th>price</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>PROD-001</td><td>29.90</td></tr>
                                        <tr><td>PROD-002</td><td>15.50</td></tr>
                                        <tr><td>PROD-003</td><td>89.99</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="wcpfs-format-example">
                            <h4><?php _e('Complete Format (with sale price):', 'price-from-sheet-woocommerce'); ?></h4>
                            <div class="wcpfs-table-example">
                                <table>
                                    <thead>
                                        <tr><th>sku</th><th>price</th><th>sale_price</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>PROD-001</td><td>29.90</td><td>24.90</td></tr>
                                        <tr><td>PROD-002</td><td>15.50</td><td></td></tr>
                                        <tr><td>PROD-003</td><td>89.99</td><td>79.99</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="wcpfs-rules">
                            <h4 style="margin-top: 4px;"><?php _e('Important Rules:', 'price-from-sheet-woocommerce'); ?></h4>
                            <ul>
                                <li><?php _e('<strong>SKU:</strong> Must be exactly the same as registered in WooCommerce', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Prices:</strong> Use dot (.) as decimal separator (e.g.: 29.90)', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>File:</strong> Save as CSV (comma separated) or Excel file respecting column names in the first row', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Encoding:</strong> UTF-8 to avoid accent problems', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- How to use -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('How to use', 'price-from-sheet-woocommerce'); ?></h3>
                        <div class="wcpfs-steps">
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">1</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Choose the File', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Click on "Choose file" and select your prepared CSV or Excel file.', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">2</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Select Mode', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Choose whether you want to "Update existing prices" or "Only add new prices".', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">3</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Run the Import', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Click on "Import Now" and wait for the process.', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Solucionando Problemas -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Troubleshooting common issues', 'price-from-sheet-woocommerce'); ?></h3>
                        
                        <div class="wcpfs-problem">
                            <h4><?php _e('"Product with SKU not found"', 'price-from-sheet-woocommerce'); ?></h4>
                            <p><strong><?php _e('Cause:', 'price-from-sheet-woocommerce'); ?></strong> <?php _e('The SKU in the spreadsheet does not exist in WooCommerce', 'price-from-sheet-woocommerce'); ?></p>
                            <p><strong><?php _e('Solution:', 'price-from-sheet-woocommerce'); ?></strong></p>
                            <ul>
                                <li><?php _e('Check if the SKU is correct (no extra spaces)', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Confirm that the product exists in WooCommerce', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Make sure the SKU is not empty', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="wcpfs-problem">
                            <h4><?php _e('"Invalid row: SKU or price not found"', 'price-from-sheet-woocommerce'); ?></h4>
                            <p><strong><?php _e('Cause:', 'price-from-sheet-woocommerce'); ?></strong> <?php _e('Spreadsheet row is incomplete', 'price-from-sheet-woocommerce'); ?></p>
                            <p><strong><?php _e('Solution:', 'price-from-sheet-woocommerce'); ?></strong></p>
                            <ul>
                                <li><?php _e('Ensure all rows have SKU and price', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Remove empty rows from the spreadsheet', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Confirm the headers are correct', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Important Tips -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Important Tips', 'price-from-sheet-woocommerce'); ?></h3>
                        
                        <div class="wcpfs-tips">
                            <div class="wcpfs-tip wcpfs-tip-success">
                                <h4><?php _e('Best Practices', 'price-from-sheet-woocommerce'); ?></h4>
                                <ul>
                                    <li><?php _e('<strong>Backup:</strong> Always make a backup before importing', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Small test:</strong> Start with a few products to test', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Check SKUs:</strong> Confirm if the SKUs are correct', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Use UTF-8:</strong> To avoid accent problems', 'price-from-sheet-woocommerce'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="wcpfs-tip wcpfs-tip-warning">
                                <h4><?php _e('Cautions', 'price-from-sheet-woocommerce'); ?></h4>
                                <ul>
                                    <li><?php _e('<strong>Zero prices:</strong> Be careful not to put zero prices', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Comma in prices:</strong> Use dot (29.90) not comma (29,90)', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Duplicate SKUs:</strong> Each SKU must be unique', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Variable products:</strong> Use the unique SKU of the variation product', 'price-from-sheet-woocommerce'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Practical Example -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Practical Example', 'price-from-sheet-woocommerce'); ?></h3>
                        <div class="wcpfs-example">
                            <h4><?php _e('Scenario: 10% price increase on 500 products', 'price-from-sheet-woocommerce'); ?></h4>
                            <ol>
                                <li><?php _e('<strong>Export current products:</strong> Use WooCommerce > Products > Export', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Calculate new prices:</strong> Open in Excel/Google Sheets and create formula to increase 10%', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Import new prices:</strong> Save as CSV or Excel file with recommended columns and formats in "<i>Preparing your sheet</i>" and import using this plugin', 'price-from-sheet-woocommerce'); ?></li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Support -->
                    <div class="wcpfs-guide-section wcpfs-support-section">
                        <h3><?php _e('Need Help?', 'price-from-sheet-woocommerce'); ?></h3>
                        <p><?php _e('If you encounter problems:', 'price-from-sheet-woocommerce'); ?></p>
                        <ul>
                            <li><?php _e('1. Check the logs in the results section above', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('2. Test with a few products first', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('3. Confirm the file format is correct', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('4. Contact: <strong>hooma.com.br</strong>', 'price-from-sheet-woocommerce'); ?></li>
                        </ul>
                        <hr style=" margin-bottom: 19px; margin-top: 20px; opacity: .44; border-bottom-width: 0; ">
                        <a href="https://hooma.com.br/" target="_blank" style="box-shadow: none !important; outline: none !important; text-decoration: none !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 412 136" width="160" height="52.81" style="enable-background:new 0 0 412 136" xml:space="preserve"><style>.st0{fill:#fff}</style><g id="OBJECTS"><path class="st0" d="M230.84 66.75c8.29 0 15.01 6.72 15.01 15 0 8.28-6.72 15-15.01 15s-15.01-6.72-15.01-15c-.01-8.28 6.71-15 15.01-15m0-15c-16.56 0-30.03 13.46-30.03 30s13.47 30 30.03 30 30.03-13.46 30.03-30-13.48-30-30.03-30zM160.77 66.75c8.29 0 15.01 6.72 15.01 15 0 8.28-6.72 15-15.01 15s-15.01-6.72-15.01-15c0-8.28 6.72-15 15.01-15m0-15c-16.56 0-30.03 13.46-30.03 30s13.47 30 30.03 30 30.03-13.46 30.03-30-13.47-30-30.03-30zM380.97 66.75c8.29 0 15.01 6.72 15.01 15 0 8.28-6.72 15-15.01 15s-15.01-6.72-15.01-15c0-8.28 6.72-15 15.01-15m0-15c-16.56 0-30.03 13.46-30.03 30s13.47 30 30.03 30S411 98.29 411 81.75s-13.47-30-30.03-30zM340.94 71.01v33c0 3.96-2.97 7.42-6.93 7.72-1.85.14-3.58-.39-4.95-1.4a7.435 7.435 0 0 1-3.13-6.08v-31c0-1.8-.73-3.42-1.9-4.6a6.433 6.433 0 0 0-4.12-1.88c-.16-.01-.32-.02-.48-.02-3.59 0-6.51 2.91-6.51 6.5v31.5c0 .07 0 .15-.01.22a6.973 6.973 0 0 1-2.57 5.21c-1.2.98-2.74 1.57-4.42 1.57-1.42 0-2.73-.42-3.83-1.14a6.994 6.994 0 0 1-3.17-5.86v-31.5c0-1.8-.73-3.42-1.9-4.6a6.522 6.522 0 0 0-3.17-1.74c-.4-.1-.82-.15-1.24-.16-3.68-.1-6.69 3.03-6.69 6.7v30.79a7.4 7.4 0 0 1-2.53 5.59 7.344 7.344 0 0 1-5.06 1.91c-4.16-.05-7.42-3.63-7.42-7.78V59.54c0-4.16 3.26-7.74 7.42-7.78 1.95-.02 3.72.69 5.06 1.91a29.69 29.69 0 0 1 10.54-1.91c2.83 0 5.58.39 8.18 1.14 1.67.46 3.29 1.08 4.83 1.82 1.1-.53 2.24-1 3.42-1.39 3.01-1.02 6.24-1.57 9.59-1.57 2.06 0 4.06.21 6.01.6.06.01.12.02.17.04 8.69 1.82 14.81 9.71 14.81 18.61z"/><path class="st0" d="M403.5 111.75h-.01c-4.14 0-7.5-3.36-7.5-7.5v-45c0-4.14 3.36-7.5 7.5-7.5h.01c4.14 0 7.5 3.36 7.5 7.5v45c0 4.14-3.36 7.5-7.5 7.5zM130.34 39.89C126.28 17.49 106.67.5 83.08.5H49.04C22.51.5 1 21.99 1 48.5v39c0 26.51 21.51 48 48.04 48h34.03c14.16 0 26.88-6.12 35.67-15.86 4.17-4.62 5.11-11.39 2.19-16.89a44.761 44.761 0 0 1-5.21-21c0-11.25 4.14-21.53 10.97-29.42 2.99-3.43 4.46-7.97 3.65-12.44zM52.55 55.16c0 1.98-.91 3.89-2.53 5.03-4.52 3.16-7.48 8.39-7.48 14.31s2.96 11.15 7.48 14.31c1.62 1.14 2.53 3.04 2.53 5.03V104c0 5.17-4.36 9.32-9.61 8.98-4.79-.31-8.41-4.5-8.41-9.29V32c0-5.17 4.36-9.32 9.61-8.98 4.79.31 8.41 4.5 8.41 9.29v22.85zM97.59 97.5c0 4.22-3.49 7.62-7.74 7.5-4.11-.12-7.28-3.7-7.28-7.81V88c0-3.31-2.69-6-6.01-6H60.05c-4.22 0-7.63-3.48-7.5-7.73.12-4.1 3.71-7.27 7.82-7.27h16.2c3.32 0 6.01-2.69 6.01-6v-8.19c0-4.11 3.17-7.69 7.28-7.81a7.507 7.507 0 0 1 7.74 7.5v45z"/></g></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handles AJAX import requests
     */
    public function handle_import_ajax() {
        check_ajax_referer('wcpfs_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to perform this action.', 'price-from-sheet-woocommerce'));
        }
        
        $importer = new WCPFS_Importer();
        $result = $importer->import_from_file($_FILES['wcpfs_file'], $_POST['wcpfs_update_mode']);
        
        wp_send_json($result);
    }

    /**
     * Handles CSV template download
     */
    public function handle_download_template() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'wcpfs_template_nonce')) {
            wp_die(__('Access denied.', 'price-from-sheet-woocommerce'));
        }
        
        // Verify permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to perform this action.', 'price-from-sheet-woocommerce'));
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="price-import-template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, array('sku', 'price', 'sale_price'), ',');
        
        // Sample data
        fputcsv($output, array('EXEMPLO-001', '29.90', '24.90'), ',');
        fputcsv($output, array('EXEMPLO-002', '15.50', ''), ',');
        fputcsv($output, array('EXEMPLO-003', '89.99', '79.99'), ',');
        
        fclose($output);
        exit;
    }

    public function handle_download_template_excel() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'wcpfs_template_nonce')) {
            wp_die(__('Access denied.', 'price-from-sheet-woocommerce'));
        }
        
        // Verify permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to perform this action.', 'price-from-sheet-woocommerce'));
        }
        
        try {
            // Create a simple Excel file using XML
            $filename = 'price-import-template.xlsx';
            
            // Define headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Create a basic Excel file using XML structure
            $this->create_simple_excel();
            
            exit;
            
        } catch (Exception $e) {
            // If it fails, redirect to CSV
            wp_redirect(admin_url('admin-ajax.php?action=wcpfs_download_template&nonce=' . wp_create_nonce('wcpfs_template_nonce')));
            exit;
        }
    }

    /**
     * Creates a simple Excel file without external dependencies
     */
    private function create_simple_excel() {
        // Data for Excel
        $data = [
            ['sku', 'price', 'sale_price'],
            ['SAMPLE-001', '29.90', '24.90'],
            ['SAMPLE-002', '15.50', ''],
            ['SAMPLE-003', '89.99', '79.99']
        ];
        
        // Create a temporary file
        $temp_file = tempnam(sys_get_temp_dir(), 'wcpfs_excel_');
        
        // Create the XML content for Excel
        $xml_content = $this->generate_excel_xml($data);
        
        // Create the ZIP file (Excel is a ZIP with XMLs)
        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // Add the necessary files for a valid Excel
            $zip->addFromString('[Content_Types].xml', $this->get_content_types_xml());
            $zip->addFromString('_rels/.rels', $this->get_rels_xml());
            $zip->addFromString('xl/workbook.xml', $this->get_workbook_xml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $xml_content);
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->get_workbook_rels_xml());
            
            $zip->close();
            
            // Send the file
            readfile($temp_file);
            unlink($temp_file);
        } else {
            throw new Exception('Could not create the Excel file');
        }
    }

    /**
     * Generates the XML of the spreadsheet
     */
    private function generate_excel_xml($data) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '<sheetData>' . "\n";
        
        foreach ($data as $row_index => $row) {
            $xml .= '<row r="' . ($row_index + 1) . '">' . "\n";
            foreach ($row as $col_index => $cell) {
                $col_letter = chr(65 + $col_index); // A, B, C...
                $cell_ref = $col_letter . ($row_index + 1);
                
                if ($row_index === 0) {
                    // Header
                    $xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t>' . htmlspecialchars($cell) . '</t></is></c>' . "\n";
                } else {
                    // Data
                    if (is_numeric($cell) && $cell !== '') {
                        $xml .= '<c r="' . $cell_ref . '"><v>' . $cell . '</v></c>' . "\n";
                    } else {
                        $xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t>' . htmlspecialchars($cell) . '</t></is></c>' . "\n";
                    }
                }
            }
            $xml .= '</row>' . "\n";
        }
        
        $xml .= '</sheetData>' . "\n";
        $xml .= '</worksheet>';
        
        return $xml;
    }

    /**
     * Helper methods to generate required Excel XML files
     */
    private function get_content_types_xml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    </Types>';
    }

    private function get_rels_xml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    </Relationships>';
    }

    private function get_workbook_xml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>
    </sheets>
    </workbook>';
    }

    private function get_workbook_rels_xml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    </Relationships>';
    }
}?>
