<?php

require_once __DIR__ . '/../model/utilizador.php';
require_once __DIR__ . '/../repository/utilizador.php';
 class UtilizadorController {

    private $utilizadorDAO;

    public function __construct()
    { $this->utilizadorDAO = new UtilizadorDAO();
       }

    public function cadastrarUtilizador($utilizador) {
        return $this->utilizadorDAO->inserir($utilizador);
    }
    
    public function listarUtilizador() {
        return $this->utilizadorDAO->listarTodos();
    }
    
    public function listarPorId( $codigoUser) {
    	return $this->utilizadorDAO->buscarPorId($codigoUser);
    }
    public function suspenderUtilizador($codigo) {
    	return $this->utilizadorDAO->remover($codigo);
    }
    
    public function adicionarFotoUser($codigo,$utilizador) {
        return $this->utilizadorDAO->adicionarFoto($codigo,$utilizador);
    }
    
    public function atualizarUser($codigo,$utilizador) {
        return $this->utilizadorDAO->atualizarUser($codigo,$utilizador);
    }
    public function buscarFoto($codigoUser) {
        return $this->utilizadorDAO->buscarFoto($codigoUser);
    }

    public function resetarSenha($codigo, $novaSenha) {
    return $this->utilizadorDAO->resetarSenha($codigo, $novaSenha);
}
}