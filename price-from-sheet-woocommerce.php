<?php
/**
 * Plugin Name: Price From Sheet | WooCommerce
 * Plugin URI: https://hooma.com.br
 * Description: Plugin para importar preços de produtos WooCommerce a partir de planilhas.
 * Version: 1.0.0
 * Author: Hooma
 * Author URI: https://hooma.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: price-from-sheet-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

/**
 * Developer: Guilherme Mandeli
 * Developer URI: https://srmandeli.contact/
 * Developer Email: guil.mandeli@gmail.com/
 * 
 * Responsável pela criação e manutenção deste código.
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes do plugin
define('WCPFS_VERSION', '1.0.0');
define('WCPFS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCPFS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCPFS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Verifica se o WooCommerce está ativo
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'wcpfs_woocommerce_missing_notice');
    return;
}

/**
 * Aviso quando WooCommerce não está ativo
 */
function wcpfs_woocommerce_missing_notice() {
    echo '<div class="notice notice-error"><p>';
    echo __('Woocommerce Price From Sheet requer o WooCommerce para funcionar.', 'price-from-sheet-woocommerce');
    echo '</p></div>';
}
// Inclui arquivos principais
require_once WCPFS_PLUGIN_PATH . 'includes/class-wcpfs-main.php';
require_once WCPFS_PLUGIN_PATH . 'includes/class-wcpfs-admin.php';
require_once WCPFS_PLUGIN_PATH . 'includes/class-wcpfs-importer.php';

// Inicializa o plugin
function wcpfs_init() {
    new WCPFS_Main();
}
add_action('plugins_loaded', 'wcpfs_init');

// Declara compatibilidade com HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Hook de ativação
register_activation_hook(__FILE__, 'wcpfs_activate');
function wcpfs_activate() {
    // Código de ativação aqui
    flush_rewrite_rules();
}

// Hook de desativação
register_deactivation_hook(__FILE__, 'wcpfs_deactivate');
function wcpfs_deactivate() {
    // Código de desativação aqui
    flush_rewrite_rules();
}
?>
