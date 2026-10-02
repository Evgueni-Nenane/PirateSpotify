<?php
/**
 * Ponto de entrada do PirateSpotify.
 *
 * O projeto é servido a partir da raiz. Este ficheiro apenas encaminha o
 * utilizador para a página correta: a listagem/administração se já tiver
 * sessão iniciada, ou o formulário de login caso contrário.
 */
require_once __DIR__ . '/src/model/sessao.php';

Sessao::iniciar();

if (Sessao::estaLogado()) {
    header('Location: src/view/pages/administracao.php');
} else {
    header('Location: src/view/pages/Login.php');
}
exit;
