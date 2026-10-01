<?php
require_once __DIR__ . '/../../model/sessao.php';

Sessao::terminarSessao();
header('Location: login.php');
exit;