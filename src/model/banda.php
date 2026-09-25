<?php 

class Banda {
    private $codigoBanda;
    private $nomeBanda;
    private ?array $cantores;
    private ?array $musicos;
    private ?array $compositores;

    public function __construct($codigoBanda = null, $nomeBanda = null, $cantores = null, $musicos = null, $compositores = null){
        $this->codigoBanda = $codigoBanda;
        $this->nomeBanda = $nomeBanda;
        $this->cantores = $cantores;
        $this->musicos = $musicos;
        $this->compositores = $compositores;
    }

    public function getCodigoBanda() { 
        return $this->codigoBanda; 
    }

    public function setCodigoBanda($codigoBanda) { 
        $this->codigoBanda = $codigoBanda;
    }

    public function getNomeBanda() { 
        return $this->nomeBanda; 
    }

    public function setNomeBanda($nomeBanda) {
        return $this->nomeBanda = $nomeBanda;
    }

    public function getCantores() {
        return $this->cantores; 
    }

    public function setCantores($cantores) {
        return $this->cantores = $cantores; 
    }

    public function getMusicos() {
        return $this->musicos; 
    }

    public function setMusicos($musicos) {
        return $this->musicos = $musicos; 
    }

    public function getCompositores() {
        return $this->compositores; 
    }

    public function setCompositores($compositores) {
        return $this->compositores = $compositores; 
    }

    public function __toString()
    {
        return "Banda:{$this->nomeBanda}<br>";
    }
}