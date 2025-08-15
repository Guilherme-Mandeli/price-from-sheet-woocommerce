<?php
/**
 * Script para compilar arquivos .po em .mo manualmente
 * Execute: php compile-translations.php
 */

function compile_po_to_mo($po_file, $mo_file) {
    if (!file_exists($po_file)) {
        echo "Arquivo PO não encontrado: $po_file\n";
        return false;
    }
    
    $po_content = file_get_contents($po_file);
    $entries = array();
    
    // Parse básico do arquivo PO
    preg_match_all('/msgid "(.*)"\s*msgstr "(.*)"/', $po_content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $msgid = stripcslashes($match[1]);
        $msgstr = stripcslashes($match[2]);
        
        if (!empty($msgid) && !empty($msgstr)) {
            $entries[$msgid] = $msgstr;
        }
    }
    
    // Criar arquivo MO
    $mo_data = "";
    
    // Header MO
    $mo_data .= pack('V', 0x950412de); // Magic number
    $mo_data .= pack('V', 0); // Version
    $mo_data .= pack('V', count($entries)); // Number of entries
    $mo_data .= pack('V', 28); // Offset of key table
    $mo_data .= pack('V', 28 + count($entries) * 8); // Offset of value table
    $mo_data .= pack('V', 0); // Hash table size
    $mo_data .= pack('V', 0); // Hash table offset
    
    $keys = "";
    $values = "";
    $key_offsets = array();
    $value_offsets = array();
    
    foreach ($entries as $key => $value) {
        $key_offsets[] = array(strlen($key), strlen($keys));
        $value_offsets[] = array(strlen($value), strlen($values));
        $keys .= $key . "\0";
        $values .= $value . "\0";
    }
    
    // Key table
    foreach ($key_offsets as $offset) {
        $mo_data .= pack('V', $offset[0]); // Length
        $mo_data .= pack('V', 28 + count($entries) * 16 + $offset[1]); // Offset
    }
    
    // Value table
    foreach ($value_offsets as $offset) {
        $mo_data .= pack('V', $offset[0]); // Length
        $mo_data .= pack('V', 28 + count($entries) * 16 + strlen($keys) + $offset[1]); // Offset
    }
    
    // Keys and values
    $mo_data .= $keys . $values;
    
    if (file_put_contents($mo_file, $mo_data)) {
        echo "Arquivo MO criado: $mo_file\n";
        return true;
    } else {
        echo "Erro ao criar arquivo MO: $mo_file\n";
        return false;
    }
}

// Compilar traduções
$languages_dir = __DIR__ . '/languages/';

if (!is_dir($languages_dir)) {
    mkdir($languages_dir, 0755, true);
}

// Compile languages
compile_po_to_mo(
    $languages_dir . 'price-from-sheet-woocommerce-pt_BR.po',
    $languages_dir . 'price-from-sheet-woocommerce-pt_BR.mo'
);

echo "Compilation complete!\n";
echo "English: Default (no translation file needed)\n";
echo "Portuguese: Translated via .mo file\n";
?>