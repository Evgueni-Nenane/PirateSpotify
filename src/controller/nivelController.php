<?php

require_once __DIR__ . '/../repository/nivelAcesso.php';
require_once __DIR__ . '/../model/nivelacesso.php';

class NivelController
{
    private $nivelDAO;

    public function __construct()
    {
        $this->nivelDAO = new NivelAcessoDAO();
    }

    public function adicionarNivel($nome)
    {
        return $this->nivelDAO->inserir($nome);
    }

    public function listarNiveis()
    {
        return $this->nivelDAO->listarNiveis();
    }
}
