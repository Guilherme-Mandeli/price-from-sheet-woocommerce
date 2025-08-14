<?php
/**
 * Classe responsável pela importação de preços
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCPFS_Importer {
    
    /**
     * Importa preços de um arquivo
     */
    public function import_from_file($file, $update_mode = 'update') {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => __('Erro no upload do arquivo.', 'price-from-sheet-woocommerce')
            );
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        switch (strtolower($file_extension)) {
            case 'csv':
                return $this->import_from_csv($file['tmp_name'], $update_mode);
            case 'xlsx':
            case 'xls':
                return $this->import_from_excel($file['tmp_name'], $update_mode);
            default:
                return array(
                    'success' => false,
                    'message' => __('Formato de arquivo não suportado.', 'price-from-sheet-woocommerce')
                );
        }
    }
    
    /**
     * Importa de arquivo CSV
     */
    private function import_from_csv($file_path, $update_mode) {
        $data = array();
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            // Lê o cabeçalho e limpa espaços/caracteres especiais
            $header = fgetcsv($handle, 1000, ',');
            
            // Limpa o cabeçalho removendo BOM, espaços e convertendo para minúsculas
            $header = array_map(function($col) {
                // Remove BOM UTF-8
                $col = str_replace("\xEF\xBB\xBF", '', $col);
                // Remove espaços e converte para minúsculas
                return strtolower(trim($col));
            }, $header);
            
            $line_number = 2;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Pula linhas vazias
                if (empty(array_filter($row))) {
                    $line_number++;
                    continue;
                }
                
                $row_data = array_combine($header, $row);
                $row_data['_line_number'] = $line_number; // Adiciona número da linha
                $data[] = $row_data;
                $line_number++;
            }
            
            fclose($handle);
        }
        
        return $this->process_import_data($data, $update_mode);
    }
    
    /**
     * Importa de arquivo Excel
     */
    private function import_from_excel($file_path, $update_mode) {
        // Aqui você implementaria a leitura de Excel usando uma biblioteca como PhpSpreadsheet
        // Por simplicidade, retornamos um erro por enquanto
        return array(
            'success' => false,
            'message' => __('Importação de Excel ainda não implementada. Use CSV por enquanto.', 'price-from-sheet-woocommerce')
        );
    }
    
    /**
     * Processa os dados importados
     */
    private function process_import_data($data, $update_mode) {
        $updated = 0;
        $errors = array();
        
        foreach ($data as $row) {
            $line_number = isset($row['_line_number']) ? $row['_line_number'] : 'N/A';
            
            // Limpa os dados da linha
            $row = array_map('trim', $row);
            
            // Verifica se as colunas obrigatórias existem
            if (!isset($row['sku']) || !isset($row['price'])) {
                $available_columns = array_filter(array_keys($row), function($key) {
                    return $key !== '_line_number';
                });
                
                $errors[] = sprintf(
                    __('Linha %s: SKU ou preço não encontrado. Colunas disponíveis: [%s]', 'price-from-sheet-woocommerce'),
                    $line_number,
                    implode(', ', $available_columns)
                );
                continue;
            }
            
            $sku = $row['sku'];
            $price = $row['price'];
            
            // Verifica se SKU está vazio
            if (empty($sku)) {
                $errors[] = sprintf(
                    __('Linha %s: SKU está vazio. Preço informado: "%s"', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $price
                );
                continue;
            }
            
            // Verifica se preço está vazio
            if (empty($price)) {
                $errors[] = sprintf(
                    __('Linha %s: Preço está vazio. SKU: "%s"', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            // Verifica se o preço é um número válido
            $price_cleaned = str_replace(',', '.', $price);
            if (!is_numeric($price_cleaned) || floatval($price_cleaned) < 0) {
                $errors[] = sprintf(
                    __('Linha %s: Preço inválido "%s". SKU: "%s" - Use apenas números com ponto ou vírgula como separador decimal', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $price,
                    $sku
                );
                continue;
            }
            
            $product_id = wc_get_product_id_by_sku($sku);
            
            if (!$product_id) {
                $errors[] = sprintf(
                    __('Linha %s: Produto não encontrado. SKU: "%s" - Verifique se o SKU existe no WooCommerce', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            $product = wc_get_product($product_id);
            
            if (!$product) {
                $errors[] = sprintf(
                    __('Linha %s: Erro ao carregar produto. SKU: "%s" - Produto pode estar corrompido', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            try {
                // Atualiza o preço
                $price_value = floatval($price_cleaned);
                $product->set_regular_price($price_value);
                
                // Se houver preço promocional na planilha
                if (isset($row['sale_price']) && !empty($row['sale_price'])) {
                    $sale_price_cleaned = str_replace(',', '.', $row['sale_price']);
                    
                    if (is_numeric($sale_price_cleaned) && floatval($sale_price_cleaned) >= 0) {
                        $sale_price_value = floatval($sale_price_cleaned);
                        
                        // Verifica se preço promocional é menor que o preço regular
                        if ($sale_price_value >= $price_value) {
                            $errors[] = sprintf(
                                __('Linha %s: Preço promocional (%s) deve ser menor que o preço regular (%s). SKU: "%s"', 'price-from-sheet-woocommerce'),
                                $line_number,
                                $row['sale_price'],
                                $price,
                                $sku
                            );
                            continue;
                        }
                        
                        $product->set_sale_price($sale_price_value);
                    } else {
                        $errors[] = sprintf(
                            __('Linha %s: Preço promocional inválido "%s". SKU: "%s" - Use apenas números', 'price-from-sheet-woocommerce'),
                            $line_number,
                            $row['sale_price'],
                            $sku
                        );
                        continue;
                    }
                }
                
                $product->save();
                $updated++;
                
            } catch (Exception $e) {
                $errors[] = sprintf(
                    __('Linha %s: Erro ao salvar produto. SKU: "%s" - Erro: %s', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku,
                    $e->getMessage()
                );
                continue;
            }
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('%d produtos atualizados com sucesso.', 'price-from-sheet-woocommerce'), $updated),
            'updated' => $updated,
            'errors' => $errors
        );
    }
}
?>