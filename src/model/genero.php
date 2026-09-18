<?php 
 class Genero {

    private $codigoGenero;
    private $nomeGenero;

   
    
    public function __construct($codigoGenero, $nomeGenero) {
        $this->codigoGenero = $codigoGenero;
        $this->nomeGenero = $nomeGenero;
    }
    
    
    public function getCodigoGenero() {
        return $this->codigoGenero;
    }

    public function getNomeGenero() {
        return $this->nomeGenero;
    }

 
    public function toString() {
        return $this->nomeGenero;
    }
}