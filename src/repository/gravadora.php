<?php

include_once __DIR__ . '/../model/gravadora.php';
include_once __DIR__ . '/connection.php';

class GravadoraDAO
{
    public function inserir($gravadora)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Gravadora (Nome_Gravadora, Email_Gravadora, Endereco_Gravadora, Contacto_Gravadora) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $NomeGravadora = $gravadora->getNomeGravadora();
        $EmailGravadora = $gravadora->getEmailGravadora();
        $EnderecoGravadora = $gravadora->getEnderecoGravadora();
        $ContactoGravadora = $gravadora->getContactoGravadora();

        $ps->bind_param("ssss", $NomeGravadora, $EmailGravadora, $EnderecoGravadora, $ContactoGravadora);

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
        $gravadoras = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Gravadora";
        $ps = $conn->prepare($sql);
        $ps->execute();
        $rows = $ps->get_result();

        while ($row = $rows->fetch_assoc()) {
            $gravadoras[] = new Gravadora(
                $row['Codigo_Gravadora'],
                $row['Nome_Gravadora'],
                $row['Contacto_Gravadora'],
                $row['Endereco_Gravadora'],
                $row['Email_Gravadora']
            );
        }
        return $gravadoras;
    }

    public function listarPorCodigo($codigoDisco)
    {
        $gravadoras = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT g.* FROM Gravadora g INNER JOIN GravadoraDisco gd " .
            "ON g.Codigo_Gravadora = gd.Codigo_Gravadora WHERE gd.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $rows = $ps->get_result();

        while ($row = $rows->fetch_assoc()) {
            $gravadoras[] = new Gravadora(
                $row['Codigo_Gravadora'],
                $row['Nome_Gravadora'],
                $row['Contacto_Gravadora'],
                $row['Endereco_Gravadora'],
                $row['Email_Gravadora']
            );
        }
        return $gravadoras;
    }

    public function buscarPorCodigo($codigoGravadora)
    {
        $gravadora = new Gravadora();
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Gravadora WHERE Codigo_Gravadora = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoGravadora);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $gravadora = new Gravadora(
                $row['Codigo_Gravadora'],
                $row['Nome_Gravadora'],
                $row['Contacto_Gravadora'],
                $row['Endereco_Gravadora'],
                $row['Email_Gravadora']
            );
        }
        return $gravadora;
    }

    public function atualizar($gravadora)
    {
        $conn = Connection::connectionDB();
        $sql = "UPDATE Gravadora SET Endereco_Gravadora = ?, Email_Gravadora = ?, Contacto_Gravadora = ? WHERE Codigo_Gravadora = ?";
        $ps = $conn->prepare($sql);

        $EnderecoGravadora = $gravadora->getEnderecoGravadora();
        $EmailGravadora = $gravadora->getEmailGravadora();
        $ContactoGravadora = $gravadora->getContactoGravadora();
        $CodigoGravadora = $gravadora->getCodigoGravadora();

        $ps->bind_param("sssi", $EnderecoGravadora, $EmailGravadora, $ContactoGravadora, $CodigoGravadora);
        return $ps->execute();
    }

    public function temRelacionamento($codigo)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT COUNT(*) FROM GravadoraDisco WHERE Codigo_Gravadora = ?";
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
        $sql = "DELETE FROM Gravadora WHERE Codigo_Gravadora = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoDiscoGravadora($codigoDisco, $codigoGravadora)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO GravadoraDisco (Codigo_DC, Codigo_Gravadora) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoDisco, $codigoGravadora);

        try {
            return $ps->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}