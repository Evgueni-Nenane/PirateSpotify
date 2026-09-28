<?php 

require_once __DIR__ . '/../repository/compositor.php';
require_once __DIR__ . '/../model/compositor.php';


 class CompositorController {
	private  $compositorDAO;
	
	public function __construct()
    {
        $this->compositorDAO = new CompositorDAO;
    } 
		
    public function cadastrarCompositor($compositor) {
        return $this->compositorDAO->inserir($compositor);
    }
    
    public function listarCompositor() {
        return $this->compositorDAO->listarTodos();
    }
    
    public function buscarPorCodigo($codigo) {
    		return $this->compositorDAO->buscarPorCodigo($codigo);
    }
    
    public function atualizarCompositor($compositor) {
    		return $this->compositorDAO->atualizar($compositor);
    }
    
    public function removerCompositor($codigo) {
    		return $this->compositorDAO->remover($codigo);
    }
    
    public function buscarCompositorPorDisco($codigoDisco) {
    		return $this->compositorDAO->listarPorCodigo($codigoDisco);
    }
}
