<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/produtorController.php';
require_once __DIR__ . '/../../controller/gravadoraController.php';
require_once __DIR__ . '/../../controller/editoraController.php';
require_once __DIR__ . '/../../model/produtor.php';
require_once __DIR__ . '/../../model/gravadora.php';
require_once __DIR__ . '/../../model/editora.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }

$prodController = new ProdutorController();
$gravController = new GravadoraController();
$editController = new EditoraController();

$msg = '';
$edProd = $edGrav = $edEdit = null;

/* ---------- Acções (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';
    $acao = $_POST['acao'] ?? '';

    if ($tipo === 'produtor') {
        if ($acao === 'adicionar') {
            $novo = $prodController->cadastrarProdutor(new Produtor(
                null, trim($_POST['nome'] ?? ''), trim($_POST['apelido'] ?? ''),
                trim($_POST['contacto'] ?? ''), trim($_POST['email'] ?? '')
            ));
            $msg = $novo > 0 ? 'Produtor adicionado.' : 'Erro ao adicionar produtor.';
        } elseif ($acao === 'atualizar') {
            $p = new Produtor(
                (int)$_POST['id'], trim($_POST['nome'] ?? ''), trim($_POST['apelido'] ?? ''),
                trim($_POST['contacto'] ?? ''), trim($_POST['email'] ?? '')
            );
            $msg = $prodController->atualizarProdutor($p) ? 'Produtor atualizado.' : 'Sem alterações.';
        } elseif ($acao === 'remover') {
            $id = (int)($_POST['sel'] ?? 0);
            $msg = $id > 0 && $prodController->removerProdutor($id)
                ? 'Produtor removido.'
                : 'Seleccione um produtor que não esteja em discos.';
        }
    } elseif ($tipo === 'gravadora') {
        if ($acao === 'adicionar') {
            $novo = $gravController->cadastrarGravadora(new Gravadora(
                null, trim($_POST['nome'] ?? ''), trim($_POST['contacto'] ?? ''),
                trim($_POST['endereco'] ?? ''), trim($_POST['email'] ?? '')
            ));
            $msg = $novo > 0 ? 'Gravadora adicionada.' : 'Erro ao adicionar gravadora.';
        } elseif ($acao === 'atualizar') {
            $g = new Gravadora(
                (int)$_POST['id'], trim($_POST['nome'] ?? ''), trim($_POST['contacto'] ?? ''),
                trim($_POST['endereco'] ?? ''), trim($_POST['email'] ?? '')
            );
            $msg = $gravController->atualizarGravadora($g) ? 'Gravadora atualizada.' : 'Sem alterações.';
        } elseif ($acao === 'remover') {
            $id = (int)($_POST['sel'] ?? 0);
            $msg = $id > 0 && $gravController->removerGravadora($id)
                ? 'Gravadora removida.'
                : 'Seleccione uma gravadora que não esteja em discos.';
        }
    } elseif ($tipo === 'editora') {
        if ($acao === 'adicionar') {
            $novo = $editController->cadastrarEditora(new Editora(
                null, trim($_POST['nome'] ?? ''), trim($_POST['contacto'] ?? ''),
                trim($_POST['email'] ?? ''), trim($_POST['endereco'] ?? '')
            ));
            $msg = $novo > 0 ? 'Editora adicionada.' : 'Erro ao adicionar editora.';
        } elseif ($acao === 'atualizar') {
            $e = new Editora(
                (int)$_POST['id'], trim($_POST['nome'] ?? ''), trim($_POST['contacto'] ?? ''),
                trim($_POST['email'] ?? ''), trim($_POST['endereco'] ?? '')
            );
            $msg = $editController->atualizarEditora($e) ? 'Editora atualizada.' : 'Sem alterações.';
        } elseif ($acao === 'remover') {
            $id = (int)($_POST['sel'] ?? 0);
            $msg = $id > 0 && $editController->removerEditora($id)
                ? 'Editora removida.'
                : 'Seleccione uma editora que não esteja em discos.';
        }
    }

    header('Location: producao.php?msg=' . urlencode($msg));
    exit;
}

$msg = $_GET['msg'] ?? '';

/* ---------- Abrir o modal de edição preenchido ---------- */
$tab = 'prod';
if (($_GET['acao'] ?? '') === 'editar') {
    $sel  = (int)($_GET['sel'] ?? 0);
    $tipo = $_GET['tipo'] ?? '';

    if ($tipo === 'produtor') {
        $edProd = $prodController->buscarPorCodigo($sel);
        $tab = 'prod';
    } elseif ($tipo === 'gravadora') {
        $edGrav = $gravController->buscarPorCodigo($sel);
        $tab = 'grav';
    } elseif ($tipo === 'editora') {
        $edEdit = $editController->buscarPorCodigo($sel);
        $tab = 'edit';
    }
    if (!$edProd && !$edGrav && !$edEdit) $msg = 'Seleccione um registo para editar.';
}

$produtores = $prodController->listarProdutor();
$gravadoras = $gravController->listarGravadoras();
$editoras   = $editController->listarEditoras();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produção | Sistema de Gestão</title>
<link rel="stylesheet" href="../css/producao.css">
</head>
<body>

<div class="app-container">
  <aside>
    <div class="title" id="producao"><h1>DiscoGest</h1></div>
    <nav>
      <ul>
        <li class="secnav">Menu Principal</li>
        <li><a href="registro.php">Registar</a></li>
        <li><a href="listagemdiscos.php">Listar Discos</a></li>
        <li class="secnav">Intervenientes</li>
        <li><a href="artistas.php">Artistas</a></li>
        <li><a href="#producao" class="active">Produção</a></li>
        <li class="secnav">Cadastros</li>
        <li><a href="instrumentos.php">Instrumentos</a></li>
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
        <p>Gestão de produção</p>
      </div>
      <div class="userdetails">
        <div class="userdetailstxt">
          <p><?= htmlspecialchars($utilizador->getNome()) ?></p>
          <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
        </div>
        <img src="../resources/user.png" alt="Foto de Perfil">
      </div>
    </header>

    <main>
      <div class="card">
        <!-- Rádios das abas: irmãos directos de .card-header e dos .panel -->
        <input type="radio" name="tabs" id="tab-prod" class="tab-toggle" <?= $tab === 'prod' ? 'checked' : '' ?>>
        <input type="radio" name="tabs" id="tab-grav" class="tab-toggle" <?= $tab === 'grav' ? 'checked' : '' ?>>
        <input type="radio" name="tabs" id="tab-edit" class="tab-toggle" <?= $tab === 'edit' ? 'checked' : '' ?>>

        <div class="card-header">
          <div class="tabs">
            <label for="tab-prod" class="tab-label">Produtores</label>
            <label for="tab-grav" class="tab-label">Gravadoras</label>
            <label for="tab-edit" class="tab-label">Editoras</label>
          </div>
          <input type="search" class="search-input" placeholder="Pesquisar...">
        </div>

        <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <!-- ===== PRODUTORES ===== -->
        <section class="panel panel-prod">
          <form method="post" id="form-prod">
            <input type="hidden" name="tipo" value="produtor">
            <div class="table-wrapper">
              <table>
                <thead><tr><th class="col-sel"></th><th>Nome</th><th>Contacto</th><th>E-mail</th></tr></thead>
                <tbody>
                  <?php foreach ($produtores as $p): ?>
                    <tr>
                      <td><input type="radio" name="sel" value="<?= $p->getCodigoProdutor() ?>"></td>
                      <td><?= htmlspecialchars($p->getNomeCompleto()) ?></td>
                      <td><?= htmlspecialchars($p->getContactoProdutor()) ?></td>
                      <td><?= htmlspecialchars($p->getEmailProdutor()) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$produtores): ?><tr><td colspan="4">Nenhum produtor cadastrado.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="table-actions">
              <label for="m-rem-prod" class="btn btn-secondary btn-sm">Remover</label>
              <button type="submit" name="acao" value="editar" formmethod="get" class="btn btn-secondary btn-sm">Editar</button>
              <label for="m-add-prod" class="btn btn-primary btn-sm">Adicionar</label>
            </div>
          </form>
        </section>

        <!-- ===== GRAVADORAS ===== -->
        <section class="panel panel-grav">
          <form method="post" id="form-grav">
            <input type="hidden" name="tipo" value="gravadora">
            <div class="table-wrapper">
              <table>
                <thead><tr><th class="col-sel"></th><th>Nome</th><th>Contacto</th><th>E-mail</th><th>Endereço</th></tr></thead>
                <tbody>
                  <?php foreach ($gravadoras as $g): ?>
                    <tr>
                      <td><input type="radio" name="sel" value="<?= $g->getCodigoGravadora() ?>"></td>
                      <td><?= htmlspecialchars($g->getNomeGravadora()) ?></td>
                      <td><?= htmlspecialchars($g->getContactoGravadora()) ?></td>
                      <td><?= htmlspecialchars($g->getEmailGravadora()) ?></td>
                      <td><?= htmlspecialchars($g->getEnderecoGravadora()) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$gravadoras): ?><tr><td colspan="5">Nenhuma gravadora cadastrada.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="table-actions">
              <label for="m-rem-grav" class="btn btn-secondary btn-sm">Remover</label>
              <button type="submit" name="acao" value="editar" formmethod="get" class="btn btn-secondary btn-sm">Editar</button>
              <label for="m-add-grav" class="btn btn-primary btn-sm">Adicionar</label>
            </div>
          </form>
        </section>

        <!-- ===== EDITORAS ===== -->
        <section class="panel panel-edit">
          <form method="post" id="form-edit">
            <input type="hidden" name="tipo" value="editora">
            <div class="table-wrapper">
              <table>
                <thead><tr><th class="col-sel"></th><th>Nome</th><th>Contacto</th><th>E-mail</th><th>Endereço</th></tr></thead>
                <tbody>
                  <?php foreach ($editoras as $e): ?>
                    <tr>
                      <td><input type="radio" name="sel" value="<?= $e->getCodigoEditora() ?>"></td>
                      <td><?= htmlspecialchars($e->getNomeEditora()) ?></td>
                      <td><?= htmlspecialchars($e->getContactoEditora()) ?></td>
                      <td><?= htmlspecialchars($e->getEmailEditora()) ?></td>
                      <td><?= htmlspecialchars($e->getEndereco()) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$editoras): ?><tr><td colspan="5">Nenhuma editora cadastrada.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="table-actions">
              <label for="m-rem-edit" class="btn btn-secondary btn-sm">Remover</label>
              <button type="submit" name="acao" value="editar" formmethod="get" class="btn btn-secondary btn-sm">Editar</button>
              <label for="m-add-edit" class="btn btn-primary btn-sm">Adicionar</label>
            </div>
          </form>
        </section>
      </div>
    </main>
  </div>
</div>

<!-- ================= MODAIS: PRODUTOR ================= -->
<input type="checkbox" id="m-add-prod" class="modal-toggle">
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Cadastrar Produtor</h2><label for="m-add-prod" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="produtor">
  <input type="hidden" name="acao" value="adicionar">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group"><label for="ap-nome">Nome *</label><input id="ap-nome" name="nome" type="text" required></div>
    <div class="label-group"><label for="ap-apelido">Apelido *</label><input id="ap-apelido" name="apelido" type="text" required></div>
    <div class="label-group full"><label for="ap-contacto">Contacto</label><input id="ap-contacto" name="contacto" type="tel"></div>
    <div class="label-group full"><label for="ap-email">E-mail</label><input id="ap-email" name="email" type="email"></div>
  </div></div>
  <div class="modal-footer"><label for="m-add-prod" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Cadastrar</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-edit-prod" class="modal-toggle" <?= $edProd ? 'checked' : '' ?>>
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Editar Produtor</h2><label for="m-edit-prod" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="produtor">
  <input type="hidden" name="acao" value="atualizar">
  <input type="hidden" name="id" value="<?= $edProd ? $edProd->getCodigoProdutor() : '' ?>">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group"><label for="ep-nome">Nome</label><input id="ep-nome" name="nome" type="text" value="<?= htmlspecialchars($edProd ? $edProd->getNomeProdutor() : '') ?>" required></div>
    <div class="label-group"><label for="ep-apelido">Apelido</label><input id="ep-apelido" name="apelido" type="text" value="<?= htmlspecialchars($edProd ? $edProd->getApelidoProdutor() : '') ?>" required></div>
    <div class="label-group full"><label for="ep-contacto">Contacto</label><input id="ep-contacto" name="contacto" type="tel" value="<?= htmlspecialchars($edProd ? $edProd->getContactoProdutor() : '') ?>"></div>
    <div class="label-group full"><label for="ep-email">E-mail</label><input id="ep-email" name="email" type="email" value="<?= htmlspecialchars($edProd ? $edProd->getEmailProdutor() : '') ?>"></div>
  </div></div>
  <div class="modal-footer"><label for="m-edit-prod" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Salvar Alterações</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-rem-prod" class="modal-toggle">
<div class="modal-overlay"><div class="modal confirm">
  <div class="modal-header"><h2>Confirmar remoção</h2><label for="m-rem-prod" class="modal-close">&times;</label></div>
  <div class="modal-body">Tem certeza que deseja remover este produtor?</div>
  <div class="modal-footer"><label for="m-rem-prod" class="btn btn-secondary">Não</label><button class="btn btn-primary" type="submit" form="form-prod" name="acao" value="remover">Sim</button></div>
</div></div>

<!-- ================= MODAIS: GRAVADORA ================= -->
<input type="checkbox" id="m-add-grav" class="modal-toggle">
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Cadastrar Gravadora</h2><label for="m-add-grav" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="gravadora">
  <input type="hidden" name="acao" value="adicionar">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group full"><label for="ag-nome">Nome *</label><input id="ag-nome" name="nome" type="text" required></div>
    <div class="label-group full"><label for="ag-contacto">Contacto</label><input id="ag-contacto" name="contacto" type="tel"></div>
    <div class="label-group full"><label for="ag-email">E-mail</label><input id="ag-email" name="email" type="email"></div>
    <div class="label-group full"><label for="ag-endereco">Endereço</label><input id="ag-endereco" name="endereco" type="text"></div>
  </div></div>
  <div class="modal-footer"><label for="m-add-grav" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Cadastrar</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-edit-grav" class="modal-toggle" <?= $edGrav ? 'checked' : '' ?>>
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Editar Gravadora</h2><label for="m-edit-grav" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="gravadora">
  <input type="hidden" name="acao" value="atualizar">
  <input type="hidden" name="id" value="<?= $edGrav ? $edGrav->getCodigoGravadora() : '' ?>">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group full"><label for="eg-nome">Nome</label><input id="eg-nome" name="nome" type="text" value="<?= htmlspecialchars($edGrav ? $edGrav->getNomeGravadora() : '') ?>" required></div>
    <div class="label-group full"><label for="eg-contacto">Contacto</label><input id="eg-contacto" name="contacto" type="tel" value="<?= htmlspecialchars($edGrav ? $edGrav->getContactoGravadora() : '') ?>"></div>
    <div class="label-group full"><label for="eg-email">E-mail</label><input id="eg-email" name="email" type="email" value="<?= htmlspecialchars($edGrav ? $edGrav->getEmailGravadora() : '') ?>"></div>
    <div class="label-group full"><label for="eg-endereco">Endereço</label><input id="eg-endereco" name="endereco" type="text" value="<?= htmlspecialchars($edGrav ? $edGrav->getEnderecoGravadora() : '') ?>"></div>
  </div></div>
  <div class="modal-footer"><label for="m-edit-grav" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Salvar Alterações</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-rem-grav" class="modal-toggle">
<div class="modal-overlay"><div class="modal confirm">
  <div class="modal-header"><h2>Confirmar remoção</h2><label for="m-rem-grav" class="modal-close">&times;</label></div>
  <div class="modal-body">Tem certeza que deseja remover esta gravadora?</div>
  <div class="modal-footer"><label for="m-rem-grav" class="btn btn-secondary">Não</label><button class="btn btn-primary" type="submit" form="form-grav" name="acao" value="remover">Sim</button></div>
</div></div>

<!-- ================= MODAIS: EDITORA ================= -->
<input type="checkbox" id="m-add-edit" class="modal-toggle">
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Cadastrar Editora</h2><label for="m-add-edit" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="editora">
  <input type="hidden" name="acao" value="adicionar">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group full"><label for="ae-nome">Nome *</label><input id="ae-nome" name="nome" type="text" required></div>
    <div class="label-group full"><label for="ae-contacto">Contacto</label><input id="ae-contacto" name="contacto" type="tel"></div>
    <div class="label-group full"><label for="ae-email">E-mail</label><input id="ae-email" name="email" type="email"></div>
    <div class="label-group full"><label for="ae-endereco">Endereço</label><input id="ae-endereco" name="endereco" type="text"></div>
  </div></div>
  <div class="modal-footer"><label for="m-add-edit" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Cadastrar</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-edit-edit" class="modal-toggle" <?= $edEdit ? 'checked' : '' ?>>
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Editar Editora</h2><label for="m-edit-edit" class="modal-close">&times;</label></div>
  <form method="post" action="producao.php">
  <input type="hidden" name="tipo" value="editora">
  <input type="hidden" name="acao" value="atualizar">
  <input type="hidden" name="id" value="<?= $edEdit ? $edEdit->getCodigoEditora() : '' ?>">
  <div class="modal-body"><div class="form-grid">
    <div class="label-group full"><label for="ee-nome">Nome</label><input id="ee-nome" name="nome" type="text" value="<?= htmlspecialchars($edEdit ? $edEdit->getNomeEditora() : '') ?>" required></div>
    <div class="label-group full"><label for="ee-contacto">Contacto</label><input id="ee-contacto" name="contacto" type="tel" value="<?= htmlspecialchars($edEdit ? $edEdit->getContactoEditora() : '') ?>"></div>
    <div class="label-group full"><label for="ee-email">E-mail</label><input id="ee-email" name="email" type="email" value="<?= htmlspecialchars($edEdit ? $edEdit->getEmailEditora() : '') ?>"></div>
    <div class="label-group full"><label for="ee-endereco">Endereço</label><input id="ee-endereco" name="endereco" type="text" value="<?= htmlspecialchars($edEdit ? $edEdit->getEndereco() : '') ?>"></div>
  </div></div>
  <div class="modal-footer"><label for="m-edit-edit" class="btn btn-secondary">Cancelar</label><button class="btn btn-primary" type="submit">Salvar Alterações</button></div>
  </form>
</div></div>

<input type="checkbox" id="m-rem-edit" class="modal-toggle">
<div class="modal-overlay"><div class="modal confirm">
  <div class="modal-header"><h2>Confirmar remoção</h2><label for="m-rem-edit" class="modal-close">&times;</label></div>
  <div class="modal-body">Tem certeza que deseja remover esta editora?</div>
  <div class="modal-footer"><label for="m-rem-edit" class="btn btn-secondary">Não</label><button class="btn btn-primary" type="submit" form="form-edit" name="acao" value="remover">Sim</button></div>
</div></div>

</body>
</html>
