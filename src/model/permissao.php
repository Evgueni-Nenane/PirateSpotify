<?php

class Permissao
{
    public const LER    = 'ler';
    public const ADIC   = 'adicionar';
    public const EDIT   = 'editar';
    public const REM    = 'remover';

    public const OPERADOR      = 1;
    public const SUPEROPERADOR = 2;
    public const ADMINISTRADOR = 3;
    public const AUDITOR       = 4;

    // Guarda as regras já lidas, para não repetir a query
    private static $cache = [];

    // Devolve as permissões de um nível, lidas da tabela NivelAcesso
    public static function regras($nivel)
    {
        $nivel = (int)$nivel;
        if (!isset(self::$cache[$nivel])) {
            require_once __DIR__ . '/../repository/nivelAcesso.php';
            $dao = new NivelAcessoDAO();
            self::$cache[$nivel] = $dao->regrasDoNivel($nivel);
        }
        return self::$cache[$nivel];
    }

    public static function nivelDo($utilizador)
    {
        if (!$utilizador) return 0;
        $perfil = $utilizador->getPerfil();
        if (is_object($perfil) && method_exists($perfil, 'getCodigoNivel')) {
            return (int)$perfil->getCodigoNivel();
        }
        return (int)$perfil;
    }

    public static function pode($utilizador, $accao)
    {
        $regras = self::regras(self::nivelDo($utilizador));
        return $regras[$accao] ?? false;
    }

    // Devolve a mensagem de erro se não puder, ou null se puder
    public static function bloquear($utilizador, $accao)
    {
        if (self::pode($utilizador, $accao)) return null;
        return 'O seu perfil não tem permissão para esta acção.';
    }
}