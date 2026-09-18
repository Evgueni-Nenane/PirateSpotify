<?php

    require_once "connection.php";

    $conexao = Connection::connectionDB();

    $query = "INSERT INTO genero (nome_genero) VALUES ('Samba')";

    try {
        $resultado = mysqli_query($conexao, $query);
        echo $resultado;

    } catch (mysqli_sql_exception $e) {
        echo "Erro" . $e->getMessage()."\n";
        echo "Codigo" . $e->getCode()."\n";   
    }


?>