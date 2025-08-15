<?php
/**
 * Classe principal do plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCPFS_Main {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Inicializa admin se estivermos no admin
        if (is_admin()) {
            new WCPFS_Admin();
        }
    }
    
    /**
     * Carrega o domínio de texto para traduções
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'price-from-sheet-woocommerce',
            false,
            dirname(WCPFS_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Enfileira scripts do frontend
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'wcpfs-style',
            WCPFS_PLUGIN_URL . 'assets/css/style.css',
            array(),
            WCPFS_VERSION
        );
        
        wp_enqueue_script(
            'wcpfs-script',
            WCPFS_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            WCPFS_VERSION,
            true
        );
    }
    
    /**
     * Enfileira scripts do admin
     */
    public function admin_enqueue_scripts($hook) {
        // Carrega apenas nas páginas do plugin
        if (strpos($hook, 'wcpfs') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wcpfs-admin-style',
            WCPFS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WCPFS_VERSION
        );
        
        wp_enqueue_script(
            'wcpfs-admin-script',
            WCPFS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WCPFS_VERSION,
            true
        );
        
        // Localiza script para AJAX
        wp_localize_script('wcpfs-admin-script', 'wcpfs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcpfs_nonce'),
            'i18n' => array(
                'errors_found' => __('Errors found:', 'price-from-sheet-woocommerce'),
                'server_error' => __('Error communicating with the server.', 'price-from-sheet-woocommerce'),
                'importing_prices' => __('Importing prices...', 'price-from-sheet-woocommerce')
            )
        ));
    }
}
?>