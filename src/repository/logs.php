<?php
require_once __DIR__ . '/../model/logs.php';
require_once __DIR__ . '/connection.php';

class LogsDAO
{
    public function inserir($log)
    {
        $conn = connection::connectionDB();
        $sql = "INSERT INTO Logs (nome, apelido, perfil, email, accao, hora) VALUES (?, ?, ?, ?, ?, ?)";
        $ps = $conn->prepare($sql);

        $nome = $log->getNome();
        $apelido = $log->getApelido();
        $perfil = $log->getPerfil();
        $email = $log->getEmail();
        $accao = $log->getAccao();
        $dataHora = $log->getDataHora();

        $ps->bind_param("ssssss", $nome, $apelido, $perfil, $email, $accao, $dataHora);

        try {
            return $ps->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function listarLogs()
    {
        $logs = [];
        $conn = connection::connectionDB();
        $sql = "SELECT * FROM Logs ORDER BY Hora DESC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $logs[] = new Logs(
                $row['Codigo'],
                $row['Nome'],
                $row['Apelido'],
                $row['Perfil'],
                $row['Email'],
                $row['Accao'],
                $row['Hora']
            );
        }
        return $logs;
    }
}