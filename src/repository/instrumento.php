<?php 
include_once __DIR__ . '/../model/instrumento.php';
include_once __DIR__ . '/connection.php';

class InstrumentoDAO
{
    public function inserir($instrumento)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Instrumento (Codigo, NomeInstrumento) VALUES (?, ?)";
        $ps = $conn->prepare($sql);

        $codigo = $instrumento->getCodigo();
        $nome = $instrumento->getNome();

        $ps->bind_param("is", $codigo, $nome);

        try {
            return $ps->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function listarTodos()
    {
        $instrumentos = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Instrumento";
        $ps = $conn->prepare($sql);
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

    public function listarPorCodigo($codigoInstrumento)
    {
        $instrumento = new Instrumento();
        $conn = connection::connectionDB();
        $sql = "SELECT i.* FROM Instrumento i INNER JOIN Musico_Instrumento mi " .
            "ON i.Codigo = mi.Codigo_Instr WHERE mi.Codigo_Musico = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoInstrumento);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $instrumento = new Instrumento(
                $row['Codigo'],
                $row['NomeInstrumento']
            );
        }
        return $instrumento;
    }

    public function temRelacionamento($codigo)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade FROM Musico_Instrumento WHERE Codigo_Instr = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        return $row && $row['Quantidade'] > 0;
    }

    public function remover($codigo)
    {
        if ($this->temRelacionamento($codigo)) {
            return false;
        }

        $conn = connection::connectionDB();
        $sql = "DELETE FROM Instrumento WHERE Codigo = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoMusicoInstrumento($codigoMusico, $codigoInstrumento)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Musico_Instrumento (Codigo_Instr, Codigo_Musico) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoInstrumento, $codigoMusico);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}		