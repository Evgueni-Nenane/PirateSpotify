<?php

include_once __DIR__ . '/connection.php';
include_once __DIR__ . '/../model/faixa.php';
include_once __DIR__ . '/../model/cantor.php';
include_once __DIR__ . '/../model/compositor.php';
include_once __DIR__ . '/../model/musico.php';
include_once __DIR__ . '/../model/produtor.php';
include_once __DIR__ . '/../model/instrumento.php';

class FaixaDAO
{
   public function inserir($faixa, $codigoDisco)
{
    $conn = connection::connectionDB();
    $sql = "INSERT INTO Faixa (Nome_Faixa, Artista_Principal, Duracao, Numero_Faixa, idDisco) VALUES (?, ?, ?, ?, ?)";
    $ps = $conn->prepare($sql);

    $NomeFaixa = $faixa->getNomeFaixa();
    $ArtistaPrincipal = $faixa->getArtistaPrincipal();
    $Duracao = $faixa->getDuracao();
    $NumeroFaixa = $faixa->getNumeroFaixa();

    $ps->bind_param("sssii", $NomeFaixa, $ArtistaPrincipal, $Duracao, $NumeroFaixa, $codigoDisco);

    try {
        if ($ps->execute()) {
            return $conn->insert_id;
        }
    } catch (mysqli_sql_exception $e) {
        return -1;
    }
    return -1;
}
    public function listarTodos()
    {
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Faixa";
        $result = $conn->prepare($sql);
        $result->execute();
        $rows = $result->get_result();
        $faixas = [];
        while ($row = $rows->fetch_assoc()) {
            $faixas[] = new Faixa(
                $row['Nome_Faixa'],
                $row['Artista_Principal'],
                $row['Duracao'],
                $row['Numero_Faixa'],
                $row['idFaixa']
            );
        }
        return $faixas;
    }
    public function listarPorCodigo($codigoDisco)
    {
        $faixas = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Faixa WHERE idDisco = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $rows = $ps->get_result();
        while ($row = $rows->fetch_assoc()) {
            $faixas[] = new Faixa(
                $row['Nome_Faixa'],
                $row['Artista_Principal'],
                $row['Duracao'],
                $row['Numero_Faixa'],
                $row['idFaixa']
            );
        }
        return $faixas;
    }
    public function listarFaixaPorCodigo($codigoFaixa)
    {
        $faixa = new Faixa();
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Faixa WHERE idFaixa = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoFaixa);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $faixa = new Faixa(
                $row['Nome_Faixa'],
                $row['Artista_Principal'],
                $row['Duracao'],
                $row['Numero_Faixa'],
                $row['idFaixa']
            );
        }
        return $faixa;
    }
    public function atualizar($faixa, $codigoFaixa)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Faixa SET Nome_Faixa = ?, Artista_Principal = ?, Duracao = ? WHERE idFaixa = ?";
        $ps = $conn->prepare($sql);
        $NomeFaixa = $faixa->getNomeFaixa();
        $ArtistaPrincipal = $faixa->getArtistaPrincipal();
        $Duracao = $faixa->getDuracao();
        $ps->bind_param("sssi", $NomeFaixa, $ArtistaPrincipal, $Duracao, $codigoFaixa);
        return $ps->execute();
    }
    public function remover($idFaixa)
    {
        if ($this->temRelacionamento($idFaixa)) { //falta metodo q faz relacionamento
            return false;
        }
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Faixa WHERE idFaixa = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $idFaixa);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function temRelacionamento($idFaixa)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT
                (SELECT COUNT(*) FROM Faixa_Compositor WHERE Codigo_Faixa = ?) +
                (SELECT COUNT(*) FROM Faixa_Cantor WHERE Codigo_Faixa = ?) +
                (SELECT COUNT(*) FROM Faixa_Musico WHERE Codigo_Faixa = ?) +
                (SELECT COUNT(*) FROM Faixa_Produtor WHERE Codigo_Faixa = ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("iiii", $idFaixa, $idFaixa, $idFaixa, $idFaixa);
        $ps->execute();
        $ps->bind_result($total);
        $ps->fetch();
        return $total > 0;
    }
    public function removerCompositor($idFaixa, $idCompositor)
    {
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Faixa_Compositor WHERE Codigo_Faixa = ? AND Codigo_Compositor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idCompositor);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    public function removerCantor($idFaixa, $idCantor)
    {
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Faixa_Cantor WHERE Codigo_Faixa = ?
    AND Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idCantor);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    public function removerMusico($idFaixa, $idMusico)
    {
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Faixa_Musico WHERE Codigo_Faixa = ? AND Codigo_Musico = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idMusico);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    public function removerProdutor($idFaixa, $idProdutor)
    {
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Faixa_Produtor WHERE Codigo_Faixa = ? AND Codigo_Produtor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idProdutor);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    private function executarRemocao($sql, $idFaixa, $idPessoa)
    {
        $conn = connection::connectionDB();
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idPessoa);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    public function listarCompositoresPorFaixa($idFaixa)
    {
        $compositores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT c.* FROM Compositor c " .
            "INNER JOIN Faixa_Compositor fc ON c.Codigo_Compositor = fc.Codigo_Compositor " .
            "WHERE fc.Codigo_Faixa = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $idFaixa);
        $ps->execute();
        $rows = $ps->get_result();
        while ($row = $rows->fetch_assoc()) {
            $compositores[] = new Compositor(
                $row['Codigo_Compositor'],
                $row['Nome_Compositor'],
                $row['Apelido_Compositor'],
                $row['Contacto_Compositor'],
                $row['Email_Compositor']
            );
        }
        return $compositores;
    }

    public function listarCantoresPorFaixa($idFaixa)
    {
        $cantores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT c.* FROM Cantor c " .
            "INNER JOIN Faixa_Cantor fc ON c.Codigo_Cantor = fc.Codigo_Cantor " .
            "WHERE fc.Codigo_Faixa = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $idFaixa);
        $ps->execute();
        $rows = $ps->get_result();
        while ($row = $rows->fetch_assoc()) {
            $cantores[] = new Cantor(
                $row['Codigo_Cantor'],
                $row['Nome_Cantor'],
                $row['Apelido_Cantor'],
                $row['Contacto_Cantor'],
                $row['Email_Cantor']
            );
        }
        return $cantores;
    }
    public function listarMusicosPorFaixa($idFaixa)
{
    $musicos = [];
    $conn = connection::connectionDB();
    $sql = "SELECT m.* FROM Musico m " .
        "INNER JOIN Faixa_Musico fm ON m.Codigo_Musico = fm.Codigo_Musico " .
        "WHERE fm.Codigo_Faixa = ?";
    $ps = $conn->prepare($sql);
    $ps->bind_param("i", $idFaixa);
    $ps->execute();
    $rows = $ps->get_result();
    while ($row = $rows->fetch_assoc()) {
        $musicos[] = new Musico(
            $row['Codigo_Musico'],
            $row['Nome_Musico'],
            $row['Apelido_Musico'],
            $this->buscarInstrumentosDoMusico($row['Codigo_Musico']),
            $row['Contacto_Musico'],
            $row['Email_Musico']
        );
    }
    return $musicos;
}
    public function listarProdutoresPorFaixa($idFaixa)
    {
        $produtores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT p.* FROM Produtor p " .
            "INNER JOIN Faixa_Produtor fp ON p.Codigo_Prod = fp.Codigo_Produtor " .
            "WHERE fp.Codigo_Faixa = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $idFaixa);
        $ps->execute();
        $rows = $ps->get_result();
        while ($row = $rows->fetch_assoc()) {
            $produtores[] = new Produtor(
                $row['Codigo_Prod'],
                $row['Nome_Prod'],
                $row['Apelido_Produtor'],
                $row['Contacto_Prod'],
                $row['Email_Prod']
            );
        }
        return $produtores;
    }
    public function InserRelacaoCompositor($idFaixa, $idCompositor)
    {
        $sql = "INSERT INTO Faixa_Compositor (Codigo_Faixa, Codigo_Compositor) VALUES (?, ?)";
        return $this->executarRelacao($sql, $idFaixa, $idCompositor);
    }
    public function InserRelacaoCantor($idFaixa, $idCantor)
    {
        $sql = "INSERT INTO Faixa_Cantor (Codigo_Faixa, Codigo_Cantor) VALUES (?, ?)";
        return $this->executarRelacao($sql, $idFaixa, $idCantor);
    }
    public function InserRelacaoMusico($idFaixa, $idMusico)
    {
        $sql = "INSERT INTO Faixa_Musico (Codigo_Faixa, Codigo_Musico) VALUES (?, ?)";
        return $this->executarRelacao($sql, $idFaixa, $idMusico);
    }
    public function InserRelacaoProdutor($idFaixa, $idProdutor)
    {
        $sql = "INSERT INTO Faixa_Produtor (Codigo_Faixa, Codigo_Produtor) VALUES (?, ?)";
        return $this->executarRelacao($sql, $idFaixa, $idProdutor);
    }
    private function executarRelacao($sql, $idFaixa, $idPessoa)
    {
        $conn = Connection::connectionDB();
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $idFaixa, $idPessoa);
        return $ps->execute();
    }
    public function buscarInstrumentosDoMusico($codigoMusico)
    {
        $instrumentos = array();

        $sql = "SELECT i.Codigo, i.NomeInstrumento " .
            "FROM Instrumento i " .
            "INNER JOIN Musico_Instrumento mi " .
            "ON i.Codigo = mi.Codigo_Instr " .
            "WHERE mi.Codigo_Musico = ?";

        $conn = connection::connectionDB();
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoMusico);
        $ps->execute();
        $rows = $ps->get_result();
        while ($row = $rows->fetch_assoc()) {
            $instrumentos[] = new Instrumento(
                $row['Codigo'],
                $row['NomeInstrumento']
            );
        }
        return $instrumentos;
    }
}
