<?php
require_once __DIR__ . '/../model/genero.php';
require_once __DIR__ . '/connection.php';

class GeneroDAO
{
    public function inserir($genero)
    {
        $NomeGenero = $genero->getNomeGenero();

        if ($this->existeGenero($NomeGenero)) {
            return -1;
        }

        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Genero (Nome_Genero) VALUES (?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("s", $NomeGenero);

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
        $generos = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Genero";
        $ps = $conn->prepare($sql);
        $ps->execute();
        $rows = $ps->get_result();

        while ($row = $rows->fetch_assoc()) {
            $generos[] = new Genero(
                $row['Codigo_Genero'],
                $row['Nome_Genero']
            );
        }
        return $generos;
    }

    public function listarPorCodigo($codigoGenero)
    {
        $genero = new Genero();
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Genero WHERE Codigo_Genero = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoGenero);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $genero = new Genero(
                $row['Codigo_Genero'],
                $row['Nome_Genero']
            );
        }
        return $genero;
    }

    public function listarPorDisco($codigoDisco)
    {
        $generos = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT g.Codigo_Genero, g.Nome_Genero " .
            "FROM Genero g INNER JOIN Disco_Genero dg " .
            "ON g.Codigo_Genero = dg.Codigo_Genero " .
            "WHERE dg.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $rows = $ps->get_result();

        while ($row = $rows->fetch_assoc()) {
            $generos[] = new Genero(
                $row['Codigo_Genero'],
                $row['Nome_Genero']
            );
        }
        return $generos;
    }

    public function temRelacionamento($codigo)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT COUNT(*) FROM Disco_Genero WHERE Codigo_Genero = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $ps->bind_result($quantidade);
        $ps->fetch();
        return $quantidade > 0;
    }

    public function remover($codigo)
    {
        if ($this->temRelacionamento($codigo)) {
            return false;
        }

        $conn = Connection::connectionDB();
        $sql = "DELETE FROM Genero WHERE Codigo_Genero = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoGeneroDisco($codigoDisco, $codigoGenero)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Disco_Genero (Codigo_DC, Codigo_Genero) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoDisco, $codigoGenero);

        try {
            return $ps->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function removerRelacoesPorDisco($codigoDisco)
    {
        $conn = Connection::connectionDB();
        $sql = "DELETE FROM Disco_Genero WHERE Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        return $ps->execute();
    }

    private function existeGenero($nomeGenero)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT COUNT(*) FROM Genero WHERE Nome_Genero = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("s", $nomeGenero);
        $ps->execute();
        $ps->bind_result($total);
        $ps->fetch();
        return $total > 0;
    }
}