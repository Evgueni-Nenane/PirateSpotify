<?php
require_once __DIR__ . '/../model/cantor.php';
require_once __DIR__ . '/connection.php';

class CantorDAO
{
    public function inserir($cantor)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Cantor (Nome_Cantor, Apelido_Cantor, "
            . "Contacto_Cantor, Email_Cantor) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $NomeCantor = $cantor->getNomeCantor();
        $ApelidoCantor = $cantor->getApelidoCantor();
        $ContactoCantor = $cantor->getContactoCantor();
        $EmailCantor = $cantor->getEmailCantor();

        $ps->bind_param("ssss", $NomeCantor, $ApelidoCantor, $ContactoCantor, $EmailCantor);

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
        $cantores = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Cantor";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
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

    public function listarPorCodigo($codigoDisco)
    {
        $cantores = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT ca.* FROM Cantor ca INNER JOIN Cantor_DC cad "
            . "ON ca.Codigo_Cantor = cad.Codigo_Cantor WHERE cad.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
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

    public function buscarPorCodigo($codigoCantor)
    {
        $cantor = new Cantor();
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Cantor WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoCantor);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $cantor = new Cantor(
                $row['Codigo_Cantor'],
                $row['Nome_Cantor'],
                $row['Apelido_Cantor'],
                $row['Contacto_Cantor'],
                $row['Email_Cantor']
            );
        }
        return $cantor;
    }

    public function atualizar($cantor)
    {
        $conn = Connection::connectionDB();
        $sql = "UPDATE Cantor SET Email_Cantor = ?, Contacto_Cantor = ? "
            . "WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);

        $email = $cantor->getEmailCantor();
        $contacto = $cantor->getContactoCantor();
        $codigo = $cantor->getCodigoCantor();

        $ps->bind_param("ssi", $email, $contacto, $codigo);
        return $ps->execute();
    }

    public function temRelacionamento($codigo)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade "
            . "FROM Cantor_DC "
            . "WHERE Codigo_Cantor = ?";
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

        $conn = Connection::connectionDB();
        $sql = "DELETE FROM Cantor WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoDiscoCantor($codigoDisco, $codigoCantor)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Cantor_DC (Codigo_Cantor, Codigo_DC) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoCantor, $codigoDisco);

        try {
            return $ps->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}