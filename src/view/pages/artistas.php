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

$musicoDAO = new MusicoDAO();
$cantorDAO = new CantorDAO();
$compositorDAO = new CompositorDAO();
$instrDAO = new InstrumentoDAO();

/* ---------- Cadastrar / Remover ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['acao'] === 'cadastrar') {
        $tipo = $_POST['tipo'];
        $nome = trim($_POST['nome']);
        $apelido = trim($_POST['apelido']);
        $contacto = trim($_POST['contacto']);
        $email = trim($_POST['email']);

        if ($tipo === 'musico') {
            $m = new Musico(0, $nome, $apelido, [], $contacto, $email);
            $m->setNomeMusico('');
            $id = $musicoDAO->inserir($m);
            foreach (($_POST['instrumentos'] ?? []) as $cod) {
                $instrDAO->inserirRelacaoMusicoInstrumento($id, (int)$cod);
            }
        } elseif ($tipo === 'cantor') {
            $id = $cantorDAO->inserir(new Cantor(0, $nome, $apelido, $contacto, $email));
        } else {
            $id = $compositorDAO->inserir(new Compositor(0, $nome, $apelido, $contacto, $email));
        }
        $msg = $id > 0 ? 'Artista cadastrado.' : 'Erro ao cadastrar.';
    }

    if ($_POST['acao'] === 'remover') {
        [$tipo, $id] = explode(':', $_POST['sel'] ?? ':');
        $id = (int)$id;
        if ($tipo === 'musico')          $ok = $musicoDAO->remover($id);
        elseif ($tipo === 'cantor')      $ok = $cantorDAO->remover($id);
        elseif ($tipo === 'compositor')  $ok = $compositorDAO->remover($id);
        else                             $ok = false;
        $msg = $ok ? 'Artista removido.' : 'Não foi possível remover (seleccione um artista que não esteja em discos).';
    }

    header('Location: artistas.php?msg=' . urlencode($msg));
    exit;
}

$msg = $_GET['msg'] ?? '';
$musicos      = $musicoDAO->listarTodos();
$cantores     = $cantorDAO->listarTodos();
$compositores = $compositorDAO->listarTodos();
$instrumentos = $instrDAO->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artistas | Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/artistas.css">
    <link rel="stylesheet" href="../css/registro.css">
</head>
<body>

    <input type="radio" name="tabs" id="tab-musicos" class="tab-toggle" checked>
    <input type="radio" name="tabs" id="tab-compositores" class="tab-toggle">
    <input type="radio" name="tabs" id="tab-cantores" class="tab-toggle">

    <input type="checkbox" id="modal-adicionar-artista-toggle" class="modal-toggle">

    <div class="app-container">
        <aside>
            <div class="title" id="artistas"><h1>DiscoGest</h1></div>
            <nav>
                <ul>
                    <li class="secnav">Menu Principal</li>
                    <li><a href="registro.php">Registar</a></li>
                    <li><a href="listagemdiscos.php">Listar Discos</a></li>
                    <li class="secnav">Intervenientes</li>
                    <li><a href="artistas.php" class="active">Artistas</a></li>
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
                    <p>Gestão de artistas</p>
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
                        <div class="tabs">
                            <label for="tab-musicos" class="tab-label">Listar Músicos</label>
                            <label for="tab-compositores" class="tab-label">Listar Compositores</label>
                            <label for="tab-cantores" class="tab-label">Listar Cantores</label>
                        </div>
                    </div>

                    <?php if ($msg): ?>
                        <div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <!-- Músicos -->
                        <div class="tab-content tab-content-musicos">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr><th class="col-sel"></th><th>Nome Completo</th><th>Instrumento</th><th>E-mail</th><th>Telefone</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($musicos as $m): ?>
                                            <?php $nomes = array_map(fn($i) => $i->getNome(), $musicoDAO->buscarInstrumentosDoMusico($m->getCodigoMusico())); ?>
                                            <tr>
                                                <td><input type="radio" name="sel" value="musico:<?= $m->getCodigoMusico() ?>"></td>
                                                <td><?= htmlspecialchars($m->getNomeMusico() . ' ' . $m->getApelidoMusico()) ?></td>
                                                <td><?= htmlspecialchars(implode(', ', $nomes)) ?></td>
                                                <td><?= htmlspecialchars($m->getEmailMusico()) ?></td>
                                                <td><?= htmlspecialchars($m->getContactoMusico()) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Compositores -->
                        <div class="tab-content tab-content-compositores">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr><th class="col-sel"></th><th>Nome Completo</th><th>E-mail</th><th>Telefone</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($compositores as $c): ?>
                                            <tr>
                                                <td><input type="radio" name="sel" value="compositor:<?= $c->getCodigoCompositor() ?>"></td>
                                                <td><?= htmlspecialchars($c->getNomeCompositor() . ' ' . $c->getApelidoCompositor()) ?></td>
                                                <td><?= htmlspecialchars($c->getEmailCompositor()) ?></td>
                                                <td><?= htmlspecialchars($c->getContactoCompositor()) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Cantores -->
                        <div class="tab-content tab-content-cantores">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr><th class="col-sel"></th><th>Nome Completo</th><th>E-mail</th><th>Telefone</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cantores as $c): ?>
                                            <tr>
                                                <td><input type="radio" name="sel" value="cantor:<?= $c->getCodigoCantor() ?>"></td>
                                                <td><?= htmlspecialchars($c->getNomeCantor() . ' ' . $c->getApelidoCantor()) ?></td>
                                                <td><?= htmlspecialchars($c->getEmailCantor()) ?></td>
                                                <td><?= htmlspecialchars($c->getContactoCantor()) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="table-actions">
                            <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="remover">Remover</button>
                            <label for="modal-adicionar-artista-toggle" class="btn btn-primary btn-sm" style="cursor:pointer; margin:0;">Adicionar</label>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL: ADICIONAR ARTISTA -->
    <div class="modal-overlay modal-adicionar-artista">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h2>Cadastrar Artista</h2>
                <label for="modal-adicionar-artista-toggle" class="modal-close">&times;</label>
            </div>
            <form method="post">
                <input type="hidden" name="acao" value="cadastrar">
                <div class="modal-body">
                    <input type="radio" name="tipo" value="musico" id="add-tipo-musico" class="tipo-toggle" checked>
                    <input type="radio" name="tipo" value="compositor" id="add-tipo-compositor" class="tipo-toggle">
                    <input type="radio" name="tipo" value="cantor" id="add-tipo-cantor" class="tipo-toggle">

                    <div class="form-grid">
                        <div class="label-group">
                            <label for="add-nome">Nome</label>
                            <input type="text" id="add-nome" name="nome" required>
                        </div>
                        <div class="label-group">
                            <label for="add-apelido">Apelido</label>
                            <input type="text" id="add-apelido" name="apelido" required>
                        </div>

                        <div class="label-group form-grid-full">
                            <label>Tipo de Artista</label>
                            <div class="tipo-selector">
                                <label for="add-tipo-musico" class="tipo-btn">Músico</label>
                                <label for="add-tipo-compositor" class="tipo-btn">Compositor</label>
                                <label for="add-tipo-cantor" class="tipo-btn">Cantor</label>
                            </div>
                        </div>

                        <!-- Só aparece para Músico -->
                        <div class="instrumento-group">
                            <label>Instrumentos Musicais</label>
                            <div class="list-box">
                                <?php foreach ($instrumentos as $i): ?>
                                    <div class="list-item">
                                        <input type="checkbox" name="instrumentos[]" value="<?= $i->getCodigo() ?>" id="inst<?= $i->getCodigo() ?>">
                                        <label for="inst<?= $i->getCodigo() ?>"><?= htmlspecialchars($i->getNome()) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="label-group form-grid-full">
                            <label for="add-contacto">Contacto</label>
                            <input type="text" id="add-contacto" name="contacto">
                        </div>
                        <div class="label-group form-grid-full">
                            <label for="add-email">E-mail</label>
                            <input type="email" id="add-email" name="email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label for="modal-adicionar-artista-toggle" class="btn btn-secondary">Cancelar</label>
                    <button class="btn btn-primary" type="submit">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>