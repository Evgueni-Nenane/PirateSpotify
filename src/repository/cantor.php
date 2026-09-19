<?php
require_once 'Cantor.php';
require_once 'connection.php';

class CantorDAO
{
    public function inserir($cantor)
    {
        $conn = connection::connectionDB(); //COLOQUEM O METODO DA CLASSE Q FAZ CONECT COM A BD EM TODOS OS METODOS

        $sql = "INSERT INTO Cantor (Nome_Cantor, Apelido_Cantor, "
            . "Contacto_Cantor, Email_Cantor) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);
        $NomeCantor = $cantor->getNomeCantor();
        $ApelidoCantor = $cantor->getApelidoCantor();
        $ContactoCantor = $cantor->getContactoCantor();
        $EmailCantor = $cantor->getEmailCantor();
        $ps->bind_param("ssss", $NomeCantor, $ApelidoCantor, $ContactoCantor, $EmailCantor);
        if ($ps->execute()) {
            return $conn->insert_id;
        }
        return -1;
    }

    public function listarTodos()
    {
        $cantores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Cantor";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $cantor = new Cantor(
                $row['Codigo_Cantor'],
                $row['Nome_Cantor'],
                $row['Apelido_Cantor'],
                $row['Email_Cantor']
            );
            $cantores[] = $cantor;
        }
        return $cantores;
    }

    public function listarPorCodigo($codigoDisco)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT ca.* FROM Cantor ca INNER JOIN Cantor_DC cad "
            . "ON ca.Codigo_Cantor = cad.Codigo_Cantor WHERE cad.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }
        return new Cantor(
            $row['Codigo_Cantor'],
            $row['Nome_Cantor'],
            $row['Apelido_Cantor'],
            $row['Email_Cantor']
        );
    }

    public function buscarPorCodigo($codigoCantor)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Cantor WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoCantor);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        if (!$row) {
            return null;
        }
        return new Cantor(
            $row['Codigo_Cantor'],
            $row['Nome_Cantor'],
            $row['Apelido_Cantor'],
            $row['Contacto_Cantor'],
            $row['Email_Cantor']
        );
    }


    public function atualizar($cantor)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Cantor SET email_cantor = ?, contacto_cantor = ? "
            . "WHERE Codigo_Cantor = ? ";
        $ps = $conn->prepare($sql);
        $email = $cantor->getEmailCantor();
        $contacto = $cantor->getContactoCantor();
        $codigo = $cantor->getCodigoCantor();
        $ps->bind_param("ssi", $email, $contacto, $codigo);
        return $ps->execute();
    }




    public function temRelacionamento($codigo)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade"
            . " FROM Cantor_DC "
            . " WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();
        return $row['Quantidade'] > 0;
    }


    public function remover($codigo)
    {
        if ($this->temRelacionamento($codigo)) {
            return false;
        }
        $conn = connection::connectionDB();
        $sql = "DELETE FROM Cantor WHERE Codigo_Cantor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }
    public function inserRelacaoDiscoCantor($codigoDisco, $codigoCantor)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Cantor_DC (Codigo_Cantor, Codigo_DC) VALUES (?,?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoCantor, $codigoDisco);
        return  $ps->execute();
    }
}
