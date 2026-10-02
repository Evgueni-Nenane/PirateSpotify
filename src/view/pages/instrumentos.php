<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../repository/musico.php';
require_once __DIR__ . '/../../repository/cantor.php';
require_once __DIR__ . '/../../repository/compositor.php';
require_once __DIR__ . '/../../repository/instrumento.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }
$username = $utilizador->getUser_name();

?>


<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instrumentos | Sistema de Gestão</title>
<link rel="stylesheet" href="../css/instrumentos.css">
</head>
<body>

<div class="app-container">
  <aside>
    <div class="title" id="instrumentos"><h1>DiscoGest</h1></div>
    <nav>
      <ul>
        <li class="secnav">Menu Principal</li>
        <li><a href="registro.php">Registar</a></li>
        <li><a href="listagemdiscos.php">Listar Discos</a></li>
        <li class="secnav">Intervenientes</li>
        <li><a href="artistas.php">Artistas</a></li>
        <li><a href="producao.php">Produção</a></li>
        <li class="secnav">Cadastros</li>
        <li><a href="#instrumentos" class="active">Instrumentos</a></li>
        <li><a href="generos.php">Géneros</a></li>
        <li class="secnav">Acções</li>
        <li><a href="#">Exportar</a></li>
        <li><a href="administracao.php">Administração</a></li>
        <li><a href="logs.php">Logs</a></li>
        <li><a href="LogOut.php">Sair</a></li>
      </ul>
    </nav>
  </aside>

  <div class="containermain">
    <header>
      <div class="pagetitle">
        <h2>Sistema de Gestão de Discos</h2>
        <p>Gestão de instrumentos musicais</p>
      </div>
      <div class="userdetails">
        <div class="userdetailstxt"><!-- PHP: nome do utilizador autenticado --><?= htmlspecialchars($utilizador->getNome()) ?><!-- PHP: perfil do utilizador --><p>Perfil</p></div>
        <img src="../resources/user.png" alt="Foto de Perfil">
      </div>
    </header>

    <main>
      <div class="card">
        <!-- Rádios das abas: irmãos directos de .card-header e dos .panel -->
        <input type="radio" name="tabs" id="tab-lista" class="tab-toggle" checked>
        <input type="radio" name="tabs" id="tab-cad" class="tab-toggle">

        <div class="card-header">
          <div class="tabs">
            <label for="tab-lista" class="tab-label">Instrumentos Cadastrados</label>
            <label for="tab-cad" class="tab-label">Cadastrar Instrumento</label>
          </div>
          <input type="search" class="search-input" placeholder="Pesquisar instrumento...">
        </div>

        <!-- ===== LISTA ===== -->
        <section class="panel panel-lista">
          <div class="table-wrapper">
            <table>
              <thead><tr><th class="col-sel"></th><th>Código</th><th>Nome do Instrumento</th></tr></thead>
              <tbody>
                <!-- PHP: repetir por linha (instrumentos: InstrumentoDAO::listarTodos) -->
                <tr><td><input type="radio" name="sel-instr" value="ID"></td><td>Código</td><td>Nome do Instrumento</td></tr>
              </tbody>
            </table>
          </div>
          <div class="table-actions">
            <label for="m-rem-instr" class="btn btn-secondary btn-sm">Remover</label>
            <label for="tab-cad" class="btn btn-primary btn-sm">Adicionar</label>
          </div>
        </section>

        <!-- ===== CADASTRAR ===== -->
        <section class="panel panel-cad">
          <!-- PHP: action para InstrumentoController::adicionarInstrumento() -->
          <form method="post">
            <div class="form-grid">
              <div class="label-group full">
                <label for="cad-nome">Nome do Instrumento *</label>
                <input type="text" id="cad-nome" name="nome" placeholder="Ex.: Guitarra" required>
              </div>
            </div>
            <div class="table-actions">
              <button class="btn btn-primary" type="submit" name="acao" value="salvar">Salvar Instrumento</button>
            </div>
          </form>
        </section>
      </div>
    </main>
  </div>
</div>

<!-- ============ MODAL: REMOVER INSTRUMENTO ============ -->
<input type="checkbox" id="m-rem-instr" class="modal-toggle">
<div class="modal-overlay"><div class="modal confirm">
  <div class="modal-header"><h2>Confirmar remoção</h2><label for="m-rem-instr" class="modal-close">&times;</label></div>
  <form method="post">
    <input type="hidden" name="id">
    <div class="modal-body">Tem certeza que deseja remover este instrumento? Esta ação não pode ser revertida.</div>
    <div class="modal-footer">
      <label for="m-rem-instr" class="btn btn-secondary">Não</label>
      <button class="btn btn-primary" type="submit" name="acao" value="remover">Sim</button>
    </div>
  </form>
</div></div>

</body>
</html>
