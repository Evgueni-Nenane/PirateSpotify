<?php 



 class Gravadora {
    private $codigoGravadora;
    private $nomeGravadora;
    private $contactoGravadora;
    private $enderecoGravadora;
    private $emailGravadora;

   public function __construct($codigoGravadora, $nomeGravadora, $contactoGravadora, $enderecoGravadora, $emailGravadora) {
        $this->codigoGravadora = $codigoGravadora;
        $this->nomeGravadora = $nomeGravadora;
        $this->contactoGravadora = $contactoGravadora;
        $this->enderecoGravadora = $enderecoGravadora;
        $this->emailGravadora = $emailGravadora;
    }

	public function getCodigoGravadora() {
		return $this->codigoGravadora;
	}

	public function setCodigoGravadora($codigoGravadora) {
		$this->codigoGravadora = $codigoGravadora;
	}

	public function getNomeGravadora() {
		return $this->nomeGravadora;
	}

	public function setNomeGravadora($nomeGravadora) {
		$this->nomeGravadora = $nomeGravadora;
	}

	public function getContactoGravadora() {
		return $this->contactoGravadora;
	}

	public function setContactoGravadora($contactoGravadora) {
		$this->contactoGravadora = $contactoGravadora;
	}

	public function getEnderecoGravadora() {
		return $this->enderecoGravadora;
	}

	public function setEnderecoGravadora($enderecoGravadora) {
		$this->enderecoGravadora = $enderecoGravadora;
	}

	public function getEmailGravadora() {
		return $this->emailGravadora;
	}

	public function setEmailGravadora($emailGravadora) {
		$this->emailGravadora = $emailGravadora;
	}

   
}?>