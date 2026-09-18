<?php 


 class NivelAcesso {
	
	const OPERADOR = 1;
    const SUPEROPERADOR = 2;
    const ADMINISTRADOR = 3;
    const AUDITOR = 4;
	
	private $codigoNivel;
	private $nome;
	
	public function __construct($codigoNivel, $nome) {
        $this->codigoNivel = $codigoNivel;
        $this->nome = $nome;
    }
	
	public function getCodigoNivel() {
		return $this->codigoNivel;
	}
	
	public function getNome() {
		return $this->nome;
	}
	public function setNome($nome) {
		$this->nome = $nome;
	}


	public function toString() {
		return $this->getNome();
	}
}
?>