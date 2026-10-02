<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/utilizadorController.php';
require_once __DIR__ . '/../../repository/nivelAcesso.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }

$ctrl = new UtilizadorController();
$nivelDAO = new NivelAcessoDAO();

// Guarda a foto enviada em ../resources/fotos/ e devolve o nome do ficheiro (ou null)
function guardarFoto($campo) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return null;
    $pasta = __DIR__ . '/../resources/fotos/';
    if (!is_dir($pasta)) mkdir($pasta, 0777, true);
    $nome = uniqid('u_') . '.' . $ext;
    move_uploaded_file($_FILES[$campo]['tmp_name'], $pasta . $nome);
    return $nome;
}

$msg = '';
$tab = 'cad';

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
            $_POST['senha'],
            true,   // primeiro acesso
            guardarFoto('foto')
        );
        $msg = $ctrl->cadastrarUtilizador($novo)
            ? 'Utilizador criado. Senha inicial: ' . $_POST['senha']
            : 'Erro ao criar (o nome de utilizador já existe?).';
    }

    if ($_POST['acao'] === 'atualizar') {
        $tab   = 'lista';
        $id    = (int)$_POST['id'];
        $atual = $ctrl->listarPorId($id);
        $edit  = new Utilizador(null, null, null, null, null,
            new NivelAcesso((int)$_POST['perfil'], ''),
            trim($_POST['email']), trim($_POST['telefone']), null, null,
            guardarFoto('foto') ?? $atual->getFoto());
        $ctrl->atualizarUser($id, $edit);
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
            $msg = 'Utilizador removido.';
        }
    }

    if ($_POST['acao'] === 'resetar') {
        $tab = 'lista';
        $id  = (int)($_POST['sel'] ?? 0);
        if ($id === 0) {
            $msg = 'Seleccione um utilizador.';
        } else {
            $nova = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 8); //VOU TROCAR
            $ctrl->resetarSenha($id, $nova);
            $msg = 'Senha resetada. Nova senha: ' . $nova;
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

$lista  = $ctrl->listarUtilizador();
$meuCodigo = 0;
foreach ($lista as $u) {
    if ($u->getUser_name() === $utilizador->getUser_name()) $meuCodigo = $u->getCodigo();
}
$fotoLogado = $ctrl->buscarFoto($meuCodigo);
$niveis = $nivelDAO->listarNiveis();
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
    <div class="title" id="administracao"><h1>DiscoGest</h1></div>
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
        <li><a href="logs.php">Logs</a></li>
        <li><a href="logout.php">Sair</a></li>
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
          <?= htmlspecialchars($utilizador->getNome()) ?>
          <p>Perfil</p>
        </div>
        <img src="<?= $fotoLogado ? '../resources/fotos/' . htmlspecialchars($fotoLogado) : '../resources/user.png' ?>" alt="Foto de Perfil">
      </div>
    </header>

    <main>
      <div class="card">
        <input type="radio" name="tabs" id="tab-cad" class="tab-toggle" <?= $tab === 'cad' ? 'checked' : '' ?>>
        <input type="radio" name="tabs" id="tab-lista" class="tab-toggle" <?= $tab === 'lista' ? 'checked' : '' ?>>

        <div class="card-header">
          <div class="tabs">
            <label for="tab-cad" class="tab-label">Cadastrar Utilizador</label>
            <label for="tab-lista" class="tab-label">Informações de Utilizador</label>
          </div>
        </div>

        <?php if ($msg): ?>
          <div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div>
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
                  <select id="cad-genero" name="genero"><option value="MASCULINO">Masculino</option><option value="FEMININO">Feminino</option></select>
                </div>
                <div class="label-group">
                  <label for="cad-perfil">Permissões de Utilizador</label>
                  <select id="cad-perfil" name="perfil">
                    <?php foreach ($niveis as $n): ?>
                      <option value="<?= $n->getCodigoNivel() ?>"><?= htmlspecialchars($n->getNome()) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="label-group full"><label for="cad-username">Nome de Utilizador *</label><input type="text" id="cad-username" name="username" required></div>
                <div class="label-group full"><label for="cad-email">E-mail Corporativo</label><input type="email" id="cad-email" name="email"></div>
                <div class="label-group full"><label for="cad-tel">Número de Telemóvel</label><input type="tel" id="cad-tel" name="telefone"></div>
                <div class="label-group full">
                  <label for="cad-senha">Password Inicial *</label>
                  <div class="inline">
                    <input type="text" id="cad-senha" name="senha" readonly required placeholder="Clique em Gerar Senha">
                    <button class="btn btn-secondary btn-sm" type="button"
                            onclick="gerarSenha()">Gerar Senha</button>
                  </div>
                </div>
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
                <thead><tr><th class="col-sel"></th><th>Código</th><th>Nome Completo</th><th>E-mail Corporativo</th><th>Género</th><th>Permissões</th><th>Telefone</th></tr></thead>
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
      </div>
    </main>
  </div>
</div>

<!-- ============ MODAL: EDITAR UTILIZADOR ============ -->
<input type="checkbox" id="m-edit-user" class="modal-toggle" <?= $ed ? 'checked' : '' ?>>
<div class="modal-overlay"><div class="modal">
  <div class="modal-header"><h2>Editar Utilizador</h2><label for="m-edit-user" class="modal-close">&times;</label></div>
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
          <label for="eu-perfil">Permissões</label>
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
</div></div>


<script>
// Senha = primeira letra + última letra do nome de utilizador + 258
function gerarSenha() {
  var u = document.getElementById('cad-username').value.trim().toLowerCase();
  if (u === '') { alert('Escreva primeiro o nome de utilizador.'); return; }
  document.getElementById('cad-senha').value = u.charAt(0) + u.charAt(u.length - 1) + '258';
}
</script>
</body>
</html>