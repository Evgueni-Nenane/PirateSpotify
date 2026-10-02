<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../model/permissao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/logsController.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }

// Auditoria: só Administrador e Auditor vêem os logs.
if (!Permissao::pode($utilizador, 'logs')) {
    header('Location: listagemdiscos.php');
    exit;
}
$podeUsers = Permissao::pode($utilizador, 'utilizadores');
$podeLogs  = true;

$logsController = new LogsController();
$logs = $logsController->listarLogs();

// Pesquisa simples (filtra em memória pelo texto introduzido)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $logs = array_filter($logs, function ($l) use ($q) {
        $alvo = strtolower(
            $l->getCodigo() . ' ' . $l->getNome() . ' ' . $l->getApelido() . ' ' .
            $l->getEmail() . ' ' . $l->getPerfil() . ' ' . $l->getAccao() . ' ' . $l->getDataHora()
        );
        return str_contains($alvo, strtolower($q));
    });
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs | Sistema de Gestão</title>
<link rel="stylesheet" href="../css/logs.css">
</head>
<body>

<div class="app-container">
  <aside>
    <div class="title" id="logs"><h1>DiscoGest</h1></div>
    <nav>
      <ul>
        <li class="secnav">Menu Principal</li>
        <li><a href="registro.php">Registar</a></li>
        <li><a href="listagemdiscos.php">Listar Discos</a></li>
        <li class="secnav">Intervenientes</li>
        <li><a href="artistas.php">Artistas</a></li>
        <li><a href="producao.php">Produção</a></li>
        <li class="secnav">Cadastros</li>
        <li><a href="instrumentos.php">Instrumentos</a></li>
        <li><a href="generos.php">Géneros</a></li>
        <li class="secnav">Acções</li>
        <li><a href="#">Exportar</a></li>
        <?php if ($podeUsers): ?><li><a href="administracao.php">Administração</a></li><?php endif; ?>
        <li><a href="#logs" class="active">Logs</a></li>
        <li><a href="LogOut.php">Sair</a></li>
      </ul>
    </nav>
  </aside>

  <div class="containermain">
    <header>
      <div class="pagetitle">
        <h2>Sistema de Gestão de Discos</h2>
        <p>Auditoria do sistema</p>
      </div>
      <div class="userdetails">
        <div class="userdetailstxt">
          <p><?= htmlspecialchars($utilizador->getNomeCompleto()) ?></p>
          <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
        </div>
        <img src="<?= ($utilizador->getFoto() ? '../resources/fotos/' . htmlspecialchars($utilizador->getFoto()) : '../resources/user.png') ?>" alt="Foto de Perfil">
      </div>
    </header>

    <main>
      <div class="card">
        <div class="card-header">
          <h3>Logs do Sistema</h3>
          <form method="get"><input type="search" name="q" class="search-input" placeholder="Pesquisar nos logs..." value="<?= htmlspecialchars($q) ?>"></form>
        </div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Nome Completo</th><th>E-mail Corporativo</th><th>Perfil</th><th>Acção</th><th>Data</th></tr></thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td><?= $l->getCodigo() ?></td>
                  <td><?= htmlspecialchars(trim($l->getNome() . ' ' . $l->getApelido())) ?></td>
                  <td><?= htmlspecialchars($l->getEmail()) ?></td>
                  <td><?= htmlspecialchars($l->getPerfil()) ?></td>
                  <td><?= htmlspecialchars($l->getAccao()) ?></td>
                  <td><?= htmlspecialchars($l->getDataHora()) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$logs): ?>
                <tr><td colspan="6">Nenhum registo encontrado.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

</body>
</html>
