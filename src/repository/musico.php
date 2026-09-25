<?php
require_once __DIR__ . '/../model/musico.php';
require_once __DIR__ . '/../model/instrumento.php';
require_once __DIR__ . '/connection.php';

class MusicoDAO
{
    public function inserir($musico)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Musico (Nome_Musico, Apelido_Musico, Contacto_Musico, Email_Musico) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $nome = $musico->getNomeMusico();
        $apelido = $musico->getApelidoMusico();
        $contacto = $musico->getContactoMusico();
        $email = $musico->getEmailMusico();

        $ps->bind_param("ssss", $nome, $apelido, $contacto, $email);

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
        $musicos = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Musico";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
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

    public function listarPorCodigo($codigoDisco)
    {
        $musicos = [];
        $conn = connection::connectionDB();
        $sql = "SELECT m.* FROM Musico m INNER JOIN Musico_DC md " .
            "ON m.Codigo_Musico = md.Codigo_Musico WHERE md.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
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

    public function buscarPorCodigo($codigoMusico)
    {
        $musico = new Musico();
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Musico WHERE Codigo_Musico = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoMusico);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $musico = new Musico(
                $row['Codigo_Musico'],
                $row['Nome_Musico'],
                $row['Apelido_Musico'],
                $this->buscarInstrumentosDoMusico($row['Codigo_Musico']),
                $row['Contacto_Musico'],
                $row['Email_Musico']
            );
        }
        return $musico;
    }

    public function buscarInstrumentosDoMusico($codigoMusico)
    {
        $instrumentos = [];
        $conn = connection::connectionDB();
        $sql = "SELECT i.Codigo, i.NomeInstrumento " .
            "FROM Instrumento i " .
            "INNER JOIN Musico_Instrumento mi " .
            "ON i.Codigo = mi.Codigo_Instr " .
            "WHERE mi.Codigo_Musico = ?";
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

    public function atualizar($musico)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Musico SET Email_Musico = ?, Contacto_Musico = ? WHERE Codigo_Musico = ?";
        $ps = $conn->prepare($sql);

        $email = $musico->getEmailMusico();
        $contacto = $musico->getContactoMusico();
        $codigo = $musico->getCodigoMusico();

        $ps->bind_param("ssi", $email, $contacto, $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function temRelacionamento($codigo)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade FROM Musico_DC WHERE Codigo_Musico = ?";
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

        $ps1 = $conn->prepare("DELETE FROM Musico_Instrumento WHERE Codigo_Musico = ?");
        $ps1->bind_param("i", $codigo);
        $ps1->execute();

        $ps2 = $conn->prepare("DELETE FROM Musico WHERE Codigo_Musico = ?");
        $ps2->bind_param("i", $codigo);
        $ps2->execute();

        return $ps2->affected_rows > 0;
    }

    public function inserirRelacaoDiscoMusico($codigoDisco, $codigoMusico)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Musico_DC (Codigo_Musico, Codigo_DC) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoMusico, $codigoDisco);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}