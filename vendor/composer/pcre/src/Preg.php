<?php
namespace Composer\Pcre;

class Preg {
    /**
     * Wrapper para preg_match com tratamento de erro
     */
    public static function match($pattern, $subject, &$matches = null, $flags = 0, $offset = 0) {
        $result = preg_match($pattern, $subject, $matches, $flags, $offset);
        
        if ($result === false) {
            throw new \RuntimeException('preg_match failed: ' . preg_last_error());
        }
        
        return $result;
    }
    
    /**
     * Verifica se há match (retorna boolean)
     */
    public static function isMatch($pattern, $subject, $flags = 0, $offset = 0) {
        $result = preg_match($pattern, $subject, $matches, $flags, $offset);
        
        if ($result === false) {
            throw new \RuntimeException('preg_match failed: ' . preg_last_error());
        }
        
        return $result === 1;
    }
    
    /**
     * Wrapper para preg_match_all com tratamento de erro
     */
    public static function matchAll($pattern, $subject, &$matches = null, $flags = PREG_PATTERN_ORDER, $offset = 0) {
        $result = preg_match_all($pattern, $subject, $matches, $flags, $offset);
        
        if ($result === false) {
            throw new \RuntimeException('preg_match_all failed: ' . preg_last_error());
        }
        
        return $result;
    }
    
    /**
     * Wrapper para preg_replace com tratamento de erro
     */
    public static function replace($pattern, $replacement, $subject, $limit = -1, &$count = null) {
        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);
        
        if ($result === null) {
            throw new \RuntimeException('preg_replace failed: ' . preg_last_error());
        }
        
        return $result;
    }
    
    /**
     * Wrapper para preg_split com tratamento de erro
     */
    public static function split($pattern, $subject, $limit = -1, $flags = 0) {
        $result = preg_split($pattern, $subject, $limit, $flags);
        
        if ($result === false) {
            throw new \RuntimeException('preg_split failed: ' . preg_last_error());
        }
        
        return $result;
    }
    
    /**
     * Wrapper para preg_grep com tratamento de erro
     */
    public static function grep($pattern, $input, $flags = 0) {
        $result = preg_grep($pattern, $input, $flags);
        
        if ($result === false) {
            throw new \RuntimeException('preg_grep failed: ' . preg_last_error());
        }
        
        return $result;
    }
    
    /**
     * Verifica se há match com captura de grupos
     */
    public static function matchWithOffsets($pattern, $subject, &$matches = null, $flags = 0, $offset = 0) {
        $result = preg_match($pattern, $subject, $matches, $flags | PREG_OFFSET_CAPTURE, $offset);
        
        if ($result === false) {
            throw new \RuntimeException('preg_match failed: ' . preg_last_error());
        }
        
        return $result;
    }
}