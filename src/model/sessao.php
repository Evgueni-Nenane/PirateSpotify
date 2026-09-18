<?php

class Sessao {

    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function iniciarSessao($utilizador) {
        self::iniciar();
        $_SESSION['utilizadorLogado'] = $utilizador;
    }

    public static function terminarSessao() {
        self::iniciar();
        unset($_SESSION['utilizadorLogado']);
        session_destroy();
    }

    public static function getUtilizadorLogado() {
        self::iniciar();
        return isset($_SESSION['utilizadorLogado']) ? $_SESSION['utilizadorLogado'] : null;
    }

    public static function estaLogado() {
        self::iniciar();
        return isset($_SESSION['utilizadorLogado']);
    }
}  