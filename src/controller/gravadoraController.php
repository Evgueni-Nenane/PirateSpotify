<?php       
require_once __DIR__ . '/../model/gravadora.php';
require_once __DIR__ . '/../repository/gravadora.php';

 class GravadoraController {

    private $gravadoraDAO;

public function __construct()
{ 
    $this->gravadoraDAO = new GravadoraDAO();
}
    public function cadastrarGravadora($gravadora) {
        return $this->gravadoraDAO->inserir($gravadora);
            }
    
    public function listarGravadoras() {
        return $this->gravadoraDAO->listarTodos();
    }
    
    public function buscarPorCodigo($codigo) {
    		return $this->gravadoraDAO->buscarPorCodigo($codigo);
    }
    
    public function atualizarGravadora($gravadora) {
    	return $this->gravadoraDAO->atualizar($gravadora);
    }
    
    public function removerGravadora($codigo) {
    	return $this->gravadoraDAO->remover($codigo);
    }
}