<?php

require_once __DIR__ . '/src/model/sessao.php';

Sessao::iniciar();

if (Sessao::estaLogado()) {
    header('Location: src/view/pages/administracao.php');
} else {
    header('Location: src/view/pages/Login.php');
}
exit;
