<?php

class Permissao
{
    // Ordem simples das acções de cada ecrã (serve para renderizar e para validar).
    public const LER    = 'ler';
    public const ADIC   = 'adicionar';
    public const EDIT   = 'editar';
    public const REM    = 'remover';

    // Níveis vindos da tabela NivelAcesso (model/nivelacesso.php).
    public const OPERADOR      = 1;
    public const SUPEROPERADOR = 2;
    public const ADMINISTRADOR = 3;
    public const AUDITOR       = 4;

    /**
     * Regras pedidas:
     * - Operador:        só Listar e Criar
     * - Superoperador:   Listar, Criar, Editar e Apagar (não gere utilizadores)
     * - Administrador:   CRUD completo + painel de utilizadores
     * - Auditor:         CRUD completo + ver logs, sem painel de utilizadores
     *
     * @return array{ler:bool,adicionar:bool,editar:bool,remover:bool,utilizadores:bool,logs:bool}
     */
    public static function regras(?int $nivel): array
    {
        return match ($nivel) {
            self::OPERADOR => [
                'ler' => true, 'adicionar' => true, 'editar' => false, 'remover' => false,
                'utilizadores' => false, 'logs' => false,
            ],
            self::SUPEROPERADOR => [
                'ler' => true, 'adicionar' => true, 'editar' => true, 'remover' => true,
                'utilizadores' => false, 'logs' => false,
            ],
            self::AUDITOR => [
                'ler' => true, 'adicionar' => true, 'editar' => true, 'remover' => true,
                'utilizadores' => false, 'logs' => true,
            ],
            self::ADMINISTRADOR => [
                'ler' => true, 'adicionar' => true, 'editar' => true, 'remover' => true,
                'utilizadores' => true, 'logs' => true,
            ],
            default => [
                'ler' => false, 'adicionar' => false, 'editar' => false, 'remover' => false,
                'utilizadores' => false, 'logs' => false,
            ],
        };
    }

    public static function nivelDo(?object $utilizador): int
    {
        if (!$utilizador) return 0;
        $perfil = $utilizador->getPerfil();
        if (is_object($perfil) && method_exists($perfil, 'getCodigoNivel')) {
            return (int)$perfil->getCodigoNivel();
        }
        return (int)$perfil;
    }

    public static function pode(?object $utilizador, string $accao): bool
    {
        $regras = self::regras(self::nivelDo($utilizador));
        return $regras[$accao] ?? false;
    }

    /** Recusa silenciosamente acções POST proibidas e devolve a mensagem a mostrar. */
    public static function bloquear(?object $utilizador, string $accao): ?string
    {
        if (self::pode($utilizador, $accao)) return null;
        return 'O seu perfil não tem permissão para esta acção.';
    }
}
