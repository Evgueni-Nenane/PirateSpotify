<?php 

 class Logs {
	private $codigo;
	private $nome;
	private $apelido;
	private $perfil;
	private $email;
	private $accao;
	private $dataHora;
	
	
public function __construct($codigo, $nome, $apelido, $perfil, $email, $accao, $dataHora) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->apelido = $apelido;
        $this->perfil = $perfil;
        $this->email = $email;
        $this->accao = $accao;
        $this->dataHora = $dataHora;
	}
	public function getNome() {
		return $this->nome;
	}
	public function setNome($nome) {
		$this->nome = $nome;
	}
	public function getApelido() {
		return $this->apelido;
	}
	public function setApelido($apelido) {
		$this->apelido = $apelido;
	}
	public function setCodigo($codigo) {
		$this->codigo = $codigo;
	}
	public function setPerfil($perfil) {
		$this->perfil = $perfil;
	}
	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getAccao() {
		return $this->accao;
	}

	public function setAccao($accao) {
		$this->accao = $accao;
	}

	public function getDataHora() {
		return $this->dataHora;
	}

	public function setDataHora($dataHora) {
		$this->dataHora = $dataHora;
	}

	public function getCodigo() {
		return $this->codigo;
	}

	public function getPerfil() {
		return $this->perfil;
	}
	}
?>