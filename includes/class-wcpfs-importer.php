<?php
/**
 * Classe responsável pela importação de preços
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCPFS_Importer {
    
    /**
     * Array de strings que indicam remoção do preço promocional
     */
    private $remove_sale_price_keywords = array(
        'null',
        'none', 
        'empty',
        'nulo',
        'vazio',
        'nenhum',
        'sem-valor',
        'vacio',
        'ninguno',
        'sin-valor'
    );
    
    /**
     * Slugifica uma string (remove acentos, converte para minúsculas, substitui espaços por hífens)
     */
    private function slugify($text) {
        // Remove acentos
        $text = remove_accents($text);
        
        // Converte para minúsculas
        $text = strtolower($text);
        
        // Remove caracteres especiais e substitui espaços por hífens
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Verifica se o valor indica remoção do preço promocional
     */
    private function should_remove_sale_price($value) {
        if (empty($value)) {
            return false; // Valores vazios mantêm comportamento atual (ignorar)
        }
        
        $slugified = $this->slugify(trim($value));
        return in_array($slugified, $this->remove_sale_price_keywords);
    }
    
    /**
     * Importa preços de um arquivo
     */
    public function import_from_file($file, $update_mode = 'update') {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => __('File upload error.', 'price-from-sheet-woocommerce')
            );
        }
        
        // Verify file size (Excel can be larger than CSV)
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB
            return array(
                'success' => false,
                'message' => __('File too large. Maximum allowed: 10MB. For larger files, use CSV.', 'price-from-sheet-woocommerce')
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
                    'message' => __('Unsupported file format.', 'price-from-sheet-woocommerce')
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
                
                // Verifica se o número de colunas da linha corresponde ao cabeçalho
                if (count($header) !== count($row)) {
                    // Ajusta o array $row para ter o mesmo tamanho do $header
                    if (count($row) < count($header)) {
                        // Adiciona valores vazios se a linha tem menos colunas
                        $row = array_pad($row, count($header), '');
                    } else {
                        // Remove colunas extras se a linha tem mais colunas
                        $row = array_slice($row, 0, count($header));
                    }
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
        try {
            // Verifica se consegue carregar PHPSpreadsheet
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return array(
                    'success' => false,
                    'message' => __('Excel functionality temporarily unavailable. Use CSV as an alternative.', 'price-from-sheet-woocommerce')
                );
            }
            
            // Tenta carregar o arquivo
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Converte para array simples
            $rawData = $worksheet->toArray(null, true, true, true);
            
            if (empty($rawData)) {
                return array(
                    'success' => false,
                    'message' => __('Excel file is empty.', 'price-from-sheet-woocommerce')
                );
            }
            
            // Processa dados
            $data = array();
            $header = array_map('strtolower', array_map('trim', $rawData[1]));
            
            for ($i = 2; $i <= count($rawData); $i++) {
                if (isset($rawData[$i]) && !empty(array_filter($rawData[$i]))) {
                    $row_data = array_combine($header, $rawData[$i]);
                    $row_data['_line_number'] = $i;
                    $data[] = $row_data;
                }
            }
            
            return $this->process_import_data($data, $update_mode);
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('Excel error: %s. Try converting to CSV.', 'price-from-sheet-woocommerce'),
                    $e->getMessage()
                )
            );
        }
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
                    __('Line %s: SKU or price not found. Available columns: [%s]', 'price-from-sheet-woocommerce'),
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
                    __('Line %s: SKU is empty. Price: "%s"', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $price
                );
                continue;
            }
            
            // Verifica se preço está vazio
            if (empty($price)) {
                $errors[] = sprintf(
                    __('Line %s: Price is empty. SKU: "%s"', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            // Verifica se o preço é um número válido
            $price_cleaned = str_replace(',', '.', $price);
            if (!is_numeric($price_cleaned) || floatval($price_cleaned) < 0) {
                $errors[] = sprintf(
                    __('Line %s: Invalid price "%s". SKU: "%s" - Use only numbers with dot or comma as decimal separator', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $price,
                    $sku
                );
                continue;
            }
            
            $product_id = wc_get_product_id_by_sku($sku);
            
            if (!$product_id) {
                $errors[] = sprintf(
                    __('Line %s: Product not found. SKU: "%s" - Check if SKU exists in WooCommerce', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            $product = wc_get_product($product_id);
            
            if (!$product) {
                $errors[] = sprintf(
                    __('Line %s: Error loading product. SKU: "%s" - Product may be corrupted', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku
                );
                continue;
            }
            
            try {
                // Atualiza o preço
                $price_value = floatval($price_cleaned);
                $product->set_regular_price($price_value);
                
                // Processa preço promocional se a coluna existir
                if (isset($row['sale_price'])) {
                    $sale_price_raw = $row['sale_price'];
                    
                    // Se está vazio, mantém comportamento atual (ignora)
                    if (empty($sale_price_raw)) {
                        // Não faz nada - mantém preço promocional atual
                    }
                    // Se é uma string que indica remoção, remove o preço promocional
                    else if ($this->should_remove_sale_price($sale_price_raw)) {
                        $product->set_sale_price(''); // Remove o preço promocional
                    }
                    // Se é um valor numérico, processa normalmente
                    else {
                        $sale_price_cleaned = str_replace(',', '.', $sale_price_raw);
                        
                        if (is_numeric($sale_price_cleaned) && floatval($sale_price_cleaned) >= 0) {
                            $sale_price_value = floatval($sale_price_cleaned);
                            
                            // Verifica se preço promocional é menor que o preço regular
                            if ($sale_price_value >= $price_value) {
                                $errors[] = sprintf(
                                    __('Line %s: Sale price (%s) must be less than regular price (%s). SKU: "%s"', 'price-from-sheet-woocommerce'),
                                    $line_number,
                                    $sale_price_raw,
                                    $price,
                                    $sku
                                );
                                continue;
                            }
                            
                            $product->set_sale_price($sale_price_value);
                        } else {
                            // Valor não numérico e não é palavra-chave de remoção - ignora
                            // Não faz nada - mantém preço promocional atual
                        }
                    }
                }
                
                $product->save();
                $updated++;
                
            } catch (Exception $e) {
                $errors[] = sprintf(
                    __('Line %s: Error saving product. SKU: "%s" - Error: %s', 'price-from-sheet-woocommerce'),
                    $line_number,
                    $sku,
                    $e->getMessage()
                );
                continue;
            }
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('%d products updated successfully.', 'price-from-sheet-woocommerce'), $updated),
            'updated' => $updated,
            'errors' => $errors
        );
    }
}?>
