<?php

require_once __DIR__ . '/../repository/logs.php';
require_once __DIR__ . '/../model/logs.php';
require_once __DIR__ . '/../model/sessao.php';

class LogsController {
    private $logsDAO;

    public function __construct()
    {
        $this->logsDAO = new LogsDAO();
    }

    public function inserirLog($log) {
        return $this->logsDAO->inserir($log);
    }

    public function listarLogs() {
        return $this->logsDAO->listarLogs();
    }

    // Regista uma acção do utilizador que está logado.
    // Uso: LogsController::registar('Registou o disco ID 5');
    public static function registar($accao) {
        $u = Sessao::getUtilizadorLogado();
        if (!$u) return false;

        $log = new Logs(
            null,
            $u->getNome(),
            $u->getApelido(),
            $u->getPerfil()->getNome(),
            $u->getEmail(),
            $accao,
            date('Y-m-d H:i:s')
        );

        return (new LogsController())->inserirLog($log);
    }
}