<?php

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/../model/discocompacto.php';
require_once __DIR__ . '/../model/edicao.php';

class DiscoDAO
{

   public function inserir($disco)
{
    $conn = Connection::connectionDB();
    $sql = "INSERT INTO Disco_Compacto (Titulo, Preco, Ano_Edicao) VALUES (?, ?, ?)";
    $ps = $conn->prepare($sql);
    $Titulo = $disco->getTitulo();
    $Preco = $disco->getPreco();
    $Ano_Edicao = $disco->getAnoEdicao();
    $ps->bind_param("sdi", $Titulo, $Preco, $Ano_Edicao);

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
        $discos = [];
        $conn = connection::connectionDB();
        $sql = "SELECT d.Codigo_Disco, d.Titulo, "
            . "GROUP_CONCAT(g.Nome_Genero SEPARATOR ', ') AS Generos, d.Preco, d.Ano_Edicao"
            . " FROM Disco_Compacto d"
            . " INNER JOIN Disco_Genero dg ON d.Codigo_Disco = dg.Codigo_DC"
            . " INNER JOIN Genero g ON dg.Codigo_Genero = g.Codigo_Genero"
            . " GROUP BY d.Codigo_Disco, d.Titulo, d.Preco, d.Ano_Edicao";

        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $disco = new DiscoCompacto(
                $row['Codigo_Disco'],
                $row['Titulo'],
                $row['Preco'],
                $row['Ano_Edicao'],
                [],   // generoMusical
                [],   // cantores
                [],   // musicos
                [],   // compositores
                [],   // produtores
                [],   // gravadoras
                [],   // editoras
                null, //edicao
                []    // faixas
            );
            $disco->setGeneroMusicalTxt($row['Generos']);
            $discos[] = $disco;
        }

        return $discos;
    }

    public function buscarPorCodigo($codigoDisco)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT d.Codigo_Disco, d.Titulo, d.Preco, d.Ano_Edicao FROM Disco_Compacto d WHERE d.Codigo_Disco = ?";

        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        return new DiscoCompacto(
            $row['Codigo_Disco'],
            $row['Titulo'],
            $row['Preco'],
            $row['Ano_Edicao'],
            [],    // generoMusical
            [],    // cantores
            [],    // musicos
            [],    // compositores
            [],    // produtores
            [],    // gravadoras
            [],    // editoras
            null,  // edicao
            []     // faixas
        );
    }

    public function buscarPorCodigoComEdicao($codigoDisco)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT d.Codigo_Disco, d.Titulo, d.Preco, d.Ano_Edicao, e.Codigo_Editora, e.Data_Edicao"
            . " FROM Disco_Compacto d LEFT JOIN Edicao e ON d.Codigo_Disco = e.Codigo_DC"
            . " WHERE d.Codigo_Disco = ?";

        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        $disco = new DiscoCompacto(
           $row['Codigo_Disco'],
            $row['Titulo'],
            $row['Preco'],
            $row['Ano_Edicao'],
            [],    // generoMusical
            [],    // cantores
            [],    // musicos
            [],    // compositores
            [],    // produtores
            [],    // gravadoras
            [],    // editoras
            null,  // edicao
            []     // faixas
        );

        $edicao = new Edicao($row['Codigo_Disco'], $row['Codigo_Editora'], $row['Data_Edicao']);
        $disco->setEdicao($edicao);

        return $disco;
    }

    public function atualizar($disco)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Disco_Compacto SET Titulo = ?, Preco = ?, Ano_Edicao = ? WHERE Codigo_Disco = ?";
        $ps = $conn->prepare($sql);
        $Titulo = $disco->getTitulo();
        $Preco = $disco->getPreco();
        $Ano_Edicao = $disco->getAnoEdicao();
        $Codigo_Disco = $disco->getCodigoDisco();
        $ps->bind_param("sdii", $Titulo, $Preco, $Ano_Edicao, $Codigo_Disco);
        $ps->execute();

        return $ps->affected_rows > 0;
    }

    public function remover($codigoDisco)
{
    $conn = Connection::connectionDB();
    $conn->begin_transaction();

    try {
        $ps = $conn->prepare("DELETE FROM Faixa WHERE idDisco = ?");
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();

        $tabelasPonte = ["Compositor_DC", "Musico_DC", "Cantor_DC", "Edicao", "GravadoraDisco", "ProDC", "Disco_Genero"];
        foreach ($tabelasPonte as $tabela) {
            $ps = $conn->prepare("DELETE FROM $tabela WHERE Codigo_DC = ?");
            $ps->bind_param("i", $codigoDisco);
            $ps->execute();
        }

        $ps = $conn->prepare("DELETE FROM Disco_Compacto WHERE Codigo_Disco = ?");
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $sucesso = $ps->affected_rows > 0;

        $conn->commit();
        return $sucesso;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return false;
    }
}
}
