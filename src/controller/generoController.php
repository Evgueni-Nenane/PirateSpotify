<?php 
require_once __DIR__ . '/../model/genero.php';
require_once __DIR__ . '/../repository/generorepository.php';

 class GeneroController {

    private $generoDAO;

   public function __construct()
   { 
    $this->generoDAO = new GeneroDAO();
     }

    public function adicionarGenero($genero) {
        return $this->generoDAO->inserir($genero);
    }
    
    public function listarGeneros() {
        return $this->generoDAO->listarTodos();
            }
    
    public function removerGenero($codigo) {
    		return $this->generoDAO->remover($codigo);
            }

}