<?php 


require_once __DIR__ . '/../model/instrumento.php';
require_once __DIR__ . '/../repository/instrumento.php';

 class InstrumentoController {
	private $instrumentoDAO;
	
	public function __construct()
    {
        $this->instrumentoDAO = new InstrumentoDAO();
        }
	
	public function adicionarInstrumento($instrumento) {
		return $this->instrumentoDAO->inserir($instrumento);
		
	}
	
	public function listarInstrumentos() {
		return $this->instrumentoDAO->listarTodos();
	}
	
	public function buscarPorCodigo($codigo) {
		return $this->instrumentoDAO->listarPorCodigo($codigo);
	}
	
	public function remover($codigo) {
		return $this->instrumentoDAO->remover($codigo);
	}
}
