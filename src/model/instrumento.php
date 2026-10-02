<?php
class Instrumento
{
    private $codigo;
    private $nome;



    public function __construct($codigo = null, $nome = null)
    {
        $this->codigo = $codigo;
        $this->nome = $nome;
    }


    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }


    public function toString()
    {
        return $this->nome;
    }
}