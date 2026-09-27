<?php
require_once __DIR__ . '/../model/nivelacesso.php';
require_once __DIR__ . '/connection.php';

class NivelAcessoDAO
{
    public function inserir($nome)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO NivelAcesso (NomeNivel) VALUES (?)";
        $ps = $conn->prepare($sql);
        $ps->bind_param("s", $nome);

        try {
            $ps->execute();
            return true;
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function listarNiveis()
    {
        $niveis = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM NivelAcesso";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $niveis[] = new NivelAcesso(
                $row['CodigoNivel'],
                $row['NomeNivel']
            );
        }
        return $niveis;
    }
}