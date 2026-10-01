<?php
require_once __DIR__ . '/../model/instrumento.php';
require_once __DIR__ . '/../model/musico.php';
require_once __DIR__ . '/../repository/instrumento.php';
require_once __DIR__ . '/../repository/musico.php';

class MusicoController
{
    private $musicoDAO;
    private $instrumentoDAO;

    public function __construct()
    {
        $this->musicoDAO = new MusicoDAO();
        $this->instrumentoDAO = new InstrumentoDAO();
    }

    public function cadastrarMusico($musico)
    {
        $codigoMusico = $this->musicoDAO->inserir($musico);

        if ($codigoMusico == -1) {
            return -1;
        }

        foreach ($musico->getInstrumento() as $instrumento) {
            $this->instrumentoDAO->inserirRelacaoMusicoInstrumento($codigoMusico, $instrumento->getCodigo());
        }

        return 1;
    }

    public function listarMusicos()
    {
        return $this->musicoDAO->listarTodos();
    }

    public function buscarPorCodigo($codigo)
    {
        return $this->musicoDAO->buscarPorCodigo($codigo);
    }

    public function atualizarMusico($musico)
    {
        return $this->musicoDAO->atualizar($musico);
    }

    public function removerMusico($codigo)
    {
        return $this->musicoDAO->remover($codigo);
    }

    public function buscarMusicoPorDisco($codigoDisco)
    {
        return $this->musicoDAO->listarPorCodigo($codigoDisco);
    }
}