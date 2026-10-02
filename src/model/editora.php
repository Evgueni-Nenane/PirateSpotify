<?php 
class Editora {
    private $codigoEditora;
    private $nomeEditora;
    private $contactoEditora;
    private $emailEditora;
    private $endereco;

    	
    public function __construct($codigoEditora = null, $nomeEditora = null, $contactoEditora = null, $emailEditora = null, $endereco = null)
    {
        $this->codigoEditora=$codigoEditora;
        $this->nomeEditora=$nomeEditora;
        $this->contactoEditora=$contactoEditora;
        $this->emailEditora=$emailEditora;
        $this->endereco=$endereco;
    }

	public function getCodigoEditora() {
		return $this->codigoEditora;
	}

	public function setCodigoEditora($codigoEditora) {
		return $this->codigoEditora = $codigoEditora;
	}

	public function getNomeEditora() {
		return $this-> nomeEditora;
	}

	public function setNomeEditora($nomeEditora) {
		return $this->nomeEditora = $nomeEditora;
	}

	public function getContactoEditora() {
		return $this->contactoEditora;
	}

	public function setContactoEditora($contactoEditora) {
		return $this->contactoEditora = $contactoEditora;
	}

	public function getEmailEditora() {
		return $this->emailEditora;
	}

	public function setEmailEditora($emailEditora) {
		return $this->emailEditora = $emailEditora;
	}

	public function getEndereco() {
		return $this->endereco;
	}

	public function setEndereco($endereco) {
		return $this->endereco = $endereco;
	}


    public function __toString()
    {
        return "Editora:{$this->nomeEditora}<br>";
    }
}