<?php
require_once __DIR__ . '/../model/produtor.php';
require_once __DIR__ . '/connection.php';

class ProdutorDAO
{
    public function inserir($produtor)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Produtor (Nome_Prod, Apelido_Produtor, Contacto_Prod, Email_Prod) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $nome = $produtor->getNomeProdutor();
        $apelido = $produtor->getApelidoProdutor();
        $contacto = $produtor->getContactoProdutor();
        $email = $produtor->getEmailProdutor();

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
        $produtores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Produtor";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
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

    public function listarPorCodigo($codigoDisco)
    {
        $produtores = [];
        $conn = connection::connectionDB();
        $sql = "SELECT p.* FROM Produtor p INNER JOIN ProDC pd ON p.Codigo_Prod = pd.Codigo_Produtor WHERE pd.Codigo_Disco = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
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

    public function buscarPorCodigo($codigoProdutor)
    {
        $produtor = new Produtor();
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Produtor WHERE Codigo_Prod = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoProdutor);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $produtor = new Produtor(
                $row['Codigo_Prod'],
                $row['Nome_Prod'],
                $row['Apelido_Produtor'],
                $row['Contacto_Prod'],
                $row['Email_Prod']
            );
        }
        return $produtor;
    }

    public function atualizar($produtor)
    {
        $conn = connection::connectionDB();
        $sql = "UPDATE Produtor SET Email_Prod = ?, Contacto_Prod = ? WHERE Codigo_Prod = ?";
        $ps = $conn->prepare($sql);

        $email = $produtor->getEmailProdutor();
        $contacto = $produtor->getContactoProdutor();
        $codigo = $produtor->getCodigoProdutor();

        $ps->bind_param("ssi", $email, $contacto, $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function temRelacionamento($codigo)
    {
        $conn = connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade FROM ProDC WHERE Codigo_Produtor = ?";
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
        $sql = "DELETE FROM Produtor WHERE Codigo_Prod = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoDiscoProdutor($codigoDisco, $codigoProdutor)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO ProDC (Codigo_Disco, Codigo_Produtor) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoDisco, $codigoProdutor);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}