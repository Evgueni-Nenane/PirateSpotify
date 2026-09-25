<?php

class Musico
{
	private $codigoMusico;
	private $nomeMusico;
	private $apelidoMusico;
	private ?array $instrumento;
	private $contactoMusico;
	private $emailMusico;

	public function __construct($codigoMusico = null, $nomeMusico = null, $apelidoMusico = null, $instrumento = null, $contactoMusico = null, $emailMusico = null)
	{
		$this->codigoMusico = $codigoMusico;
		$this->nomeMusico = $nomeMusico;
		$this->apelidoMusico = $apelidoMusico;
		$this->instrumento = $instrumento;
		$this->contactoMusico = $contactoMusico;
		$this->emailMusico = $emailMusico;
	}


	public function getCodigoMusico()
	{
		return $this->codigoMusico;
	}

	public function setCodigoMusico($codigoMusico)
	{
		$this->codigoMusico = $codigoMusico;
	}

	public function getNomeMusico()
	{
		return $this->nomeMusico;
	}

	public function setNomeMusico($nomeMusico)
	{
		$this->nomeMusico = $nomeMusico;
	}

	public function getApelidoMusico()
	{
		return $this->apelidoMusico;
	}

	public function setApelidoMusico($apelidoMusico)
	{
		$this->apelidoMusico = $apelidoMusico;
	}

	public function getInstrumento()
	{
		return $this->instrumento;
	}

	public function setInstrumento($instrumento)
	{
		$this->instrumento = $instrumento;
	}

	public function getContactoMusico()
	{
		return $this->contactoMusico;
	}

	public function setContactoMusico($contactoMusico)
	{
		$this->contactoMusico = $contactoMusico;
	}

	public function getEmailMusico()
	{
		return $this->emailMusico;
	}

	public function setEmailMusico($emailMusico)
	{
		$this->emailMusico = $emailMusico;
	}

	public function getNomeCompleto()
	{
		return $this->nomeMusico . " " . $this->apelidoMusico;
	}
}