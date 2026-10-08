<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../model/permissao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/logsController.php';
require_once __DIR__ . '/../../repository/musico.php';
require_once __DIR__ . '/../../repository/cantor.php';
require_once __DIR__ . '/../../repository/compositor.php';
require_once __DIR__ . '/../../repository/instrumento.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) { header('Location: Login.php'); exit; }
$username = $utilizador->getUser_name();
$podeAdicionar = Permissao::pode($utilizador, 'adicionar');
$podeEditar    = Permissao::pode($utilizador, 'editar');
$podeRemover   = Permissao::pode($utilizador, 'remover');
$podeUsers     = Permissao::pode($utilizador, 'utilizadores');
$podeLogs      = Permissao::pode($utilizador, 'logs');

$musicoDAO = new MusicoDAO();
$cantorDAO = new CantorDAO();
$compositorDAO = new CompositorDAO();
$instrDAO = new InstrumentoDAO();

/* ---------- Cadastrar / Atualizar / Remover (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if ($acao === 'cadastrar') {
        if (($bloq = Permissao::bloquear($utilizador, 'adicionar'))) { $msg = $bloq; }
        else {
        $nome     = trim($_POST['nome'] ?? '');
        $apelido  = trim($_POST['apelido'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $id = -1;

        if ($nome === '' || $apelido === '') {
            $msg = 'Preencha pelo menos o nome e o apelido.';
        } elseif ($tipo === 'musico') {
            $id = $musicoDAO->inserir(new Musico(0, $nome, $apelido, [], $contacto, $email));
            if ($id > 0) {
                foreach (($_POST['instrumentos'] ?? []) as $cod) {
                    $instrDAO->inserirRelacaoMusicoInstrumento($id, (int)$cod);
                }
            }
            $msg = $id > 0 ? 'Artista cadastrado.' : 'Erro ao cadastrar.';
        } elseif ($tipo === 'cantor') {
            $id = $cantorDAO->inserir(new Cantor(0, $nome, $apelido, $contacto, $email));
            $msg = $id > 0 ? 'Artista cadastrado.' : 'Erro ao cadastrar.';
        } elseif ($tipo === 'compositor') {
            $id = $compositorDAO->inserir(new Compositor(0, $nome, $apelido, $contacto, $email));
            $msg = $id > 0 ? 'Artista cadastrado.' : 'Erro ao cadastrar.';
        } else {
            $msg = 'Escolha o tipo de artista.';
        }

        if ($id > 0) {
            LogsController::registar('Registou o artista (' . $tipo . '): ' . $nome . ' ' . $apelido);
        }
        }
    }

    if ($acao === 'atualizar') {
        if (($bloq = Permissao::bloquear($utilizador, 'editar'))) { $msg = $bloq; }
        else {
        $id       = (int)($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $apelido  = trim($_POST['apelido'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if ($id === 0 || $nome === '' || $apelido === '') {
            $msg = 'Dados inválidos para atualizar.';
        } elseif ($tipo === 'musico') {
            $ok = $musicoDAO->atualizar(new Musico($id, $nome, $apelido, [], $contacto, $email));
            $instrDAO->removerRelacoesPorMusico($id);
            foreach (($_POST['instrumentos'] ?? []) as $cod) {
                $instrDAO->inserirRelacaoMusicoInstrumento($id, (int)$cod);
            }
            $msg = $ok ? 'Músico atualizado.' : 'Sem alterações.';
        } elseif ($tipo === 'cantor') {
            $ok = $cantorDAO->atualizar(new Cantor($id, $nome, $apelido, $contacto, $email));
            $msg = $ok ? 'Cantor atualizado.' : 'Sem alterações.';
        } elseif ($tipo === 'compositor') {
            $ok = $compositorDAO->atualizar(new Compositor($id, $nome, $apelido, $contacto, $email));
            $msg = $ok ? 'Compositor atualizado.' : 'Sem alterações.';
        } else {
            $msg = 'Dados inválidos para atualizar.';
        }
        }
    }

    if ($acao === 'remover') {
        if (($bloq = Permissao::bloquear($utilizador, 'remover'))) { $msg = $bloq; }
        else {
        // Cada aba selecciona pelo seu próprio name; mantém-se o formato antigo "tipo:id".
        $id = (int)($_POST['sel_' . $tipo] ?? 0);
        if ($id === 0) {
            [$tipoAntigo, $idAntigo] = array_pad(explode(':', $_POST['sel'] ?? ''), 2, '');
            if ($tipo === '' && $tipoAntigo !== '') $tipo = $tipoAntigo;
            $id = (int)$idAntigo;
        }
        if ($tipo === 'musico')          $ok = $id > 0 && $musicoDAO->remover($id);
        elseif ($tipo === 'cantor')      $ok = $id > 0 && $cantorDAO->remover($id);
        elseif ($tipo === 'compositor')  $ok = $id > 0 && $compositorDAO->remover($id);
        else                             $ok = false;

        if ($ok) {
            LogsController::registar('Removeu o artista (' . $tipo . ') ID ' . $id);
        }

        $msg = $ok ? 'Artista removido.' : 'Não foi possível remover (seleccione um artista que não esteja em discos).';
        }
    }

    // Volta para a mesma aba depois de gravar (padrão POST-Redirect-GET).
    $aba = ['musico' => 'musicos', 'compositor' => 'compositores', 'cantor' => 'cantores'][$tipo] ?? 'musicos';
    header('Location: artistas.php?tab=' . $aba . '&msg=' . urlencode($msg));
    exit;
}

$msg = $_GET['msg'] ?? '';

/* ---------- Aba visível ---------- */
$tab = $_GET['tab'] ?? 'musicos';
if (!in_array($tab, ['musicos', 'compositores', 'cantores'], true)) $tab = 'musicos';

/* ---------- Botão Editar (GET): carrega o artista seleccionado ---------- */
$ed = null;             // artista a editar
$edTipo = '';           // 'musico' | 'compositor' | 'cantor'
$edInstrumentos = [];   // instrumentos do músico em edição
if (($_GET['acao'] ?? '') === 'editar') {
    $edTipo = $_GET['tipo'] ?? '';
    $edId = (int)($_GET['sel_' . $edTipo] ?? 0);
    if ($edId === 0) { // compatibilidade com o formato antigo "tipo:id"
        [$tipoAntigo, $idAntigo] = array_pad(explode(':', $_GET['sel'] ?? ''), 2, '');
        if ($edTipo === '' && $tipoAntigo !== '') $edTipo = $tipoAntigo;
        $edId = (int)$idAntigo;
    }
    if ($edId > 0 && $edTipo === 'musico') {
        $ed = $musicoDAO->buscarPorCodigo($edId);
        if ($ed->getCodigoMusico()) {
            foreach ($musicoDAO->buscarInstrumentosDoMusico($edId) as $i) $edInstrumentos[] = $i->getCodigo();
            $tab = 'musicos';
        } else {
            $ed = null;
        }
    } elseif ($edId > 0 && $edTipo === 'compositor') {
        $ed = $compositorDAO->buscarPorCodigo($edId);
        if ($ed->getCodigoCompositor()) $tab = 'compositores';
        else $ed = null;
    } elseif ($edId > 0 && $edTipo === 'cantor') {
        $ed = $cantorDAO->buscarPorCodigo($edId);
        if ($ed->getCodigoCantor()) $tab = 'cantores';
        else $ed = null;
    } else {
        $edTipo = '';
    }
    if (!$ed && $msg === '') $msg = 'Seleccione um artista.';
}
$musicos      = $musicoDAO->listarTodos();
$cantores     = $cantorDAO->listarTodos();
$compositores = $compositorDAO->listarTodos();
$instrumentos = $instrDAO->listarTodos();

/* ---------- Cabeçalho: foto e perfil do utilizador com sessão ---------- */
$fotoLogado = $utilizador->getFoto();

/* ---------- Campos do modal de edição (normaliza os 3 tipos de artista) ---------- */
$edId = $edNome = $edApelido = $edContacto = $edEmail = '';
if ($ed) {
    if ($edTipo === 'musico') {
        $edId = $ed->getCodigoMusico();
        $edNome = $ed->getNomeMusico();
        $edApelido = $ed->getApelidoMusico();
        $edContacto = $ed->getContactoMusico();
        $edEmail = $ed->getEmailMusico();
    } elseif ($edTipo === 'compositor') {
        $edId = $ed->getCodigoCompositor();
        $edNome = $ed->getNomeCompositor();
        $edApelido = $ed->getApelidoCompositor();
        $edContacto = $ed->getContactoCompositor();
        $edEmail = $ed->getEmailCompositor();
    } else {
        $edId = $ed->getCodigoCantor();
        $edNome = $ed->getNomeCantor();
        $edApelido = $ed->getApelidoCantor();
        $edContacto = $ed->getContactoCantor();
        $edEmail = $ed->getEmailCantor();
    }
}
$edTitulo = ['musico' => 'Músico', 'compositor' => 'Compositor', 'cantor' => 'Cantor'][$edTipo] ?? 'Artista';
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

    <input type="radio" name="tabs" id="tab-musicos" class="tab-toggle" <?= $tab === 'musicos' ? 'checked' : '' ?>>
    <input type="radio" name="tabs" id="tab-compositores" class="tab-toggle" <?= $tab === 'compositores' ? 'checked' : '' ?>>
    <input type="radio" name="tabs" id="tab-cantores" class="tab-toggle" <?= $tab === 'cantores' ? 'checked' : '' ?>>

    <input type="checkbox" id="modal-adicionar-artista-toggle" class="modal-toggle">
    <input type="checkbox" id="modal-editar-artista-toggle" class="modal-toggle" <?= $ed ? 'checked' : '' ?>>

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
                    <p>Gestão de artistas</p>
                </div>
                <div class="userdetails">
                    <div class="userdetailstxt">
                        <?= htmlspecialchars($utilizador->getNomeCompleto()) ?>
                        <p><?= htmlspecialchars($utilizador->getPerfil()->getNome()) ?></p>
                    </div>
                    <img src="<?= $fotoLogado ? '../resources/fotos/' . htmlspecialchars($fotoLogado) : '../resources/user.png' ?>" alt="Foto de Perfil">
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

                    <!-- Músicos -->
                    <form method="post" action="artistas.php">
                        <input type="hidden" name="tipo" value="musico">
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
                                                <td><input type="radio" name="sel_musico" value="<?= $m->getCodigoMusico() ?>"></td>
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
                    </form>

                    <!-- Compositores -->
                    <form method="post" action="artistas.php">
                        <input type="hidden" name="tipo" value="compositor">
                        <div class="tab-content tab-content-compositores">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr><th class="col-sel"></th><th>Nome Completo</th><th>E-mail</th><th>Telefone</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($compositores as $c): ?>
                                            <tr>
                                                <td><input type="radio" name="sel_compositor" value="<?= $c->getCodigoCompositor() ?>"></td>
                                                <td><?= htmlspecialchars($c->getNomeCompositor() . ' ' . $c->getApelidoCompositor()) ?></td>
                                                <td><?= htmlspecialchars($c->getEmailCompositor()) ?></td>
                                                <td><?= htmlspecialchars($c->getContactoCompositor()) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>

                    <!-- Cantores -->
                    <form method="post" action="artistas.php">
                        <input type="hidden" name="tipo" value="cantor">
                        <div class="tab-content tab-content-cantores">
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr><th class="col-sel"></th><th>Nome Completo</th><th>E-mail</th><th>Telefone</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cantores as $c): ?>
                                            <tr>
                                                <td><input type="radio" name="sel_cantor" value="<?= $c->getCodigoCantor() ?>"></td>
                                                <td><?= htmlspecialchars($c->getNomeCantor() . ' ' . $c->getApelidoCantor()) ?></td>
                                                <td><?= htmlspecialchars($c->getEmailCantor()) ?></td>
                                                <td><?= htmlspecialchars($c->getContactoCantor()) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </form>

                    <!-- Fila única de ações para Músicos, Compositores e Cantores -->
                    <div class="table-actions">
                        <?php if ($podeRemover): ?>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="acaoArtista('remover')">Remover</button>
                        <?php endif; ?>

                        <?php if ($podeEditar): ?>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="acaoArtista('editar')">Editar</button>
                        <?php endif; ?>

                        <?php if ($podeAdicionar): ?>
                            <label for="modal-adicionar-artista-toggle" class="btn btn-primary btn-sm" style="cursor:pointer; margin:0;">Adicionar</label>
                        <?php endif; ?>
                    </div>
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
            <form method="post" action="artistas.php?tab=<?= $tab ?>">
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

    <!-- MODAL: EDITAR ARTISTA (abre pré-preenchido pelo botão "Editar") -->
    <div class="modal-overlay modal-editar-artista">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h2>Editar <?= $edTitulo ?></h2>
                <label for="modal-editar-artista-toggle" class="modal-close">&times;</label>
            </div>
            <form method="post" action="artistas.php?tab=<?= $tab ?>">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="tipo" value="<?= $ed ? htmlspecialchars($edTipo) : 'musico' ?>">
                <input type="hidden" name="id" value="<?= $ed ? $edId : '' ?>">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="label-group">
                            <label for="ed-nome">Nome</label>
                            <input type="text" id="ed-nome" name="nome" required value="<?= htmlspecialchars($edNome) ?>">
                        </div>
                        <div class="label-group">
                            <label for="ed-apelido">Apelido</label>
                            <input type="text" id="ed-apelido" name="apelido" required value="<?= htmlspecialchars($edApelido) ?>">
                        </div>

                        <!-- Só para Músico (os outros tipos não têm instrumentos) -->
                        <?php if ($edTipo !== 'compositor' && $edTipo !== 'cantor'): ?>
                        <div class="instrumento-group sempre">
                            <label>Instrumentos Musicais</label>
                            <div class="list-box">
                                <?php foreach ($instrumentos as $i): ?>
                                    <div class="list-item">
                                        <input type="checkbox" name="instrumentos[]" value="<?= $i->getCodigo() ?>" id="edinst<?= $i->getCodigo() ?>"
                                            <?= in_array($i->getCodigo(), $edInstrumentos) ? 'checked' : '' ?>>
                                        <label for="edinst<?= $i->getCodigo() ?>"><?= htmlspecialchars($i->getNome()) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="label-group form-grid-full">
                            <label for="ed-contacto">Contacto</label>
                            <input type="text" id="ed-contacto" name="contacto" value="<?= htmlspecialchars($edContacto) ?>">
                        </div>
                        <div class="label-group form-grid-full">
                            <label for="ed-email">E-mail</label>
                            <input type="email" id="ed-email" name="email" value="<?= htmlspecialchars($edEmail) ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label for="modal-editar-artista-toggle" class="btn btn-secondary">Cancelar</label>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

<script>
function acaoArtista(acao) {
    const tabs = {
        'tab-musicos': 'musico',
        'tab-compositores': 'compositor',
        'tab-cantores': 'cantor'
    };

    let tipo = '';
    for (const id in tabs) {
        const radio = document.getElementById(id);
        if (radio && radio.checked) {
            tipo = tabs[id];
            break;
        }
    }

    if (!tipo) {
        alert('Selecione um tipo de artista.');
        return;
    }

    const selecionado = document.querySelector('input[name="sel_' + tipo + '"]:checked');

    if (!selecionado) {
        alert('Selecione um artista primeiro.');
        return;
    }

    const id = selecionado.value;

    if (acao === 'remover') {
        if (!confirm('Tem certeza que deseja remover este artista?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'artistas.php';

        const campos = {
            acao: 'remover',
            tipo: tipo
        };
        campos['sel_' + tipo] = id;

        for (const nome in campos) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = nome;
            input.value = campos[nome];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    } else if (acao === 'editar') {
        window.location.href = 'artistas.php?acao=editar&tipo=' + encodeURIComponent(tipo) +
            '&sel_' + encodeURIComponent(tipo) + '=' + encodeURIComponent(id);
    }
}
</script>

</body>
</html>