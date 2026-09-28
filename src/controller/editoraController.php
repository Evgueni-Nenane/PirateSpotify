<?php 

require_once __DIR__ . '/../repository/edicao.php';
require_once __DIR__ . '/../model/editora.php';

class EditoraController {
	private $editoraDAO;
	
	public function __construct()
    { 
        $this->editoraDAO = new EditoraDAO;
       } 
		
	
	
    public function cadastrarEditora($editora) {
        return $this->editoraDAO->inserir($editora);
    }
    
    public function listarEditoras() {
        return $this->editoraDAO->listarTodos();
    }
    
    public function buscarPorCodigo($codigo) {
    		return $this->editoraDAO->buscarPorCodigo($codigo);
    }
    
    public function atualizarEditora($editora) {
    		return $this->editoraDAO->atualizar($editora);
    }
    
    public function removerEditora($codigo) {
    		return $this->editoraDAO->remover($codigo);
    }
}
