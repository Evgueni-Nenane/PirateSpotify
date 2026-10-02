<?php 


require_once __DIR__ . '/../repository/cantor.php';
require_once __DIR__ . '/../model/cantor.php';



 class CantorController {
	private  $cantorDAO;

    public function __construct()
    {
        $this->cantorDAO = new CantorDAO();
    }
	
    public function cadastrarCantor($cantor) {
        return $this->cantorDAO->inserir($cantor);
    }
    
    public function listarCantor() {
        return $this->cantorDAO->listarTodos();
    }
    
    public function buscarPorCodigo($codigo) {
    		return $this->cantorDAO->buscarPorCodigo($codigo);
    }
    
    public function atualizarCantor($cantor) {
    	return $this->cantorDAO->atualizar($cantor);
    }
    
    public function removerCantor($codigo) {
    	return $this->cantorDAO->remover($codigo);
    }
    
    public function buscarCantorPorDisco($codigoDisco) {
    		return $this->cantorDAO->listarPorCodigo($codigoDisco);
    }
}
