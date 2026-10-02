<?php 
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/../model/edicao.php';

class EdicaoDAO {
    public function inserir($edicao) {
        $conn = Connection::connectionDB();
        $sql = "INSERT INTO Edicao (Data_Edicao, Codigo_Disco, Codigo_Editora) VALUES (?, ?, ?)";
        $ps = $conn->prepare($sql);

        $Data_Edicao = $edicao->getDataEdicao();
        $Codigo_Disco = $edicao->getCodigoDisco();
        $Codigo_Editora = $edicao->getCodigoEditora();

        $ps->bind_param("sii", $Data_Edicao, $Codigo_Disco, $Codigo_Editora);

        try {
            if ($ps->execute()) {
                return $conn->insert_id;
            }
        } catch (mysqli_sql_exception $e) {
            return -1;
        }
        return -1;
    }

    public function buscarPorCodigoDisco($codigoDisco) {
        $conn = Connection::connectionDB();
        $sql = "SELECT * FROM Edicao WHERE Codigo_Disco = ?";
        $ps = $conn->prepare($sql);
        $ps->bind_param("i", $codigoDisco);
        $ps->execute();
        $row = $ps->get_result()->fetch_assoc();

        if ($row) {
            return new Edicao(
                $row['Codigo_Disco'],
                $row['Codigo_Editora'],
                $row['Data_Edicao']
            );
        }
        return null;
    }
}