<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../model/permissao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../repository/disco.php';
require_once __DIR__ . '/../../repository/generorepository.php';
require_once __DIR__ . '/../../repository/compositor.php';
require_once __DIR__ . '/../../repository/musico.php';
require_once __DIR__ . '/../../repository/cantor.php';
require_once __DIR__ . '/../../repository/produtor.php';
require_once __DIR__ . '/../../repository/gravadora.php';
require_once __DIR__ . '/../../repository/editora.php';
require_once __DIR__ . '/../../repository/edicao.php';
require_once __DIR__ . '/../../controller/logsController.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) {
  header('Location: Login.php');
  exit;
}
$podeAdicionar = Permissao::pode($utilizador, 'adicionar');
$podeUsers     = Permissao::pode($utilizador, 'utilizadores');
$podeLogs      = Permissao::pode($utilizador, 'logs');

$discoDAO      = new DiscoDAO();
$generoDAO     = new GeneroDAO();
$compositorDAO = new CompositorDAO();
$musicoDAO     = new MusicoDAO();
$cantorDAO     = new CantorDAO();
$produtorDAO   = new ProdutorDAO();
$gravadoraDAO  = new GravadoraDAO();
$editoraDAO    = new EditoraDAO();
$edicaoDAO     = new EdicaoDAO();

$msg  = '';
$erro = '';

// Mensagem de sucesso vem do redirect (padrão POST-Redirect-GET)
if (isset($_GET['ok'])) {
  $msg = 'Disco registado com sucesso';
}

/* ---------- Registar o disco (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($bloq = Permissao::bloquear($utilizador, 'adicionar'))) {
    $erro = $bloq;
  } else {
    $titulo     = trim($_POST['titulo'] ?? '');
    $preco      = ($_POST['preco'] ?? '') !== '' ? (float)$_POST['preco'] : 0;
    $dataEdicao = $_POST['ano_edicao'] ?? '';
    $ano        = $dataEdicao !== '' ? (int)substr($dataEdicao, 0, 4) : null;   // o disco guarda só o ano
    $codEditora = (int)($_POST['editora'] ?? 0);

    if ($titulo === '') {
      $erro = 'O título é obrigatório.';
    } elseif (empty($_POST['generos'])) {
      $erro = 'Escolha pelo menos um género.';
    } elseif ($codEditora > 0 && $dataEdicao === '') {
      $erro = 'Escolha a data de edição para associar a editora.';
    } else {
      $disco  = new DiscoCompacto(0, $titulo, $preco, $ano, [], [], [], [], [], [], [], null, []);
      $codigo = $discoDAO->inserir($disco);

      if ($codigo > 0) {
        // género(s)
        foreach ($_POST['generos'] as $g) $generoDAO->inserirRelacaoGeneroDisco($codigo, (int)$g);

        // participantes
        foreach (($_POST['compositores'] ?? []) as $c) $compositorDAO->inserirRelacaoDiscoCompositor($codigo, (int)$c);
        foreach (($_POST['musicos'] ?? [])      as $m) $musicoDAO->inserirRelacaoDiscoMusico($codigo, (int)$m);
        foreach (($_POST['cantores'] ?? [])     as $c) $cantorDAO->inserirRelacaoDiscoCantor($codigo, (int)$c);

        // produção
        foreach (($_POST['produtores'] ?? []) as $p) $produtorDAO->inserirRelacaoDiscoProdutor($codigo, (int)$p);
        foreach (($_POST['gravadoras'] ?? []) as $g) $gravadoraDAO->inserirRelacaoDiscoGravadora($codigo, (int)$g);
        if ($codEditora > 0) $edicaoDAO->inserir(new Edicao($codigo, $codEditora, $dataEdicao));

        if ($codEditora > 0) $edicaoDAO->inserir(new Edicao($codigo, $codEditora, $dataEdicao));

        LogsController::registar('Registou o disco: ' . $titulo);

        // redireciona: o formulário volta vazio e o F5 não regista outra vez
        header('Location: registro.php?ok=' . $codigo);
        exit;
      } else {
        $erro = 'Erro ao registar o disco.';
      }
    }
  }
}

/* ---------- Listas para os modais (código => texto) ---------- */
$generos = [];
foreach ($generoDAO->listarTodos() as $g) $generos[$g->getCodigoGenero()] = $g->getNomeGenero();

$compositores = [];
foreach ($compositorDAO->listarTodos() as $c) $compositores[$c->getCodigoCompositor()] = $c->getNomeCompositor() . ' ' . $c->getApelidoCompositor();

$musicos = [];
foreach ($musicoDAO->listarTodos() as $m) $musicos[$m->getCodigoMusico()] = $m->getNomeMusico() . ' ' . $m->getApelidoMusico();

$cantores = [];
foreach ($cantorDAO->listarTodos() as $c) $cantores[$c->getCodigoCantor()] = $c->getNomeCantor() . ' ' . $c->getApelidoCantor();

$produtores = [];
foreach ($produtorDAO->listarTodos() as $p) $produtores[$p->getCodigoProdutor()] = $p->getNomeProdutor() . ' ' . $p->getApelidoProdutor();

$gravadoras = [];
foreach ($gravadoraDAO->listarTodos() as $g) $gravadoras[$g->getCodigoGravadora()] = $g->getNomeGravadora();

$editoras = [];
foreach ($editoraDAO->listarTodos() as $e) $editoras[$e->getCodigoEditora()] = $e->getNomeEditora();

// Desenha as linhas de uma lista. Os inputs usam form="form-registo" porque
// os modais ficam fora do <form>, mas os valores têm de ir com ele.
function itens($campo, $lista, $contador, $radio = false)
{
  foreach ($lista as $codigo => $texto) {
    $id   = $campo . $codigo;
    $tipo = $radio ? 'radio' : 'checkbox';
    $nome = $radio ? $campo : $campo . '[]';
    echo '<div class="list-item">';
    echo '<input type="' . $tipo . '" name="' . $nome . '" id="' . $id . '" value="' . $codigo . '"';
    echo ' form="form-registo" data-nome="' . htmlspecialchars($texto) . '" onchange="contar(this, \'' . $contador . '\')">';
    echo '<label for="' . $id . '">' . htmlspecialchars($texto) . '</label>';
    echo '</div>';
  }
  if (!$lista) echo '<div class="list-item text-muted">Sem registos.</div>';
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registar Disco | Sistema de Gestão</title>
  <link rel="stylesheet" href="../css/registro.css">
</head>

<body>

  <!-- Controlos dos modais (checkbox hack) -->
  <input type="checkbox" id="modal-generos-toggle" class="modal-toggle">
  <input type="checkbox" id="modal-participantes-toggle" class="modal-toggle">
  <input type="checkbox" id="modal-producao-toggle" class="modal-toggle">

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
          <?php if ($podeUsers): ?><li><a href="administracao.php">Administração</a></li><?php endif; ?>
          <?php if ($podeLogs): ?><li><a href="logs.php">Logs</a></li><?php endif; ?>
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
            <p><?= htmlspecialchars($utilizador->getNomeCompleto()) ?></p>
            <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
          </div>
          <img src="<?= ($utilizador->getFoto() ? '../resources/fotos/' . htmlspecialchars($utilizador->getFoto()) : '../resources/user.png') ?>" alt="Foto de Perfil">
        </div>
      </header>

      <main>
        <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

        <form method="post" action="registro.php" id="form-registo" class="maincontent">

          <!-- ===== Dados do disco ===== -->
          <div class="card">
            <h3>Informações do Disco</h3>
            <div class="form-grid">
              <div class="label-group form-grid-full">
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
            </div>
          </div>

          <!-- ===== Painéis: abrem os modais ===== -->
          <div class="credits-grid">

            <div class="credit-panel">
              <h4>Géneros *</h4>
              <label for="modal-generos-toggle" class="btn-select">Selecionar géneros</label>
              <p class="selection-info"><strong id="n-generos">0</strong> selecionado(s)</p>
            </div>

            <div class="credit-panel">
              <h4>Participantes</h4>
              <label for="modal-participantes-toggle" class="btn-select">Selecionar participantes</label>
              <ul>
                <li>Compositores: <span id="n-compositores">0</span></li>
                <li>Músicos: <span id="n-musicos">0</span></li>
                <li>Cantores: <span id="n-cantores">0</span></li>
              </ul>
            </div>

            <div class="credit-panel">
              <h4>Produção</h4>
              <label for="modal-producao-toggle" class="btn-select">Selecionar produção</label>
              <ul>
                <li>Produtores: <span id="n-produtores">0</span></li>
                <li>Gravadoras: <span id="n-gravadoras">0</span></li>
                <li>Editora: <span id="n-editora">nenhuma</span></li>
              </ul>
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <?php if ($podeAdicionar): ?>
              <button class="btn btn-secondary" type="reset">Limpar</button>
              <button class="btn btn-primary" type="submit">Registar Disco</button>
            <?php else: ?>
              <span class="text-muted text-sm">O seu perfil só pode listar.</span>
            <?php endif; ?>
          </div>
        </form>
      </main>
    </div>
  </div>

  <!-- ============ MODAL: GÉNEROS ============ -->
  <div class="modal-overlay modal-generos">
    <div class="modal modal-sm">
      <div class="modal-header">
        <h2>Géneros</h2>
        <label for="modal-generos-toggle" class="modal-close">&times;</label>
      </div>
      <div class="modal-body">
        <div class="list-box"><?php itens('generos', $generos, 'n-generos'); ?></div>
      </div>
      <div class="modal-footer">
        <label for="modal-generos-toggle" class="btn btn-primary">Concluir</label>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: PARTICIPANTES ============ -->
  <div class="modal-overlay modal-participantes">
    <div class="modal">
      <div class="modal-header">
        <h2>Participantes</h2>
        <label for="modal-participantes-toggle" class="modal-close">&times;</label>
      </div>
      <div class="modal-body">
        <div class="participant-grid">
          <div class="participant-col">
            <h4>Compositores</h4>
            <div class="list-box"><?php itens('compositores', $compositores, 'n-compositores'); ?></div>
          </div>
          <div class="participant-col">
            <h4>Músicos</h4>
            <div class="list-box"><?php itens('musicos', $musicos, 'n-musicos'); ?></div>
          </div>
          <div class="participant-col">
            <h4>Cantores</h4>
            <div class="list-box"><?php itens('cantores', $cantores, 'n-cantores'); ?></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <label for="modal-participantes-toggle" class="btn btn-primary">Concluir</label>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: PRODUÇÃO ============ -->
  <div class="modal-overlay modal-producao">
    <div class="modal">
      <div class="modal-header">
        <h2>Produção</h2>
        <label for="modal-producao-toggle" class="modal-close">&times;</label>
      </div>
      <div class="modal-body">
        <div class="participant-grid">
          <div class="participant-col">
            <h4>Produtores</h4>
            <div class="list-box"><?php itens('produtores', $produtores, 'n-produtores'); ?></div>
          </div>
          <div class="participant-col">
            <h4>Gravadoras</h4>
            <div class="list-box"><?php itens('gravadoras', $gravadoras, 'n-gravadoras'); ?></div>
          </div>
          <div class="participant-col">
            <h4>Editora (uma só)</h4>
            <div class="list-box"><?php itens('editora', $editoras, 'n-editora', true); ?></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <label for="modal-producao-toggle" class="btn btn-primary">Concluir</label>
      </div>
    </div>
  </div>

<script>
// Actualiza o número (ou o nome da editora) que aparece no painel
function contar(el, destino) {
  var alvo = document.getElementById(destino);
  if (el.type === 'radio') {
    alvo.textContent = el.dataset.nome;
    return;
  }
  alvo.textContent = document.querySelectorAll('input[name="' + el.name + '"]:checked').length;
}
</script>
</body>

</html>