[English](README.md) | [Português](README.pt-BR.md)

# WooCommerce Price From Sheet

WordPress plugin for bulk importing WooCommerce product prices via CSV and Excel spreadsheets.

## 📋 Overview

**Price From Sheet | WooCommerce** is a plugin that allows you to update prices for hundreds or thousands of WooCommerce products at once using CSV or Excel files.  
Instead of manually editing products one by one, you can do it all in just a few clicks!

## 🎯 Key Features

- ✅ Bulk price import via CSV (`.csv`) or Excel (`.xlsx`, `.xls`)  
- ✅ Support for sale prices (`sale_price`)  
- ✅ Two update modes: update existing prices or only add new ones  
- ✅ Intuitive interface integrated into WooCommerce dashboard  
- ✅ Robust data validation with detailed error reports  
- ✅ Ready-to-use templates for download (CSV and Excel)  
- ✅ Full UTF-8 support for special characters  
- ✅ HPOS (High-Performance Order Storage) compatible  

## 🚀 Installation

### Requirements

- WordPress 5.0+  
- WooCommerce 5.0+  
- PHP 7.4+  
- PHP extensions: `zip`, `xml`  

### Installation Steps

1. Clone the repository into your WordPress plugins folder:
```
git clone https://github.com/Guilherme-Mandeli/price-from-sheet-woocommerce.git
```
or via SSH
```
git clone git@github.com:Guilherme-Mandeli/price-from-sheet-woocommerce.git
```
or upload the ZIP file as a WordPress plugin.  
2. Activate the plugin in the WordPress dashboard under **Plugins > Installed Plugins**.  
3. Access the plugin under **WooCommerce > Price From Sheet**.

## 📊 How to Use

### 1. Preparing Your Spreadsheet

**Basic Format (Required)**

| sku      | price |
|----------|-------|
| PROD-001 | 29.90 |
| PROD-002 | 15.50 |
| PROD-003 | 89.99 |

**Full Format (With Sale Price)**

| sku      | price | sale_price |
|----------|-------|------------|
| PROD-001 | 29.90 | 24.90      |
| PROD-002 | 15.50 |            |
| PROD-003 | 89.99 | null       |

**Supported Keywords**

Column: sale_price
- null, none, empty
- nulo, vazio, nenhum, sem valor
- vacío, ninguno, sin valor

These keywords will remove the sale price value.

**Important Rules**

- **SKU:** Must match exactly what is registered in WooCommerce  
- **Prices:** Use a dot (`.`) as the decimal separator (e.g., 29.90)  
- **File:** Save as CSV (comma-separated) or Excel  
- **Encoding:** UTF-8 to avoid issues with accents  
- **Maximum size:** 10MB  

### 2. Importing Prices

1. Select the file: Click **Choose File** and select your spreadsheet  
2. Select the mode:
   - **Update existing prices:** Sets the new value for all listed products  
   - **Only add new prices:** Sets value only for products without a defined price  
3. Execute the import: Click **Import Now** and wait for the process  

### 3. Ready-to-Use Templates

The plugin offers ready-to-use templates:

- **CSV Template:** Basic format for import  
- **Excel Template:** Excel format with correct structure  

Access them in **WooCommerce > Price From Sheet** and click the download buttons.

## 🏗️ System Architecture

### File Structure

**Main Classes**

- **WCPFS_Main**  
  - Plugin initialization  
  - Loading scripts and styles  
  - Managing WordPress hooks  

- **WCPFS_Admin**  
  - Admin interface  
  - Template generation  
  - Handling AJAX requests  
  - Settings page and full guide  

- **WCPFS_Importer**  
  - CSV and Excel file processing  
  - Data validation  
  - Updating WooCommerce products  
  - Generating error reports  

## 🔧 Technical Features

**File Processing**

- CSV: Native reading with `fgetcsv()`  
- Excel: Uses PHPSpreadsheet for `.xlsx/.xls` files  
- Validation: Checks format, size, and structure  
- Encoding: Full UTF-8 support with BOM removal  

**Implemented Validations**

- ✅ Check if SKU exists in WooCommerce  
- ✅ Validate price format (positive numbers)  
- ✅ Ensure sale price is lower than regular price  
- ✅ Detect empty or incomplete rows  
- ✅ Detailed error report with line numbers  

**Security**

- ✅ Nonce verification on all AJAX requests  
- ✅ Permission checks (`manage_woocommerce`)  
- ✅ Input data sanitization  
- ✅ Prevent direct file access  

## 🐛 Troubleshooting

**Common Errors**

- **"Product with SKU not found"**  
  Cause: SKU in the spreadsheet does not exist in WooCommerce  
  Solution: Verify that the SKU is correct and exists in the system  

- **"Invalid row: SKU or price not found"**  
  Cause: Row in spreadsheet is incomplete  
  Solution: Ensure all rows have SKU and price  

- **"Invalid price"**  
  Cause: Price is not a valid number  
  Solution: Use only numbers with dot as decimal separator  

**Best Practices**

- 🔄 Always backup before importing  
- 🧪 Small test: Start with a few products to test  
- ✅ Verify SKUs: Make sure SKUs are correct  
- 🔤 Use UTF-8: To avoid accent issues  

## 📈 Practical Example

**Scenario:** Increase prices by 10% for 500 products

1. Export current products: Use **WooCommerce > Products > Export**  
2. Calculate new prices: Open in Excel/Google Sheets and create a formula to increase by 10%  
3. Import new prices: Save as CSV or Excel and import using this plugin  

## 🔒 Security

- Nonce validation on all AJAX requests  
- Permission checks (`manage_woocommerce`)  
- Sanitization of all input data  
- File validation: type, size, and structure  
- XSS prevention: output escaping  

**Recommendations**

- Keep regular backups  
- Test in a development environment first  
- Monitor error logs  
- Limit access to authorized users only  

## 👥 Development

**Lead Developer**

- Guilherme Mandeli  
  - 🌐 Website: [srmandeli.contact](https://srmandeli.contact)  
  - 📧 Email: guil.mandeli@gmail.com  

**Company**

- Hooma  
  - 🌐 Website: [hooma.com.br](https://hooma.com.br)  
  - 📧 Support: gmandeli@hooma.com.br

## 📄 License

This plugin is licensed under **GPL v2** or later.

## 🔄 Changelog

**Version 1.0.0 (2025-8-15)**

- ✨ Initial release  
- ✅ CSV and Excel import support  
- ✅ Full admin interface  
- ✅ Robust validations  
- ✅ Downloadable templates  
- ✅ Sale price support  
- ✅ HPOS compatibility  

## 🆘 Support

**Support Channels**

1. Documentation: This README  
2. Website: [hooma.com.br](https://hooma.com.br)  
3. Issues: GitHub Issues (if applicable)  
4. Email: Through Hooma website or gmandeli@hooma.com.br

**FAQ**

- **Can I import fields other than price?**  
  Currently only regular and sale prices. Other fields are on the roadmap.  

- **What is the limit of products per import?**  
  We recommend up to 5000 products. For more, split into smaller batches.  

- **Does the plugin work with variable products?**  
  Yes, use the specific SKU of each variation.  

- **Can I undo an import?**  
  There is no undo function. Always backup before importing.  

💡 Need help? Visit [hooma.com.br](https://hooma.com.br) for technical support.  

⭐ Enjoying the plugin? Consider leaving a review and sharing it with other developers!
