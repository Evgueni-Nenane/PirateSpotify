<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../model/instrumento.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/instrumentoController.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }

$instrController = new InstrumentoController();
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') {
            $erro = 'O nome do instrumento é obrigatório.';
        } else {
            $novo = $instrController->adicionarInstrumento(new Instrumento(null, $nome));
            $msg = $novo > 0 ? 'Instrumento adicionado com sucesso.' : 'Erro ao adicionar (já existe?).';
        }
    } elseif ($acao === 'remover') {
        $id = (int)($_POST['sel'] ?? 0);
        if ($id === 0) {
            $erro = 'Seleccione um instrumento.';
        } else {
            $msg = $instrController->remover($id)
                ? 'Instrumento removido.'
                : 'Não foi possível remover (o instrumento está associado a músicos).';
        }
    }
}

$instrumentos = $instrController->listarInstrumentos();
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
        <div class="userdetailstxt">
          <p><?= htmlspecialchars($utilizador->getNomeCompleto()) ?></p>
          <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
        </div>
        <img src="<?= ($utilizador->getFoto() ? '../resources/fotos/' . htmlspecialchars($utilizador->getFoto()) : '../resources/user.png') ?>" alt="Foto de Perfil">
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

        <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

        <!-- ===== LISTA ===== -->
        <section class="panel panel-lista">
          <form method="post" id="form-instr">
            <div class="table-wrapper">
              <table>
                <thead><tr><th class="col-sel"></th><th>Código</th><th>Nome do Instrumento</th></tr></thead>
                <tbody>
                  <?php foreach ($instrumentos as $i): ?>
                    <tr>
                      <td><input type="radio" name="sel" value="<?= $i->getCodigo() ?>"></td>
                      <td><?= $i->getCodigo() ?></td>
                      <td><?= htmlspecialchars($i->getNome()) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$instrumentos): ?>
                    <tr><td colspan="3">Nenhum instrumento cadastrado.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="table-actions">
              <label for="m-rem-instr" class="btn btn-secondary btn-sm">Remover</label>
              <label for="tab-cad" class="btn btn-primary btn-sm">Adicionar</label>
            </div>
          </form>
        </section>

        <!-- ===== CADASTRAR ===== -->
        <section class="panel panel-cad">
          <form method="post" action="instrumentos.php">
            <input type="hidden" name="acao" value="salvar">
            <div class="form-grid">
              <div class="label-group full">
                <label for="cad-nome">Nome do Instrumento *</label>
                <input type="text" id="cad-nome" name="nome" placeholder="Ex.: Guitarra" required>
              </div>
            </div>
            <div class="table-actions">
              <button class="btn btn-primary" type="submit">Salvar Instrumento</button>
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
  <div class="modal-body">Tem certeza que deseja remover este instrumento? Esta ação não pode ser revertida.</div>
  <div class="modal-footer">
    <label for="m-rem-instr" class="btn btn-secondary">Não</label>
    <button class="btn btn-primary" type="submit" form="form-instr" name="acao" value="remover">Sim</button>
  </div>
</div></div>

</body>
</html>
