<?php
class Connection
{

    private static $hostname = "127.0.0.1";
    private static $username = "root";
    private static $password = "System.out.print";
    private static $database = "discocompacto";

   public static function connectionDB() {
    $conexao = mysqli_init();
    $conexao->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
    @$conexao->real_connect(self::$hostname, self::$username, self::$password, self::$database);

    if ($conexao->connect_error) {
        die("Erro de conexao com a base de dados: " . $conexao->connect_error);
    }
    return $conexao;
}
}
