<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../repository/disco.php';
require_once __DIR__ . '/../../repository/generorepository.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) {
  header('Location: Login.php');
  exit;
}
$username = $utilizador->getUser_name();
$generoDAO = new GeneroDAO();
$generos   = $generoDAO->listarTodos();

$msg = '';
$erro = '';

// Mensagem de sucesso vem do redirect (padrão POST-Redirect-GET)
if (isset($_GET['ok'])) {
  $msg = 'Disco registado com sucesso';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  $preco  = ($_POST['preco'] ?? '') !== '' ? (float)$_POST['preco'] : 0;
  // o calendário envia AAAA-MM-DD; o modelo guarda só o ano
  $ano    = ($_POST['ano_edicao'] ?? '') !== '' ? (int)substr($_POST['ano_edicao'], 0, 4) : null;


  if ($titulo === '') {
    $erro = 'O título é obrigatório.';
  } else {
    $disco = new DiscoCompacto(0, $titulo, $preco, $ano, [], [], [], [], [], [], [], null, []);
    $dao = new DiscoDAO();
    $codigo = $dao->inserir($disco);

    if ($codigo > 0) {
      $codGenero = (int)($_POST['genero'] ?? 0);
      if ($codGenero > 0) {
        $generoDAO->inserirRelacaoGeneroDisco($codigo, $codGenero);
      }
      // redireciona: o formulário volta vazio e o F5 não regista outra vez
      header('Location: registro.php?ok=' . $codigo);
      exit;
    } else {
      $erro = 'Erro ao registar o disco.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registar Disco | Sistema de Gestão</title>
  <link rel="stylesheet" href="../css/administracao.css">
  <link rel="stylesheet" href="../css/registro.css">
</head>

<body>

  <div class="app-container">
    <aside>
      <div class="title">
        <h1>DiscoGest</h1>
      </div>
      <nav>
        <ul>
          <li class="secnav">Menu Principal</li>
          <li><a href="registro.php" class="active">Registar</a></li>
          <li><a href="listagemdiscos.php">Listar Discos</a></li>
          <li class="secnav">Intervenientes</li>
          <li><a href="artistas.php">Artistas</a></li>
          <li><a href="producao.php">Produção</a></li>
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
          <p>Registar disco</p>
        </div>
        <div class="userdetails">
          <div class="userdetailstxt">
            <?= htmlspecialchars($utilizador->getNome()) ?>
            <p>Perfil</p>
          </div>
          <img src="../resources/user.png" alt="Foto de Perfil">
        </div>
      </header>

      <main>
        <div class="card">
          <div class="card-header">
            <div class="tabs"><span class="tab-label active">Novo Disco</span></div>
          </div>

          <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
          <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

          <form method="post">
            <div class="form-grid">
              <div class="label-group full">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" maxlength="150" required
                  value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
              </div>
              <div class="label-group">
                <label for="ano_edicao">Data de Edição</label>
                <input type="date" id="ano_edicao" name="ano_edicao" min="1900-01-01" max="<?= date('Y-m-d') ?>"
                  value="<?= htmlspecialchars($_POST['ano_edicao'] ?? '') ?>">
              </div>
              <div class="label-group">
                <label for="preco">Preço</label>
                <input type="number" id="preco" name="preco" min="0" step="0.01"
                  value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>">
              </div>

              <div class="label-group">
                <label for="genero">Género *</label>
                <select id="genero" name="genero" required>
                  <option value="">Seleccione...</option>
                  <?php foreach ($generos as $g): ?>
                    <option value="<?= $g->getCodigoGenero() ?>"><?= htmlspecialchars($g->getNomeGenero()) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="table-actions">
              <button class="btn btn-secondary" type="reset">Limpar</button>
              <button class="btn btn-primary" type="submit">Registar Disco</button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

</body>

</html>