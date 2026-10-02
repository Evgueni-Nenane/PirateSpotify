<?php

class Utilizador
{
	private $codigo;
	private $foto;
	private $nome;
	private $nomeCompleto;
	private $apelido;
	private $user_name;
	private $genero;
	private $perfil;
	private $email;
	private $contacto;
	private $senha;
	private $primeiroAcesso;

	public function __construct($codigo = null, $nome = null, $apelido = null, $user_name = null, $genero = null, $perfil = null, $email = null, $contacto = null, $senha = null, $primeiroAcesso = null, $foto = null)
	{
		$this->codigo = $codigo;
		$this->nome = $nome;
		$this->apelido = $apelido;
		$this->user_name = $user_name;
		$this->genero = $genero;
		$this->perfil = $perfil;
		$this->email = $email;
		$this->contacto = $contacto;
		$this->senha = $senha;
		$this->primeiroAcesso = $primeiroAcesso;
		$this->foto = $foto;

		if ($this->nome && $this->apelido) {
			$this->nomeCompleto = trim($this->nome . ' ' . $this->apelido);
		} else {
			$this->nomeCompleto = '';
		}
	}

	public function setCodigo($codigo)
	{
		return $this->codigo = $codigo;
	}
	public function setPerfil($perfil)
	{
		return $this->perfil = $perfil;
	}
	public function getNome()
	{
		return $this->nome;
	}
	public function setNome($nome)
	{
		return $this->nome = $nome;
	}
	public function getApelido()
	{
		return $this->apelido;
	}
	public function setApelido($apelido)
	{
		return $this->apelido = $apelido;
	}

	public function getNomeCompleto()
	{
		return $this->nomeCompleto;
	}
	public function setNomeCompleto($nomeCompleto)
	{
		return $this->nomeCompleto = $nomeCompleto;
	}
	public function getUser_name()
	{
		return $this->user_name;
	}
	public function setUser_name($user_name)
	{
		return $this->user_name = $user_name;
	}
	public function getGenero()
	{
		return $this->genero;
	}
	public function setGenero($genero)
	{
		return $this->genero = $genero;
	}
	public function getPerfil()
	{
		return $this->perfil;
	}
	public function getEmail()
	{
		return $this->email;
	}
	public function setEmail($email)
	{
		return $this->email = $email;
	}
	public function getContacto()
	{
		return $this->contacto;
	}
	public function setContacto($contacto)
	{
		return $this->contacto = $contacto;
	}
	public function getSenha()
	{
		return $this->senha;
	}
	public function getCodigo()
	{
		return $this->codigo;
	}
	public function isPrimeiroAcesso()
	{
		return $this->primeiroAcesso;
	}
	public function setPrimeiroAcesso($primeiroAcesso)
	{
		return $this->primeiroAcesso = $primeiroAcesso;
	}
	public function getFoto()
	{
		return $this->foto;
	}
	public function setFoto($foto)
	{
		return $this->foto = $foto;
	}

	public function __toString()
	{
		return "======= Credenciais de " . $this->nome . " =======\n"
			. "--- Nome:\t" . $this->nome . "\n"
			. "--- Apelido:\t" . $this->apelido . "\n"
			. "--- Username:\t" . $this->user_name . "\n"
			. "--- Senha:\t" . $this->senha . "\n"
			. "--- Contacto:\t" . $this->contacto . "\n";
	}

	public function toStringReset()
	{
		return "======= Credenciais de " . $this->nomeCompleto . " =======\n"
			. "--- Nome:\t" . $this->nomeCompleto . "\n"
			. "--- Senha:\t" . $this->senha . "\n"
			. "--- Contacto:\t" . $this->contacto . "\n";
	}
}