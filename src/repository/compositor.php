<?php

require_once __DIR__ . '/../model/compositor.php';
require_once __DIR__ . '/connection.php';

class CompositorDAO
{
    public function inserir($compositor)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Compositor (Nome_Compositor, Apelido_Compositor, "
            . "Contacto_Compositor, Email_Compositor) VALUES (?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $NomeCompositor = $compositor->getNomeCompositor();
        $ApelidoCompositor = $compositor->getApelidoCompositor();
        $ContactoCompositor = $compositor->getContactoCompositor();
        $EmailCompositor = $compositor->getEmailCompositor();

        $ps->bind_param("ssss", $NomeCompositor, $ApelidoCompositor, $ContactoCompositor, $EmailCompositor);

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
        $compositores = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Compositor";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
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

    public function listarPorCodigo($codigoDisco)
    {
        $compositores = [];
        $conn = Connection::connectionDB();
        $sql = "SELECT c.* FROM Compositor c INNER JOIN Compositor_DC cd "
            . "ON c.Codigo_Compositor = cd.Codigo_Compositor WHERE cd.Codigo_DC = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
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

    public function buscarPorCodigo($codigoCompositor)
    {
        $compositor = new Compositor();
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Compositor WHERE Codigo_Compositor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoCompositor);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            $compositor = new Compositor(
                $row['Codigo_Compositor'],
                $row['Nome_Compositor'],
                $row['Apelido_Compositor'],
                $row['Contacto_Compositor'],
                $row['Email_Compositor']
            );
        }
        return $compositor;
    }

    public function atualizar($compositor)
    {
        $conn = Connection::connectionDB();
        $sql = "UPDATE Compositor SET Email_Compositor = ?, Contacto_Compositor = ? "
            . "WHERE Codigo_Compositor = ?";
        $ps = $conn->prepare($sql);

        $email = $compositor->getEmailCompositor();
        $contacto = $compositor->getContactoCompositor();
        $codigo = $compositor->getCodigoCompositor();

        $ps->bind_param("ssi", $email, $contacto, $codigo);
        return $ps->execute();
    }

    public function temRelacionamento($codigo)
    {
        $conn = Connection::connectionDB();
        $sql = "SELECT COUNT(*) AS Quantidade "
            . "FROM Compositor_DC "
            . "WHERE Codigo_Compositor = ?";
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
        $sql = "DELETE FROM Compositor WHERE Codigo_Compositor = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigo);
        $ps->execute();
        return $ps->affected_rows > 0;
    }

    public function inserirRelacaoDiscoCompositor($codigoDisco, $codigoCompositor)
    {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Compositor_DC (Codigo_Compositor, Codigo_DC) VALUES (?, ?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("ii", $codigoCompositor, $codigoDisco);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}