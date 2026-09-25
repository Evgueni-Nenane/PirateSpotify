<?php

class DiscoCompacto {
	private $codigoDisco;
	private $titulo;
	private $preco;
	private $anoEdicao;
	private $generoMusicalTxt;
	private $generoMusical;
	private $cantores;
	private $musicos;
	private $compositores;
	private $produtores;
	private $gravadoras;
	private $editoras;
	private $edicao;
	private $faixas;


	public function __construct($codigoDisco = null, $titulo = null, $preco = null, $anoEdicao = null, $generoMusical = null, $cantores = null, $musicos = null, $compositores = null, $produtores = null, $gravadoras = null, $editoras = null, $edicao = null, $faixas = null)
	{
		$this->codigoDisco = $codigoDisco;
		$this->titulo = $titulo;
		$this->preco = $preco;
		$this->anoEdicao = $anoEdicao;
		$this->generoMusical = $generoMusical;
		$this->cantores = $cantores;
		$this->musicos = $musicos;
		$this->compositores = $compositores;
		$this->produtores = $produtores;
		$this->gravadoras = $gravadoras;
		$this->editoras = $editoras;
		$this->edicao = $edicao;
		$this->faixas = $faixas;
	}


	public function getCodigoDisco()
	{
		return $this->codigoDisco;
	}

	public function setCodigoDisco($codigoDisco)
	{
		return $this->codigoDisco = $codigoDisco;
	}

	public function getTitulo()
	{
		return $this->titulo;
	}

	public function setTitulo($titulo)
	{
		return $this->titulo = $titulo;
	}

	public function getPreco()
	{
		return $this->preco;
	}

	public function setPreco($preco)
	{
		return $this->preco = $preco;
	}

	public function getAnoEdicao()
	{
		return $this->anoEdicao;
	}

	public function setAnoEdicao($anoEdicao)
	{
		return $this->anoEdicao = $anoEdicao;
	}

	public function getGeneroMusicalTxt()
	{
		return $this->generoMusicalTxt;
	}

	public function setGeneroMusicalTxt($generoMusicalTxt)
	{
		return $this->generoMusicalTxt = $generoMusicalTxt;
	}

	public function getGeneroMusical()
	{
		return $this->generoMusical;
	}

	public function setGeneroMusical(?array $generoMusical)
	{
		return $this->generoMusical = $generoMusical;
	}

	public function getCantores()
	{
		return $this->cantores;
	}

	public function setCantores(?array $cantores)
	{
		return $this->cantores = $cantores;
	}

	public function getMusicos()
	{
		return $this->musicos;
	}

	public function setMusicos(?array $musicos)
	{
		return $this->musicos = $musicos;
	}

	public function getCompositores()
	{
		return $this->compositores;
	}

	public function setCompositores(?array $compositores)
	{
		return $this->compositores = $compositores;
	}

	public function getProdutores()
	{
		return $this->produtores;
	}

	public function setProdutores(?array $produtores)
	{
		return $this->produtores = $produtores;
	}

	public function getGravadoras()
	{
		return $this->gravadoras;
	}

	public function setGravadoras(?array $gravadoras)
	{
		return $this->gravadoras = $gravadoras;
	}

	public function getEditoras()
	{
		return $this->editoras;
	}

	public function setEditoras(?array $editoras)
	{
		return $this->editoras = $editoras;
	}

	public function getEdicao()
	{
		return $this->edicao;
	}

	public function setEdicao(?Edicao $edicao)
	{
		return $this->edicao = $edicao;
	}

	public function getFaixas()
	{
		return $this->faixas;
	}

	public function setFaixas(?array $faixas)
	{
		return $this->faixas = $faixas;
	}

	public function discoExistencia($ano)
	{
		$anoActual = (int) date("Y");
		return $anoActual - $ano;
	}
}