<?php
/**
 * Modelo Base Legacy - FluentPDO
 * Clase base simplificada para modelos legacy
 */

class KardexFpdoModel {
    
    protected $fluent;
    
    public function __construct($fluentInstance) {
        $this->fluent = $fluentInstance;
    }
    
    /**
     * Acceso directo a FluentPDO
     */
    public function fluent() {
        return $this->fluent;
    }
}
