<?php

class Faixa
{
	private $idFaixa;
	private $nomeFaixa;
	private $artistaPrincipal;
	private $duracao; // formato "HH:MM:SS" (igual ao TIME do MySQL)
	private $numeroFaixa;
	private array $participantes = [];
	private array $compositores = [];
	private array $musicos = [];
	private array $cantores = [];

	public function __construct($nomeFaixa = null, $artistaPrincipal = null, $duracao = null, $numeroFaixa = null, $idFaixa = null)
	{
		$this->nomeFaixa = $nomeFaixa;
		$this->artistaPrincipal = $artistaPrincipal;
		$this->duracao = $duracao;
		$this->numeroFaixa = $numeroFaixa;
		$this->idFaixa = $idFaixa;
	}


	public function getIdFaixa()
	{
		return $this->idFaixa;
	}

	public function setIdFaixa($idFaixa)
	{
		return $this->idFaixa = $idFaixa;
	}

	public function getNomeFaixa()
	{
		return $this->nomeFaixa;
	}

	public function setNomeFaixa($nomeFaixa)
	{
		return $this->nomeFaixa = $nomeFaixa;
	}

	public function getArtistaPrincipal()
	{
		return $this->artistaPrincipal;
	}

	public function setArtistaPrincipal($artistaPrincipal)
	{
		return $this->artistaPrincipal = $artistaPrincipal;
	}

	public function getDuracao()
	{
		return $this->duracao;
	}

	public function setDuracao($duracao)
	{
		return $this->duracao = $duracao;
	}

	public function getNumeroFaixa()
	{
		return $this->numeroFaixa;
	}

	public function setNumeroFaixa($numeroFaixa)
	{
		return $this->numeroFaixa = $numeroFaixa;
	}

	public function getParticipantes()
	{
		return $this->participantes;
	}

	public function setParticipantes(array $participantes)
	{
		return $this->participantes = $participantes;
	}

	public function getCompositores()
	{
		return $this->compositores;
	}

	public function setCompositores(array $compositores)
	{
		return $this->compositores = $compositores;
	}

	public function getMusicos()
	{
		return $this->musicos;
	}

	public function setMusicos(array $musicos)
	{
		return $this->musicos = $musicos;
	}

	public function getCantores()
	{
		return $this->cantores;
	}

	public function setCantores(array $cantores)
	{
		return $this->cantores = $cantores;
	}
}