<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';   // carrega Utilizador/Perfil antes de ler a sessão
require_once __DIR__ . '/../../controller/logsController.php';

LogsController::registar('Realizou logout');   // antes de terminar a sessão
Sessao::terminarSessao();
header('Location: Login.php');
exit;