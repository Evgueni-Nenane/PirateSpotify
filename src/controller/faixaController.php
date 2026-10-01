<?php 
require_once __DIR__ . '/../repository/faixa.php';
require_once __DIR__ . '/../model/faixa.php';

class FaixaController
{
    private $faixaDAO;

    public function __construct()
    {
        $this->faixaDAO = new FaixaDAO();
    }

    public function cadastrarFaixa($nomeFaixa, $artistaPrincipal, $duracao, $numeroFaixa, $codigoDisco)
    {
        if ($nomeFaixa === null || trim($nomeFaixa) === '') {
            echo "Erro: nome da faixa não pode ser vazio.";
            return -1;
        }
        if ($duracao === null) {
            echo "Erro: duração é obrigatória.";
            return -1;
        }

        $faixa = new Faixa($nomeFaixa, $artistaPrincipal, $duracao, $numeroFaixa);
        return $this->faixaDAO->inserir($faixa, $codigoDisco);
    }

    public function listarTodasFaixas()
    {
        return $this->faixaDAO->listarTodos();
    }

    public function listarFaixasPorDisco($codigoDisco)
    {
        return $this->faixaDAO->listarPorCodigo($codigoDisco);
    }

    public function obterFaixaMusical($codigo)
    {
        return $this->faixaDAO->listarFaixaPorCodigo($codigo);
    }

    public function atualizarFaixa($codigoFaixa, $faixa)
    {
        if ($faixa === null || $codigoFaixa <= 0) {
            echo "Erro: faixa inválida para atualização.";
            return false;
        }
        return $this->faixaDAO->atualizar($faixa, $codigoFaixa);
    }

    public function removerFaixa($idFaixa)
    {
        return $this->faixaDAO->remover($idFaixa);
    }

    public function associarCompositor($idFaixa, $idCompositor)
    {
        return $this->faixaDAO->InserRelacaoCompositor($idFaixa, $idCompositor);
    }

    public function associarCantor($idFaixa, $idCantor)
    {
        return $this->faixaDAO->InserRelacaoCantor($idFaixa, $idCantor);
    }

    public function associarMusico($idFaixa, $idMusico)
    {
        return $this->faixaDAO->InserRelacaoMusico($idFaixa, $idMusico);
    }

    public function associarProdutor($idFaixa, $idProdutor)
    {
        return $this->faixaDAO->InserRelacaoProdutor($idFaixa, $idProdutor);
    }

    public function removerCompositorDaFaixa($idFaixa, $idCompositor)
    {
        return $this->faixaDAO->removerCompositor($idFaixa, $idCompositor);
    }

    public function removerCantorDaFaixa($idFaixa, $idCantor)
    {
        return $this->faixaDAO->removerCantor($idFaixa, $idCantor);
    }

    public function removerMusicoDaFaixa($idFaixa, $idMusico)
    {
        return $this->faixaDAO->removerMusico($idFaixa, $idMusico);
    }

    public function removerProdutorDaFaixa($idFaixa, $idProdutor)
    {
        return $this->faixaDAO->removerProdutor($idFaixa, $idProdutor);
    }

    public function listarCompositoresDaFaixa($idFaixa)
    {
        return $this->faixaDAO->listarCompositoresPorFaixa($idFaixa);
    }

    public function listarCantoresDaFaixa($idFaixa)
    {
        return $this->faixaDAO->listarCantoresPorFaixa($idFaixa);
    }

    public function listarMusicosDaFaixa($idFaixa)
    {
        return $this->faixaDAO->listarMusicosPorFaixa($idFaixa);
    }

    public function listarProdutoresDaFaixa($idFaixa)
    {
        return $this->faixaDAO->listarProdutoresPorFaixa($idFaixa);
    }
}