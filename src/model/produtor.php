<?php 
// Produtor.java

 class Produtor {
    private $codigoProdutor;
    private $nomeProdutor;
    private $apelidoProdutor;
    private $contactoProdutor;
    private $emailProdutor;
    
    public function __construct($codigoProdutor, $nomeProdutor, $apelidoProdutor, $contactoProdutor, $emailProdutor) {
        $this->codigoProdutor = $codigoProdutor;
        $this->nomeProdutor = $nomeProdutor;
        $this->apelidoProdutor = $apelidoProdutor;
        $this->contactoProdutor = $contactoProdutor;
        $this->emailProdutor = $emailProdutor;
    }

	public function getCodigoProdutor() {
		return $this->codigoProdutor;
	}

	public function setCodigoProdutor($codigoProdutor) {
		$this->codigoProdutor = $codigoProdutor;
	}

	public function getNomeProdutor() {
		return $this->nomeProdutor;
	}

	public function setNomeProdutor($nomeProdutor) {
		$this->nomeProdutor = $nomeProdutor;
	}

	public function getApelidoProdutor() {
		return $this->apelidoProdutor;
	}

	public function setApelidoProdutor($apelidoProdutor) {
		$this->apelidoProdutor = $apelidoProdutor;
	}

	public function getContactoProdutor() {
		return $this->contactoProdutor;
	}

	public function setContactoProdutor($contactoProdutor) {
		$this->contactoProdutor = $contactoProdutor;
	}

	public function getEmailProdutor() {
		return $this->emailProdutor;
	}

	public function setEmailProdutor($emailProdutor) {
		$this->emailProdutor = $emailProdutor;
	}

    public function getNomeCompleto() {
    		return $this->nomeProdutor . " " . $this->apelidoProdutor;
    }
}