<?php

require_once __DIR__ . '/../repository/produtor.php';
require_once __DIR__ . '/../model/produtor.php';


 class ProdutorController {
	private $produtorDAO;
	
	public function __construct(){
        $this->produtorDAO = new ProdutorDAO();
	}
	
    public function cadastrarProdutor($produtor) {
        return $this->produtorDAO->inserir($produtor);
    }
    
    public function listarProdutor() {
        return $this->produtorDAO->listarTodos();
    }
    
    public function buscarPorCodigo($codigo) {
    		return $this->produtorDAO->buscarPorCodigo($codigo);    
    }
    
    public function atualizarProdutor($produtor) {
    	return $this->produtorDAO->atualizar($produtor);
    }
    
    public function removerProdutor($codigo) {
    	return $this->produtorDAO->remover($codigo);
    }
}
