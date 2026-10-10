<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../model/permissao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/utilizadorController.php';
require_once __DIR__ . '/../../controller/logsController.php';
require_once __DIR__ . '/../../repository/nivelAcesso.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) {
  header('Location: Login.php');
  exit;
}

// Painel de utilizadores: só quem tem a permissão "utilizadores" entra aqui.
if (!Permissao::pode($utilizador, 'utilizadores')) {
  header('Location: listagemdiscos.php');
  exit;
}
$podeUsers = true;
$podeLogs  = Permissao::pode($utilizador, 'logs');

$ctrl = new UtilizadorController();
$nivelDAO = new NivelAcessoDAO();

// Guarda a foto enviada em ../resources/fotos/ e devolve o nome do ficheiro (ou null)
function guardarFoto($campo)
{
  if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;
  $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return null;
  $pasta = __DIR__ . '/../resources/fotos/';
  if (!is_dir($pasta)) mkdir($pasta, 0777, true);
  $nome = uniqid('u_') . '.' . $ext;
  move_uploaded_file($_FILES[$campo]['tmp_name'], $pasta . $nome);
  return $nome;
}

// Mostra "Sim" ou "Não" na tabela de perfis
function sn($valor)
{
  return $valor ? 'Sim' : 'Não';
}

$msg = '';
$tab = 'cad';

// Aba pedida na URL (a pesquisa envia a aba actual)
if (in_array($_GET['tab'] ?? '', ['cad', 'lista', 'perfis'], true)) {
  $tab = $_GET['tab'];
}

/* ---------- Cadastrar / Atualizar / Remover ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if ($_POST['acao'] === 'salvar') {
    $novo = new Utilizador(
      null,
      trim($_POST['nome']),
      trim($_POST['apelido']),
      trim($_POST['username']),
      $_POST['genero'],
      new NivelAcesso((int)$_POST['perfil'], ''),
      trim($_POST['email']),
      trim($_POST['telefone']),
      "PeixeFrito",
      true,   // primeiro acesso
      guardarFoto('foto')
    );
    $criado = $ctrl->cadastrarUtilizador($novo);
    if ($criado) {
      LogsController::registar('Criou o utilizador: ' . trim($_POST['nome']) . " " . trim($_POST['apelido']));
    }
    $msg = $criado
      ? 'Utilizador criado. '
      : 'Erro ao criar (o nome de utilizador já existe?).';
  }

  if ($_POST['acao'] === 'atualizar') {
    $tab   = 'lista';
    $id    = (int)$_POST['id'];
    $atual = $ctrl->listarPorId($id);
    $edit  = new Utilizador(
      null,
      null,
      null,
      null,
      null,
      new NivelAcesso((int)$_POST['perfil'], ''),
      trim($_POST['email']),
      trim($_POST['telefone']),
      null,
      null,
      guardarFoto('foto') ?? $atual->getFoto()
    );
    $ctrl->atualizarUser($id, $edit);
    LogsController::registar('Actualizou o utilizador: ' . $atual->getNomeCompleto());
    $msg = 'Utilizador atualizado.';
  }

  if ($_POST['acao'] === 'remover') {
    $tab = 'lista';
    $id  = (int)($_POST['sel'] ?? 0);
    if ($id === 0) {
      $msg = 'Seleccione um utilizador.';
    } elseif (($alvo = $ctrl->listarPorId($id)) && $alvo->getUser_name() === $utilizador->getUser_name()) {
      $msg = 'Não pode remover o seu próprio utilizador.';
    } else {
      $ctrl->suspenderUtilizador($id);
      LogsController::registar('Removeu o utilizador: ' . ($alvo ? $alvo->getNomeCompleto() : 'ID ' . $id)); //Econtrar um jeito melhor de colocar este log
      $msg = 'Utilizador removido.';
    }
  }

  if ($_POST['acao'] === 'resetar') {
    $tab = 'lista';
    $id  = (int)($_POST['sel'] ?? 0);
    if ($id === 0) {
      $msg = 'Seleccione um utilizador.';
    } else {
      $alvo = $ctrl->listarPorId($id);
      $nova = "Maguinhas123"; //VOU TROCAR
      $ctrl->resetarSenha($id, $nova);
      LogsController::registar('Resetou a senha do utilizador: ' . ($alvo ? $alvo->getNomeCompleto() : 'ID ' . $id)); //Econtrar um jeito melhor de colocar este log
      $msg = 'Senha resetada.';
    }
  }

  /* ----- Perfis ----- */
  if ($_POST['acao'] === 'criar_perfil') {
    $tab  = 'perfis';
    $nome = trim($_POST['nome_perfil'] ?? '');
    if ($nome === '') {
      $msg = 'O nome do perfil é obrigatório.';
    } elseif ($nivelDAO->criar($nome, $_POST['perm'] ?? [])) {
      LogsController::registar('Criou o perfil: ' . $nome);
      $msg = 'Perfil criado.';
    } else {
      $msg = 'Erro ao criar o perfil.';
    }
  }

  if ($_POST['acao'] === 'atualizar_perfil') {
    $tab  = 'perfis';
    $id   = (int)($_POST['id_perfil'] ?? 0);
    $nome = trim($_POST['nome_perfil'] ?? '');
    if ($nome === '') {
      $msg = 'O nome do perfil é obrigatório.';
    } elseif ($nivelDAO->atualizar($id, $nome, $_POST['perm'] ?? [])) {
      LogsController::registar('Actualizou o perfil: ' . $nome);
      $msg = 'Perfil atualizado.';
    } else {
      $msg = 'Não foi possível atualizar (os perfis base não se editam).';
    }
  }

  if ($_POST['acao'] === 'remover_perfil') {
    $tab = 'perfis';
    $id  = (int)($_POST['sel_perfil'] ?? 0);
    if ($id === 0) {
      $msg = 'Seleccione um perfil.';
    } elseif ($nivelDAO->remover($id)) {
      LogsController::registar('Removeu o perfil ID ' . $id);
      $msg = 'Perfil removido.';
    } else {
      $msg = 'Não foi possível remover (perfil base ou com utilizadores associados).';
    }
  }
}

/* ---------- Botão Editar: abre o modal preenchido ---------- */
$ed = null;
if (($_GET['acao'] ?? '') === 'editar') {
  $tab = 'lista';
  $ed  = $ctrl->listarPorId((int)($_GET['sel'] ?? 0));
  if (!$ed) $msg = 'Seleccione um utilizador.';
}

/* ---------- Botão Editar Perfil: abre o modal preenchido ---------- */
$edPerfil = null;
if (($_GET['acao'] ?? '') === 'editar_perfil') {
  $tab = 'perfis';
  $edPerfil = $nivelDAO->buscarPorCodigo((int)($_GET['sel_perfil'] ?? 0));
  if (!$edPerfil) {
    $msg = 'Seleccione um perfil.';
  } elseif ($edPerfil['CodigoNivel'] <= 4) {
    $msg = 'Os perfis base não se editam.';
    $edPerfil = null;
  }
}

$lista  = $ctrl->listarUtilizador();
$meuCodigo = 0;
foreach ($lista as $u) {
  if ($u->getUser_name() === $utilizador->getUser_name()) $meuCodigo = $u->getCodigo();
}
$fotoLogado = $utilizador->getFoto();
$niveis  = $nivelDAO->listarNiveis();
$perfis  = $nivelDAO->listarPerfis();

/* ---------- Pesquisa simples (filtra em memória as duas listas) ---------- */
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $qMin = mb_strtolower($q);

  $lista = array_values(array_filter($lista, function ($u) use ($qMin) {
    $alvo = mb_strtolower(
      $u->getCodigo() . ' ' . $u->getNomeCompleto() . ' ' . $u->getUser_name() . ' ' .
      $u->getEmail() . ' ' . $u->getGenero() . ' ' . $u->getPerfil()->getNome() . ' ' .
      $u->getContacto()
    );
    return str_contains($alvo, $qMin);
  }));

  $perfis = array_values(array_filter($perfis, function ($p) use ($qMin) {
    // Inclui o nome das permissões que o perfil tem (ex.: "logs", "editar")
    $perms = '';
    if ($p['PodeLer'])          $perms .= ' listar';
    if ($p['PodeAdicionar'])    $perms .= ' criar adicionar';
    if ($p['PodeEditar'])       $perms .= ' editar';
    if ($p['PodeRemover'])      $perms .= ' remover';
    if ($p['PodeUtilizadores']) $perms .= ' utilizadores gerir';
    if ($p['PodeLogs'])         $perms .= ' logs';
    $alvo = mb_strtolower($p['CodigoNivel'] . ' ' . $p['NomeNivel'] . $perms);
    return str_contains($alvo, $qMin);
  }));

  // Se a aba actual ficou sem resultados e a outra tem, muda para a outra
  if (!$ed && !$edPerfil) {
    if (($tab === 'lista' || $tab === 'cad') && !$lista && $perfis) $tab = 'perfis';
    elseif (($tab === 'perfis' || $tab === 'cad') && !$perfis && $lista) $tab = 'lista';
  }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administração | Sistema de Gestão</title>
  <link rel="stylesheet" href="../css/administracao.css">
  <link rel="stylesheet" href="../css/registro.css">
</head>

<body>

  <div class="app-container">
    <aside>
      <div class="title" id="administracao">
        <h1>DiscoGest</h1>
      </div>
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
          <li><a href="#administracao" class="active">Administração</a></li>
          <?php if ($podeLogs): ?><li><a href="logs.php">Logs</a></li><?php endif; ?>
          <li><a href="LogOut.php">Sair</a></li>
        </ul>
      </nav>
    </aside>

    <div class="containermain">
      <header>
        <div class="pagetitle">
          <h2>Sistema de Gestão de Discos</h2>
          <p>Administração de utilizadores</p>
        </div>
        <div class="userdetails">
          <div class="userdetailstxt">
            <p><?= htmlspecialchars($utilizador->getNomeCompleto()) ?></p>
            <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
          </div>
          <img src="<?= $fotoLogado ? '../resources/fotos/' . htmlspecialchars($fotoLogado) : '../resources/user.png' ?>" alt="Foto de Perfil">
        </div>
      </header>

      <main>
        <div class="card">
          <!-- Formulário GET da pesquisa (o input liga-se a ele via form="form-pesq").
               O campo escondido mantém a aba actual. -->
          <form method="get" action="administracao.php" id="form-pesq">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
          </form>

          <input type="radio" name="tabs" id="tab-cad" class="tab-toggle" <?= $tab === 'cad' ? 'checked' : '' ?>>
          <input type="radio" name="tabs" id="tab-lista" class="tab-toggle" <?= $tab === 'lista' ? 'checked' : '' ?>>
          <input type="radio" name="tabs" id="tab-perfis" class="tab-toggle" <?= $tab === 'perfis' ? 'checked' : '' ?>>

          <div class="card-header">
            <div class="tabs">
              <label for="tab-cad" class="tab-label">Cadastrar Utilizador</label>
              <label for="tab-lista" class="tab-label">Informações de Utilizador</label>
              <label for="tab-perfis" class="tab-label">Perfis</label>
            </div>
            <input type="search" class="search-input" name="q" form="form-pesq"
              value="<?= htmlspecialchars($q) ?>" placeholder="Pesquisar utilizador ou perfil...">
          </div>

          <?php if ($msg): ?>
            <p><?= htmlspecialchars($msg) ?></p>
          <?php endif; ?>

          <!-- ===== CADASTRAR ===== -->
          <section class="panel panel-cad">
            <form method="post" action="administracao.php" enctype="multipart/form-data">
              <input type="hidden" name="acao" value="salvar">
              <div class="cadastro-layout">
                <div class="foto-user">
                  <div class="foto-preview"><img id="cad-preview" src="../resources/user.png" alt="Foto do Utilizador"></div>
                  <div class="foto-actions">
                    <input type="file" id="cad-foto" name="foto" accept="image/*" class="file-hidden"
                      onchange="document.getElementById('cad-preview').src = window.URL.createObjectURL(this.files[0])">
                    <label for="cad-foto" class="btn btn-primary btn-sm">Escolher Foto</label>
                  </div>
                </div>

                <div class="form-grid">
                  <div class="label-group"><label for="cad-nome">Primeiro Nome *</label><input type="text" id="cad-nome" name="nome" required></div>
                  <div class="label-group"><label for="cad-apelido">Último Nome *</label><input type="text" id="cad-apelido" name="apelido" required></div>
                  <div class="label-group">
                    <label for="cad-genero">Género</label>
                    <select id="cad-genero" name="genero">
                      <option value="MASCULINO">Masculino</option>
                      <option value="FEMININO">Feminino</option>
                    </select>
                  </div>
                  <div class="label-group">
                    <label for="cad-perfil">Perfil do Utilizador</label>
                    <select id="cad-perfil" name="perfil">
                      <?php foreach ($niveis as $n): ?>
                        <option value="<?= $n->getCodigoNivel() ?>"><?= htmlspecialchars($n->getNome()) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="label-group full"><label for="cad-username">Nome de Utilizador *</label><input type="text" id="cad-username" name="username" required></div>
                  <div class="label-group full"><label for="cad-email">E-mail Corporativo</label><input type="email" id="cad-email" name="email"></div>
                  <div class="label-group full"><label for="cad-tel">Número de Telemóvel</label><input type="tel" id="cad-tel" name="telefone"></div>
                </div>
              </div>
              <div class="table-actions">
                <button class="btn btn-primary" type="submit">Salvar Utilizador</button>
              </div>
            </form>
          </section>

          <!-- ===== LISTA ===== -->
          <section class="panel panel-lista">
            <form method="post" action="administracao.php">
              <div class="table-wrapper">
                <table>
                  <thead>
                    <tr>
                      <th class="col-sel"></th>
                      <th>Código</th>
                      <th>Nome Completo</th>
                      <th>E-mail Corporativo</th>
                      <th>Género</th>
                      <th>Perfil</th>
                      <th>Telefone</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lista as $u): ?>
                      <tr>
                        <td><input type="radio" name="sel" value="<?= $u->getCodigo() ?>"></td>
                        <td><?= $u->getCodigo() ?></td>
                        <td><?= htmlspecialchars($u->getNomeCompleto()) ?></td>
                        <td><?= htmlspecialchars($u->getEmail()) ?></td>
                        <td><?= htmlspecialchars($u->getGenero()) ?></td>
                        <td><?= htmlspecialchars($u->getPerfil()->getNome()) ?></td>
                        <td><?= htmlspecialchars($u->getContacto()) ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (!$lista): ?>
                      <tr>
                        <td colspan="7"><?= $q !== '' ? 'Nenhum utilizador encontrado.' : 'Nenhum utilizador cadastrado.' ?></td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="table-actions">
                <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="remover">Remover Seleccionado</button>
                <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="resetar">Resetar Senha</button>
                <button class="btn btn-primary btn-sm" type="submit" name="acao" value="editar" formmethod="get">Editar Utilizador</button>
              </div>
            </form>
          </section>

          <!-- ===== PERFIS ===== -->
          <section class="panel panel-perfis">
            <!-- Criar perfil novo -->
            <form method="post" action="administracao.php">
              <input type="hidden" name="acao" value="criar_perfil">
              <div class="form-grid">
                <div class="label-group full">
                  <label for="perfil-nome">Nome do Novo Perfil *</label>
                  <input type="text" id="perfil-nome" name="nome_perfil" required>
                </div>
                <div class="label-group full">
                  <label>Permissões</label>
                  <div class="perm-grid">
                    <label class="perm-check"><input type="checkbox" checked disabled> Listar</label>
                    <label class="perm-check"><input type="checkbox" name="perm[adicionar]" value="1"> Criar</label>
                    <label class="perm-check"><input type="checkbox" name="perm[editar]" value="1"> Editar</label>
                    <label class="perm-check"><input type="checkbox" name="perm[remover]" value="1"> Remover</label>
                    <label class="perm-check"><input type="checkbox" name="perm[utilizadores]" value="1"> Gerir utilizadores</label>
                    <label class="perm-check"><input type="checkbox" name="perm[logs]" value="1"> Ver logs</label>
                  </div>
                </div>
              </div>
              <div class="table-actions">
                <button class="btn btn-primary btn-sm" type="submit">Salvar Perfil</button>
              </div>
            </form>

            <!-- Lista de perfis -->
            <form method="post" action="administracao.php">
              <div class="table-wrapper">
                <table>
                  <thead>
                    <tr>
                      <th class="col-sel"></th>
                      <th>Código</th>
                      <th>Perfil</th>
                      <th>Listar</th>
                      <th>Criar</th>
                      <th>Editar</th>
                      <th>Remover</th>
                      <th>Gerir Utilizadores</th>
                      <th>Logs</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($perfis as $p): ?>
                      <tr>
                        <td><input type="radio" name="sel_perfil" value="<?= $p['CodigoNivel'] ?>"></td>
                        <td><?= $p['CodigoNivel'] ?></td>
                        <td><?= htmlspecialchars($p['NomeNivel']) ?></td>
                        <td><?= sn($p['PodeLer']) ?></td>
                        <td><?= sn($p['PodeAdicionar']) ?></td>
                        <td><?= sn($p['PodeEditar']) ?></td>
                        <td><?= sn($p['PodeRemover']) ?></td>
                        <td><?= sn($p['PodeUtilizadores']) ?></td>
                        <td><?= sn($p['PodeLogs']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (!$perfis): ?>
                      <tr>
                        <td colspan="9"><?= $q !== '' ? 'Nenhum perfil encontrado.' : 'Nenhum perfil cadastrado.' ?></td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="table-actions">
                <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="remover_perfil">Remover Perfil</button>
                <button class="btn btn-primary btn-sm" type="submit" name="acao" value="editar_perfil" formmethod="get">Editar Perfil</button>
              </div>
            </form>
          </section>
        </div>
      </main>
    </div>
  </div>

  <!-- ============ MODAL: EDITAR UTILIZADOR ============ -->
  <input type="checkbox" id="m-edit-user" class="modal-toggle" <?= $ed ? 'checked' : '' ?>>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>Editar Utilizador</h2><label for="m-edit-user" class="modal-close">&times;</label>
      </div>
      <form method="post" action="administracao.php" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="atualizar">
        <input type="hidden" name="id" value="<?= $ed ? $ed->getCodigo() : '' ?>">
        <div class="modal-body">
          <div class="cadastro-layout sm">
            <div class="foto-user">
              <div class="foto-preview">
                <img id="eu-preview" src="<?= ($ed && $ed->getFoto()) ? '../resources/fotos/' . htmlspecialchars($ed->getFoto()) : '../resources/user.png' ?>" alt="Foto do Utilizador">
              </div>
              <div class="foto-actions">
                <input type="file" id="edit-foto" name="foto" accept="image/*" class="file-hidden"
                  onchange="document.getElementById('eu-preview').src = window.URL.createObjectURL(this.files[0])">
                <label for="edit-foto" class="btn btn-primary btn-sm">Escolher Foto</label>
              </div>
            </div>
            <div class="form-grid">
              <div class="label-group"><label>Primeiro Nome</label><input type="text" value="<?= htmlspecialchars($ed ? $ed->getNome() : '') ?>" readonly></div>
              <div class="label-group"><label>Último Nome</label><input type="text" value="<?= htmlspecialchars($ed ? $ed->getApelido() : '') ?>" readonly></div>
              <div class="label-group full">
                <label for="eu-perfil">Perfil</label>
                <select id="eu-perfil" name="perfil">
                  <?php foreach ($niveis as $n): ?>
                    <option value="<?= $n->getCodigoNivel() ?>" <?= ($ed && $ed->getPerfil()->getCodigoNivel() == $n->getCodigoNivel()) ? 'selected' : '' ?>><?= htmlspecialchars($n->getNome()) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="label-group full"><label for="eu-email">E-mail Corporativo</label><input type="email" id="eu-email" name="email" value="<?= htmlspecialchars($ed ? $ed->getEmail() : '') ?>"></div>
              <div class="label-group full"><label for="eu-tel">Número de Telemóvel</label><input type="tel" id="eu-tel" name="telefone" value="<?= htmlspecialchars($ed ? $ed->getContacto() : '') ?>"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <label for="m-edit-user" class="btn btn-secondary">Cancelar</label>
          <button class="btn btn-primary" type="submit">Atualizar Utilizador</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ============ MODAL: EDITAR PERFIL ============ -->
  <input type="checkbox" id="m-edit-perfil" class="modal-toggle" <?= $edPerfil ? 'checked' : '' ?>>
  <div class="modal-overlay">
    <div class="modal">
      <div class="modal-header">
        <h2>Editar Perfil</h2><label for="m-edit-perfil" class="modal-close">&times;</label>
      </div>
      <form method="post" action="administracao.php">
        <input type="hidden" name="acao" value="atualizar_perfil">
        <input type="hidden" name="id_perfil" value="<?= $edPerfil ? $edPerfil['CodigoNivel'] : '' ?>">
        <div class="modal-body">
          <div class="form-grid">
            <div class="label-group full">
              <label for="ep-nome">Nome do Perfil *</label>
              <input type="text" id="ep-nome" name="nome_perfil" required value="<?= htmlspecialchars($edPerfil ? $edPerfil['NomeNivel'] : '') ?>">
            </div>
            <div class="label-group full">
              <label>Permissões</label>
              <div class="perm-grid">
                <label class="perm-check"><input type="checkbox" checked disabled> Listar</label>
                <label class="perm-check"><input type="checkbox" name="perm[adicionar]" value="1" <?= ($edPerfil && $edPerfil['PodeAdicionar']) ? 'checked' : '' ?>> Criar</label>
                <label class="perm-check"><input type="checkbox" name="perm[editar]" value="1" <?= ($edPerfil && $edPerfil['PodeEditar']) ? 'checked' : '' ?>> Editar</label>
                <label class="perm-check"><input type="checkbox" name="perm[remover]" value="1" <?= ($edPerfil && $edPerfil['PodeRemover']) ? 'checked' : '' ?>> Remover</label>
                <label class="perm-check"><input type="checkbox" name="perm[utilizadores]" value="1" <?= ($edPerfil && $edPerfil['PodeUtilizadores']) ? 'checked' : '' ?>> Gerir utilizadores</label>
                <label class="perm-check"><input type="checkbox" name="perm[logs]" value="1" <?= ($edPerfil && $edPerfil['PodeLogs']) ? 'checked' : '' ?>> Ver logs</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <label for="m-edit-perfil" class="btn btn-secondary">Cancelar</label>
          <button class="btn btn-primary" type="submit">Atualizar Perfil</button>
        </div>
      </form>
    </div>
  </div>

</body>

</html>