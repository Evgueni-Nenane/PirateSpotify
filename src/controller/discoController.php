<?php 

require_once __DIR__ . '/../repository/disco.php';
require_once __DIR__ . '/../repository/gravadora.php';
require_once __DIR__ . '/../repository/editora.php';
require_once __DIR__ . '/../repository/produtor.php';
require_once __DIR__ . '/../repository/compositor.php';
require_once __DIR__ . '/../repository/musico.php';
require_once __DIR__ . '/../repository/cantor.php';
require_once __DIR__ . '/../repository/edicao.php';
require_once __DIR__ . '/../repository/generorepository.php';
require_once __DIR__ . '/../repository/faixa.php';
require_once __DIR__ . '/../model/edicao.php';
require_once __DIR__ . '/logsController.php';

class DiscoController
{
    private $discoDAO;
    private $gravadoraDAO;
    private $editoraDAO;
    private $produtorDAO;
    private $compositorDAO;
    private $musicoDAO;
    private $cantorDAO;
    private $edicaoDAO;
    private $generoDAO;
    private $faixaDAO;

    public function __construct()
    {
        $this->discoDAO = new DiscoDAO();
        $this->gravadoraDAO = new GravadoraDAO();
        $this->editoraDAO = new EditoraDAO();
        $this->produtorDAO = new ProdutorDAO();
        $this->compositorDAO = new CompositorDAO();
        $this->musicoDAO = new MusicoDAO();
        $this->cantorDAO = new CantorDAO();
        $this->edicaoDAO = new EdicaoDAO();
        $this->generoDAO = new GeneroDAO();
        $this->faixaDAO = new FaixaDAO();
    }

    public function cadastrarDisco($disco, $dataEdicaoCompleta)
    {
        $codigoDisco = $this->discoDAO->inserir($disco);

        if ($codigoDisco == -1) {
            return -1;
        }

        foreach ($disco->getGeneroMusical() as $genero) {
            $this->generoDAO->inserirRelacaoGeneroDisco($codigoDisco, $genero->getCodigoGenero());
        }

        foreach ($disco->getCompositores() as $compositor) {
            $this->compositorDAO->inserirRelacaoDiscoCompositor($codigoDisco, $compositor->getCodigoCompositor());
        }

        foreach ($disco->getMusicos() as $musico) {
            $this->musicoDAO->inserirRelacaoDiscoMusico($codigoDisco, $musico->getCodigoMusico());
        }

        foreach ($disco->getCantores() as $cantor) {
            $this->cantorDAO->inserirRelacaoDiscoCantor($codigoDisco, $cantor->getCodigoCantor());
        }

        foreach ($disco->getGravadoras() as $gravadora) {
            $this->gravadoraDAO->inserirRelacaoDiscoGravadora($codigoDisco, $gravadora->getCodigoGravadora());
        }

        foreach ($disco->getProdutores() as $produtor) {
            $this->produtorDAO->inserirRelacaoDiscoProdutor($codigoDisco, $produtor->getCodigoProdutor());
        }

        foreach ($disco->getEditoras() as $editora) {
            $edicao = new Edicao($codigoDisco, $editora->getCodigoEditora(), $dataEdicaoCompleta);
            $this->edicaoDAO->inserir($edicao);
        }

        if ($disco->getFaixas() !== null) {
            foreach ($disco->getFaixas() as $faixa) {
                $this->faixaDAO->inserir($faixa, $codigoDisco);
            }
        }

        $edicao = $this->edicaoDAO->buscarPorCodigoDisco($codigoDisco);
        $disco->setEdicao($edicao);

        LogsController::registar('Registou o disco ID ' . $codigoDisco);
        return 1;
    }

    public function listarDiscos()
    {
        return $this->discoDAO->listarTodos();
    }

    public function actualizarDisco($disco)
    {
        $this->discoDAO->atualizar($disco);
        $this->generoDAO->removerRelacoesPorDisco($disco->getCodigoDisco());
        foreach ($disco->getGeneroMusical() as $genero) {
            $this->generoDAO->inserirRelacaoGeneroDisco($disco->getCodigoDisco(), $genero->getCodigoGenero());
        }

        LogsController::registar('Actualizou o disco ID ' . $disco->getCodigoDisco());
    }

    public function removerDisco($codigoDisco)
    {
        $removido = $this->discoDAO->remover($codigoDisco);
        if ($removido) {
            LogsController::registar('Eliminou o disco ID ' . $codigoDisco);
        }
        return $removido;
    }

    public function buscarDiscoCompleto($codigoDisco)
    {
        $disco = $this->discoDAO->buscarPorCodigo($codigoDisco);
        if ($disco === null) {
            return null;
        }

        $disco->setGeneroMusical($this->generoDAO->listarPorDisco($codigoDisco));
        $disco->setCompositores($this->compositorDAO->listarPorCodigo($codigoDisco));
        $disco->setMusicos($this->musicoDAO->listarPorCodigo($codigoDisco));
        $disco->setCantores($this->cantorDAO->listarPorCodigo($codigoDisco));
        $disco->setProdutores($this->produtorDAO->listarPorCodigo($codigoDisco));
        $disco->setGravadoras($this->gravadoraDAO->listarPorCodigo($codigoDisco));
        $disco->setEditoras($this->editoraDAO->listarPorCodigo($codigoDisco));
        $disco->setEdicao($this->edicaoDAO->buscarPorCodigoDisco($codigoDisco));
        $disco->setFaixas($this->faixaDAO->listarPorCodigo($codigoDisco));

        return $disco;
    }

    public function adicionarFaixaAoDisco($codigoDisco, $faixa)
    {
        $idFaixa = $this->faixaDAO->inserir($faixa, $codigoDisco);
        if ($idFaixa == -1) {
            return false;
        }

        if ($faixa->getCompositores() !== null) {
            foreach ($this->compositorDAO->listarPorCodigo($codigoDisco) as $compositor) {
                $this->faixaDAO->InserRelacaoCompositor($idFaixa, $compositor->getCodigoCompositor());
            }
        }

        if ($faixa->getMusicos() !== null) {
            foreach ($this->musicoDAO->listarPorCodigo($codigoDisco) as $musico) {
                $this->faixaDAO->InserRelacaoMusico($idFaixa, $musico->getCodigoMusico());
            }
        }

        if ($faixa->getCantores() !== null) {
            foreach ($this->cantorDAO->listarPorCodigo($codigoDisco) as $cantor) {
                $this->faixaDAO->InserRelacaoCantor($idFaixa, $cantor->getCodigoCantor());
            }
        }

        LogsController::registar('Adicionou a faixa ID ' . $idFaixa . ' ao disco ID ' . $codigoDisco);
        return true;
    }

    public function removerFaixasDoDisco($idsFaixas)
    {
        $todasRemovidas = true;

        foreach ($idsFaixas as $idFaixa) {
            foreach ($this->faixaDAO->listarCompositoresPorFaixa($idFaixa) as $compositor) {
                $this->faixaDAO->removerCompositor($idFaixa, $compositor->getCodigoCompositor());
            }
            foreach ($this->faixaDAO->listarMusicosPorFaixa($idFaixa) as $musico) {
                $this->faixaDAO->removerMusico($idFaixa, $musico->getCodigoMusico());
            }
            foreach ($this->faixaDAO->listarCantoresPorFaixa($idFaixa) as $cantor) {
                $this->faixaDAO->removerCantor($idFaixa, $cantor->getCodigoCantor());
            }
            foreach ($this->faixaDAO->listarProdutoresPorFaixa($idFaixa) as $produtor) {
                $this->faixaDAO->removerProdutor($idFaixa, $produtor->getCodigoProdutor());
            }

            $removida = $this->faixaDAO->remover($idFaixa);
            if (!$removida) {
                $todasRemovidas = false;
            }
        }

        if (count($idsFaixas) > 0) {
            LogsController::registar('Removeu as faixas ID ' . implode(', ', $idsFaixas));
        }

        return $todasRemovidas;
    }
}