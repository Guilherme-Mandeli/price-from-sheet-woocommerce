<?php
/**
 * Classe de administração do plugin
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
     * Adiciona menu no admin
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
     * Página de administração
     */
    public function admin_page() {
        ?>
        <div class="wrap">            
            <!-- Seção de Importação Fixa -->
            <div class="wcpfs-import-section">
                <div class="wcpfs-import-card">
                    <h2><?php _e('Importar Preços', 'price-from-sheet-woocommerce'); ?></h2>
                    <form id="wcpfs-import-form" method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field('wcpfs_import', 'wcpfs_nonce'); ?>
                        
                        <div class="wcpfs-form-row">
                            <div class="wcpfs-form-col">
                                <label for="wcpfs_file"><?php _e('Arquivo da Planilha', 'price-from-sheet-woocommerce'); ?></label>
                                <input type="file" id="wcpfs_file" name="wcpfs_file" accept=".csv,.xlsx,.xls" required>
                                <small><?php _e('Formatos aceitos: CSV (.csv), Excel (.xlsx, .xls) - Máximo: 10MB', 'price-from-sheet-woocommerce'); ?></small>
                            </div>
                            <div class="wcpfs-form-col">
                                <label for="wcpfs_update_mode"><?php _e('Modo de Atualização', 'price-from-sheet-woocommerce'); ?></label>
                                <select id="wcpfs_update_mode" name="wcpfs_update_mode">
                                    <option value="update"><?php _e('Atualizar preços existentes', 'price-from-sheet-woocommerce'); ?></option>
                                    <option value="add"><?php _e('Apenas adicionar novos preços', 'price-from-sheet-woocommerce'); ?></option>
                                </select>
                            </div>
                            <div class="wcpfs-form-col wcpfs-submit-col">
                                <input type="submit" class="button-primary wcpfs-import-btn" value="<?php _e('Importar Agora', 'price-from-sheet-woocommerce'); ?>" disabled="true">
                            </div>
                        </div>
                    </form>
                    
                    <div id="wcpfs-import-results" style="display: none;">
                        <button type="button" class="wcpfs-close-btn" aria-label="Close">&times;</button>
                        <h3><?php _e('Resultados da Importação', 'price-from-sheet-woocommerce'); ?></h3>
                        <div id="wcpfs-results-content"></div>
                    </div>
                </div>
            </div>
            
            <!-- Guia do Usuário -->
            <div class="wcpfs-guide-container">
                <div class="wcpfs-guide-header">
                    <div class="wcpfs-guide-header_content-wrapper">
                        <h1 style="display: inline-block;"><?php echo esc_html(get_admin_page_title()); ?></h1>
                        <span style="display: inline-block; position: relative; top: -2px; margin-inline: 5px 3px;">|</span>
                        <h2 style="display: inline-block;"><?php _e('Guia Completo', 'price-from-sheet-woocommerce'); ?></h2>
                    </div>
                    <p style="margin-top: 4px;"><?php _e('Aprenda como atualizar os preços dos seus produtos em massa.', 'price-from-sheet-woocommerce'); ?></p>
                </div>
                
                <div class="wcpfs-guide-content">
                    <!-- O que o Plugin Faz -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('O que o plugin faz?', 'price-from-sheet-woocommerce'); ?></h3>
                        <p><?php _e('Este plugin permite atualizar preços de centenas ou milhares de produtos WooCommerce de uma só vez, usando arquivos CSV ou Excel. Ao invés de alterar produto por produto manualmente, você pode fazer tudo em poucos cliques!', 'price-from-sheet-woocommerce'); ?></p>
                        
                        <h4><?php _e('Modos de atualizações:', 'price-from-sheet-woocommerce'); ?></h4>
                        <ul>
                            <li><?php _e('<strong>Atualizar preços existentes:</strong> será definido o novo valor de todos os produtos listados no arquivo', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('<strong>Apenas adicionar novos preços:</strong> será definido o novo valor somente para os produtos sem preço definido que estejam listados no arquivo', 'price-from-sheet-woocommerce'); ?></li>
                        </ul>
                    </div>
                    
                    <!-- Preparando a Planilha -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Preparando sua planilha', 'price-from-sheet-woocommerce'); ?></h3>
                        <!-- Botão para baixar modelo CSV -->
                        <div class="wcpfs-template-download" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                            <a href="<?php echo admin_url('admin-ajax.php?action=wcpfs_download_template&nonce=' . wp_create_nonce('wcpfs_template_nonce')); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-download" style="margin-right: 2px; padding-top: 5px"></span>
                                <?php _e('Baixar Modelo | CSV', 'price-from-sheet-woocommerce'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin-ajax.php?action=wcpfs_download_template_excel&nonce=' . wp_create_nonce('wcpfs_template_nonce')); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-download" style="margin-right: 2px; padding-top: 5px"></span>
                                <?php _e('Baixar Modelo | Excel', 'price-from-sheet-woocommerce'); ?>
                            </a>
                            <small style="display: block; margin-top: 5px; color: #666;">
                                <?php _e('Baixe um arquivo modelo com a estrutura correta para importação.', 'price-from-sheet-woocommerce'); ?>
                            </small>
                        </div>
                        <div class="wcpfs-format-example">
                            <h4><?php _e('Formato Obrigatório:', 'price-from-sheet-woocommerce'); ?></h4>
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
                            <h4><?php _e('Formato Completo (com preço promocional):', 'price-from-sheet-woocommerce'); ?></h4>
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
                            <h4 style="margin-top: 4px;"><?php _e('Regras Importantes:', 'price-from-sheet-woocommerce'); ?></h4>
                            <ul>
                                <li><?php _e('<strong>SKU:</strong> Deve ser exatamente igual ao cadastrado no WooCommerce', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Preços:</strong> Use ponto (.) como separador decimal (ex: 29.90)', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Arquivo:</strong> Salve como CSV (separado por vírgulas) ou como arquivo Excel respeitando o nome das colunas na primeira linha', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Codificação:</strong> UTF-8 para evitar problemas com acentos', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Como Usar -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Como usar', 'price-from-sheet-woocommerce'); ?></h3>
                        <div class="wcpfs-steps">
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">1</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Escolha o Arquivo', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Clique em "Escolher arquivo" e selecione seu arquivo CSV ou Excel preparado.', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">2</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Selecione o Modo', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Escolha se quer "Atualizar preços existentes" ou "Apenas adicionar novos preços".', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                            <div class="wcpfs-step">
                                <div class="wcpfs-step-number">3</div>
                                <div class="wcpfs-step-content">
                                    <h4><?php _e('Execute a Importação', 'price-from-sheet-woocommerce'); ?></h4>
                                    <p><?php _e('Clique em "Importar Agora" e aguarde o processamento.', 'price-from-sheet-woocommerce'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Solucionando Problemas -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Solucionando problemas comuns', 'price-from-sheet-woocommerce'); ?></h3>
                        
                        <div class="wcpfs-problem">
                            <h4><?php _e('"Produto com SKU não encontrado"', 'price-from-sheet-woocommerce'); ?></h4>
                            <p><strong><?php _e('Causa:', 'price-from-sheet-woocommerce'); ?></strong> <?php _e('O SKU na planilha não existe no WooCommerce', 'price-from-sheet-woocommerce'); ?></p>
                            <p><strong><?php _e('Solução:', 'price-from-sheet-woocommerce'); ?></strong></p>
                            <ul>
                                <li><?php _e('Verifique se o SKU está correto (sem espaços extras)', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Confirme se o produto existe no WooCommerce', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Certifique-se de que o SKU não está vazio', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="wcpfs-problem">
                            <h4><?php _e('"Linha inválida: SKU ou preço não encontrado"', 'price-from-sheet-woocommerce'); ?></h4>
                            <p><strong><?php _e('Causa:', 'price-from-sheet-woocommerce'); ?></strong> <?php _e('Linha da planilha está incompleta', 'price-from-sheet-woocommerce'); ?></p>
                            <p><strong><?php _e('Solução:', 'price-from-sheet-woocommerce'); ?></strong></p>
                            <ul>
                                <li><?php _e('Verifique se todas as linhas têm SKU e preço', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Remova linhas vazias da planilha', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('Confirme se os cabeçalhos estão corretos', 'price-from-sheet-woocommerce'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Dicas Importantes -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Dicas importantes', 'price-from-sheet-woocommerce'); ?></h3>
                        
                        <div class="wcpfs-tips">
                            <div class="wcpfs-tip wcpfs-tip-success">
                                <h4><?php _e('Boas Práticas', 'price-from-sheet-woocommerce'); ?></h4>
                                <ul>
                                    <li><?php _e('<strong>Faça backup:</strong> Sempre faça backup antes de importar', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Teste pequeno:</strong> Comece com poucos produtos para testar', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Verifique SKUs:</strong> Confirme se os SKUs estão corretos', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Use UTF-8:</strong> Para evitar problemas com acentos', 'price-from-sheet-woocommerce'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="wcpfs-tip wcpfs-tip-warning">
                                <h4><?php _e('Cuidados', 'price-from-sheet-woocommerce'); ?></h4>
                                <ul>
                                    <li><?php _e('<strong>Preços zerados:</strong> Cuidado para não colocar preços 0.00', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Vírgulas nos preços:</strong> Use ponto (29.90) não vírgula (29,90)', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>SKUs duplicados:</strong> Cada SKU deve ser único', 'price-from-sheet-woocommerce'); ?></li>
                                    <li><?php _e('<strong>Produtos variáveis:</strong> Use o SKU único da variação do produto', 'price-from-sheet-woocommerce'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Exemplo Prático -->
                    <div class="wcpfs-guide-section">
                        <h3><?php _e('Exemplo prático', 'price-from-sheet-woocommerce'); ?></h3>
                        <div class="wcpfs-example">
                            <h4><?php _e('Cenário: Reajuste de 10% em 500 produtos', 'price-from-sheet-woocommerce'); ?></h4>
                            <ol>
                                <li><?php _e('<strong>Exporte os produtos atuais:</strong> Use WooCommerce > Produtos > Exportar', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Calcule os novos preços:</strong> Abra no Excel/Google Sheets e crie fórmula para aumentar 10%', 'price-from-sheet-woocommerce'); ?></li>
                                <li><?php _e('<strong>Importe os novos preços:</strong> Salve como CSV ou arquivo Excel com as colunas e formatos recomendados em "<i>Preparando sua planilha" e importe usando este plugin<i>', 'price-from-sheet-woocommerce'); ?></li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Suporte -->
                    <div class="wcpfs-guide-section wcpfs-support-section">
                        <h3><?php _e('Precisa de ajuda?', 'price-from-sheet-woocommerce'); ?></h3>
                        <p><?php _e('Se encontrar problemas:', 'price-from-sheet-woocommerce'); ?></p>
                        <ul>
                            <li><?php _e('1. Verifique os logs na seção de resultados acima', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('2. Teste com poucos produtos primeiro', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('3. Confirme se o formato do arquivo está correto', 'price-from-sheet-woocommerce'); ?></li>
                            <li><?php _e('4. Entre em contato: <strong>hooma.com.br</strong>', 'price-from-sheet-woocommerce'); ?></li>
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
     * Manipula requisições AJAX de importação
     */
    public function handle_import_ajax() {
        check_ajax_referer('wcpfs_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'price-from-sheet-woocommerce'));
        }
        
        $importer = new WCPFS_Importer();
        $result = $importer->import_from_file($_FILES['wcpfs_file'], $_POST['wcpfs_update_mode']);
        
        wp_send_json($result);
    }

    /**
     * Manipula o download do template CSV
     */
    public function handle_download_template() {
        // Verifica nonce
        if (!wp_verify_nonce($_GET['nonce'], 'wcpfs_template_nonce')) {
            wp_die(__('Acesso negado.', 'price-from-sheet-woocommerce'));
        }
        
        // Verifica permissões
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'price-from-sheet-woocommerce'));
        }
        
        // Define headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="modelo-importacao-precos.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Abre output
        $output = fopen('php://output', 'w');
        
        // Adiciona BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos do CSV
        fputcsv($output, array('sku', 'price', 'sale_price'), ',');
        
        // Exemplos de dados
        fputcsv($output, array('EXEMPLO-001', '29.90', '24.90'), ',');
        fputcsv($output, array('EXEMPLO-002', '15.50', ''), ',');
        fputcsv($output, array('EXEMPLO-003', '89.99', '79.99'), ',');
        
        // Fecha output
        fclose($output);
        exit;
    }

    public function handle_download_template_excel() {
        // Verifica nonce
        if (!wp_verify_nonce($_GET['nonce'], 'wcpfs_template_nonce')) {
            wp_die(__('Acesso negado.', 'price-from-sheet-woocommerce'));
        }
        
        // Verifica permissões
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'price-from-sheet-woocommerce'));
        }
        
        try {
            // Cria um arquivo Excel simples usando XML
            $filename = 'modelo-importacao-precos.xlsx';
            
            // Define headers para download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Cria um arquivo Excel básico usando estrutura XML
            $this->create_simple_excel();
            
            exit;
            
        } catch (Exception $e) {
            // Se falhar, redireciona para CSV
            wp_redirect(admin_url('admin-ajax.php?action=wcpfs_download_template&nonce=' . wp_create_nonce('wcpfs_template_nonce')));
            exit;
        }
    }

    /**
     * Cria um arquivo Excel simples sem dependências externas
     */
    private function create_simple_excel() {
        // Dados para o Excel
        $data = [
            ['sku', 'price', 'sale_price'],
            ['EXEMPLO-001', '29.90', '24.90'],
            ['EXEMPLO-002', '15.50', ''],
            ['EXEMPLO-003', '89.99', '79.99']
        ];
        
        // Cria um arquivo temporário
        $temp_file = tempnam(sys_get_temp_dir(), 'wcpfs_excel_');
        
        // Cria o conteúdo XML do Excel
        $xml_content = $this->generate_excel_xml($data);
        
        // Cria o arquivo ZIP (Excel é um ZIP com XMLs)
        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // Adiciona os arquivos necessários para um Excel válido
            $zip->addFromString('[Content_Types].xml', $this->get_content_types_xml());
            $zip->addFromString('_rels/.rels', $this->get_rels_xml());
            $zip->addFromString('xl/workbook.xml', $this->get_workbook_xml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $xml_content);
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->get_workbook_rels_xml());
            
            $zip->close();
            
            // Envia o arquivo
            readfile($temp_file);
            unlink($temp_file);
        } else {
            throw new Exception('Não foi possível criar o arquivo Excel');
        }
    }

    /**
     * Gera o XML da planilha
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
                    // Cabeçalho
                    $xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t>' . htmlspecialchars($cell) . '</t></is></c>' . "\n";
                } else {
                    // Dados
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
     * Métodos auxiliares para criar os XMLs necessários do Excel
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
    <sheet name="Modelo" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>
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
