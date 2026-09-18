<?php
    class Connection {

        private static $hostname = "localhost";
        private static $username = "dev_user";
        private static $password = "System.out.print";
        private static $database = "discocompacto";

        public static function connectionDB() {
            $conexao = new mysqli(self::$hostname, self::$username, self::$password, self::$database);

            if (!$conexao) {
                die("Erro de conexao com a base de dados " . mysqli_connect_error()."(".mysqli_connect_error().")");
            } else {
                echo "Conexao estabelecida";
                return $conexao;
            }
        }

    }   
?>