<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';
require_once __DIR__ . '/../../controller/utilizadorController.php';
require_once __DIR__ . '/../../repository/disco.php';
require_once __DIR__ . '/../../repository/faixa.php';
require_once __DIR__ . '/../../repository/generorepository.php';
require_once __DIR__ . '/../../repository/compositor.php';
require_once __DIR__ . '/../../repository/musico.php';
require_once __DIR__ . '/../../repository/cantor.php';

$utilizador = Sessao::getUtilizadorLogado();
if (!$utilizador) {
    header('Location: Login.php');
    exit;
}

$discoDAO      = new DiscoDAO();
$faixaDAO      = new FaixaDAO();
$generoDAO     = new GeneroDAO();
$compositorDAO = new CompositorDAO();
$musicoDAO     = new MusicoDAO();
$cantorDAO     = new CantorDAO();

$msg = '';
$det = null;   // disco aberto em "Ver Detalhes"

/* ---------- Acções (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];

    if ($acao === 'remover') {
        $id = (int)($_POST['sel'] ?? 0);
        if ($id === 0) $msg = 'Seleccione um disco.';
        else $msg = $discoDAO->remover($id) ? 'Disco removido.' : 'Erro ao remover o disco.';
    }

    if ($acao === 'atualizar') {
        $id = (int)$_POST['id'];
        $discoDAO->atualizar(new DiscoCompacto(
            $id,
            trim($_POST['titulo']),
            (float)$_POST['preco'],
            (int)substr($_POST['ano_edicao'], 0, 4),   // o calendário envia AAAA-MM-DD
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            null,
            []
        ));
        // géneros: apaga as ligações antigas e grava as marcadas
        $generoDAO->removerRelacoesPorDisco($id);
        foreach (($_POST['generos'] ?? []) as $g) {
            $generoDAO->inserirRelacaoGeneroDisco($id, (int)$g);
        }
        $msg = 'Disco atualizado.';
    }

    if ($acao === 'addfaixa') {
        $id  = (int)$_POST['id'];
        $dur = sprintf('%02d:%02d', (int)$_POST['min'], (int)$_POST['seg']);
        $num = count($faixaDAO->listarPorCodigo($id)) + 1;

        $idFaixa = $faixaDAO->inserir(
            new Faixa(trim($_POST['nome']), trim($_POST['artista']), $dur, $num),
            $id
        );

        if ($idFaixa > 0) {
            foreach (($_POST['compositores'] ?? []) as $c) $faixaDAO->InserRelacaoCompositor($idFaixa, (int)$c);
            foreach (($_POST['musicos'] ?? []) as $m)      $faixaDAO->InserRelacaoMusico($idFaixa, (int)$m);
            foreach (($_POST['cantores'] ?? []) as $c)     $faixaDAO->InserRelacaoCantor($idFaixa, (int)$c);
            $msg = 'Faixa adicionada.';
        } else {
            $msg = 'Erro ao adicionar a faixa.';
        }
    }

    if ($acao === 'removerfaixa') {
        $id = (int)$_POST['id'];
        $f  = (int)($_POST['sel_faixa'] ?? 0);
        if ($f === 0) {
            $msg = 'Seleccione uma faixa.';
        } else {
            // o DAO não deixa remover faixas com participantes: tira-os primeiro
            foreach ($faixaDAO->listarCompositoresPorFaixa($f) as $c) $faixaDAO->removerCompositor($f, $c->getCodigoCompositor());
            foreach ($faixaDAO->listarMusicosPorFaixa($f) as $m)      $faixaDAO->removerMusico($f, $m->getCodigoMusico());
            foreach ($faixaDAO->listarCantoresPorFaixa($f) as $c)     $faixaDAO->removerCantor($f, $c->getCodigoCantor());
            $msg = $faixaDAO->remover($f) ? 'Faixa removida.' : 'Não foi possível remover a faixa.';
        }
        $det = $discoDAO->buscarPorCodigo($id);   // volta a abrir os detalhes
    }
}

/* ---------- Botões Editar / Adicionar Faixas / Ver Detalhes (GET) ---------- */
$ed = null;   // disco a editar
$fx = null;   // disco a que se vai juntar uma faixa
$acaoGet = $_GET['acao'] ?? '';
if ($acaoGet !== '') {
    $d = $discoDAO->buscarPorCodigo((int)($_GET['sel'] ?? 0));
    if (!$d)                       $msg = 'Seleccione um disco.';
    elseif ($acaoGet === 'editar')   $ed  = $d;
    elseif ($acaoGet === 'faixas')   $fx  = $d;
    elseif ($acaoGet === 'detalhes') $det = $d;
}

$discos = $discoDAO->listarTodos();   // depois das acções, para vir actualizado

/* ---------- Cabeçalho: foto do utilizador com sessão ---------- */
$utilizadorController = new UtilizadorController();
$fotoLogado = null;
foreach ($utilizadorController->listarUtilizador() as $u) {
    if ($u->getUser_name() === $utilizador->getUser_name()) {
        $fotoLogado = $utilizadorController->buscarFoto($u->getCodigo());
    }
}

function h($v)
{
    return htmlspecialchars((string)$v);
}
function nomes($lista, $g1, $g2)
{   // "Nome Apelido, Nome Apelido"
    $r = [];
    foreach ($lista as $p) $r[] = $p->$g1() . ' ' . $p->$g2();
    return implode(', ', $r);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Discos | Sistema de Gestão</title>
    <link rel="stylesheet" href="../css/listagemdiscos.css">
    <link rel="stylesheet" href="../css/registro.css">
</head>

<body>

    <input type="checkbox" id="modal-detalhes-toggle" class="modal-toggle" <?= $det ? 'checked' : '' ?>>
    <input type="checkbox" id="modal-editar-disco-toggle" class="modal-toggle" <?= $ed ? 'checked' : '' ?>>
    <input type="checkbox" id="modal-adicionar-faixa-toggle" class="modal-toggle" <?= $fx ? 'checked' : '' ?>>

    <div class="app-container">
        <aside>
            <div class="title" id="listar">
                <h1>DiscoGest</h1>
            </div>
            <nav>
                <ul>
                    <li class="secnav">Menu Principal</li>
                    <li><a href="registro.php">Registar</a></li>
                    <li><a href="#listar" class="active">Listar Discos</a></li>
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
                    <p>Listagem de discos cadastrados</p>
                </div>
                <div class="userdetails">
                    <div class="userdetailstxt">
                        <?= h($utilizador->getNome()) ?>
                        <p>Perfil</p>
                    </div>
                    <img src="<?= $fotoLogado ? '../resources/fotos/' . h($fotoLogado) : '../resources/user.png' ?>" alt="Foto de Perfil">
                </div>
            </header>

            <main>
                <div class="card">
                    <div class="card-header">
                        <h3>Discos Cadastrados no Sistema</h3>
                    </div>

                    <?php if ($msg): ?>
                        <div class="alert alert-ok"><?= h($msg) ?></div>
                    <?php endif; ?>

                    <form method="post" action="listagemdiscos.php">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Título</th>
                                        <th>Género</th>
                                        <th>Preço</th>
                                        <th>Ano Edição</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($discos as $d): ?>
                                        <tr>
                                            <td><input type="radio" name="sel" value="<?= $d->getCodigoDisco() ?>"></td>
                                            <td><?= h($d->getTitulo()) ?></td>
                                            <td><?= h($d->getGeneroMusicalTxt()) ?></td>
                                            <td><?= number_format($d->getPreco(), 2, ',', '.') ?></td>
                                            <td><?= h($d->getAnoEdicao()) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-actions">
                            <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="remover"
                                onclick="return confirm('Remover o disco seleccionado e as suas faixas?')">Remover</button>
                            <button class="btn btn-secondary btn-sm" type="submit" name="acao" value="faixas" formmethod="get">Adicionar Faixas</button>
                            <button class="btn btn-primary btn-sm" type="submit" name="acao" value="editar" formmethod="get">Editar</button>
                            <button class="btn btn-primary btn-sm" type="submit" name="acao" value="detalhes" formmethod="get">Ver Detalhes</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- ============ MODAL: DETALHES DO DISCO ============ -->
    <div class="modal-overlay modal-detalhes">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h2>Detalhes do Disco</h2>
                <label for="modal-detalhes-toggle" class="modal-close">&times;</label>
            </div>
            <?php
            if ($det) {
                $dId      = $det->getCodigoDisco();
                $dGeneros = implode(', ', array_map(fn($g) => $g->getNomeGenero(), $generoDAO->listarPorDisco($dId)));
                $dFaixas  = $faixaDAO->listarPorCodigo($dId);
            }
            ?>
            <form method="post" action="listagemdiscos.php">
                <input type="hidden" name="acao" value="removerfaixa">
                <input type="hidden" name="id" value="<?= $det ? $dId : '' ?>">
                <div class="modal-body">
                    <div class="detail-section">
                        <h4>Informações do Disco</h4>
                        <div class="detail-grid">
                            <div class="detail-item"><span class="label">Título do Disco</span><span class="value"><?= $det ? h($det->getTitulo()) : '' ?></span></div>
                            <div class="detail-item"><span class="label">Género Musical</span><span class="value"><?= $det ? h($dGeneros) : '' ?></span></div>
                            <div class="detail-item"><span class="label">Preço</span><span class="value"><?= $det ? number_format($det->getPreco(), 2, ',', '.') : '' ?></span></div>
                            <div class="detail-item"><span class="label">Tempo de Existência</span><span class="value"><?= $det ? (date('Y') - (int)$det->getAnoEdicao()) . ' ano(s)' : '' ?></span></div>
                            <div class="detail-item"><span class="label">Ano de Edição</span><span class="value"><?= $det ? h($det->getAnoEdicao()) : '' ?></span></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Participantes do Disco</h4>
                        <div class="detail-grid">
                            <div class="detail-item"><span class="label">Compositores</span><span class="value"><?= $det ? h(nomes($compositorDAO->listarPorCodigo($dId), 'getNomeCompositor', 'getApelidoCompositor')) : '' ?></span></div>
                            <div class="detail-item"><span class="label">Músicos</span><span class="value"><?= $det ? h(nomes($musicoDAO->listarPorCodigo($dId), 'getNomeMusico', 'getApelidoMusico')) : '' ?></span></div>
                            <div class="detail-item"><span class="label">Cantores</span><span class="value"><?= $det ? h(nomes($cantorDAO->listarPorCodigo($dId), 'getNomeCantor', 'getApelidoCantor')) : '' ?></span></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Faixas do Disco</h4>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nº</th>
                                        <th>Nome da Faixa</th>
                                        <th>Artista Principal</th>
                                        <th>Duração</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($det): foreach ($dFaixas as $f): ?>
                                            <tr>
                                                <td><input type="radio" name="sel_faixa" value="<?= $f->getIdFaixa() ?>"></td>
                                                <td><?= h($f->getNumeroFaixa()) ?></td>
                                                <td><?= h($f->getNomeFaixa()) ?></td>
                                                <td><?= h($f->getArtistaPrincipal()) ?></td>
                                                <td><?= h($f->getDuracao()) ?></td>
                                            </tr>
                                    <?php endforeach;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label for="modal-detalhes-toggle" class="btn btn-secondary">Fechar</label>
                    <button class="btn btn-primary" type="submit" onclick="return confirm('Remover a faixa seleccionada?')">Remover Faixa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ MODAL: EDITAR DISCO ============ -->
    <div class="modal-overlay modal-editar-disco">
        <div class="modal">
            <div class="modal-header">
                <h2>Editar Disco</h2>
                <label for="modal-editar-disco-toggle" class="modal-close">&times;</label>
            </div>
            <?php
            $generosDoDisco = [];
            if ($ed) {
                foreach ($generoDAO->listarPorDisco($ed->getCodigoDisco()) as $g) $generosDoDisco[] = $g->getCodigoGenero();
            }
            ?>
            <form method="post" action="listagemdiscos.php">
                <input type="hidden" name="acao" value="atualizar">
                <input type="hidden" name="id" value="<?= $ed ? $ed->getCodigoDisco() : '' ?>">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="label-group form-grid-full">
                            <label for="edit-titulo">Título</label>
                            <input type="text" id="edit-titulo" name="titulo" required value="<?= h($ed ? $ed->getTitulo() : '') ?>">
                        </div>
                        <div class="label-group">
                            <label for="edit-preco">Preço</label>
                            <input type="number" id="edit-preco" name="preco" min="0" step="0.01" value="<?= $ed ? $ed->getPreco() : '' ?>">
                        </div>
                        <div class="label-group">
                            <label for="edit-ano">Data de Edição</label>
                            <input type="date" id="edit-ano" name="ano_edicao" required value="<?= $ed ? $ed->getAnoEdicao() . '-01-01' : '' ?>">
                        </div>
                        <div class="label-group form-grid-full">
                            <label>Géneros</label>
                            <div class="list-box" style="max-height:140px;">
                                <?php foreach ($generoDAO->listarTodos() as $g): ?>
                                    <div class="list-item">
                                        <input type="checkbox" name="generos[]" id="eg<?= $g->getCodigoGenero() ?>" value="<?= $g->getCodigoGenero() ?>"
                                            <?= in_array($g->getCodigoGenero(), $generosDoDisco) ? 'checked' : '' ?>>
                                        <label for="eg<?= $g->getCodigoGenero() ?>"><?= h($g->getNomeGenero()) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label for="modal-editar-disco-toggle" class="btn btn-secondary">Cancelar</label>
                    <button class="btn btn-primary" type="submit">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ MODAL: ADICIONAR FAIXA ============ -->
    <div class="modal-overlay modal-adicionar-faixa">
        <div class="modal">
            <div class="modal-header">
                <h2>Nova Faixa</h2>
                <label for="modal-adicionar-faixa-toggle" class="modal-close">&times;</label>
            </div>
            <form method="post" action="listagemdiscos.php">
                <input type="hidden" name="acao" value="addfaixa">
                <input type="hidden" name="id" value="<?= $fx ? $fx->getCodigoDisco() : '' ?>">
                <div class="modal-body">
                    <div class="detail-section">
                        <h4>Informações da Faixa<?= $fx ? ' — ' . h($fx->getTitulo()) : '' ?></h4>
                        <div class="form-grid">
                            <div class="label-group">
                                <label for="faixa-nome">Nome da Faixa</label>
                                <input type="text" id="faixa-nome" name="nome" required>
                            </div>
                            <div class="label-group">
                                <label for="faixa-artista">Artista Principal</label>
                                <input type="text" id="faixa-artista" name="artista" required>
                            </div>
                            <div class="label-group">
                                <label>Duração (mm:ss)</label>
                                <div style="display:flex; gap:0.5rem; align-items:center;">
                                    <input type="number" name="min" min="0" max="99" value="0" style="width:70px; text-align:center;">
                                    <span>:</span>
                                    <input type="number" name="seg" min="0" max="59" value="0" style="width:70px; text-align:center;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h4>Selecione entre os participantes já escolhidos no disco</h4>
                        <div class="participant-grid">
                            <div class="participant-col">
                                <h4>Compositores</h4>
                                <div class="list-box" style="max-height:120px;">
                                    <?php if ($fx): foreach ($compositorDAO->listarPorCodigo($fx->getCodigoDisco()) as $c): ?>
                                            <div class="list-item"><input type="checkbox" name="compositores[]" id="fc<?= $c->getCodigoCompositor() ?>" value="<?= $c->getCodigoCompositor() ?>"><label for="fc<?= $c->getCodigoCompositor() ?>"><?= h($c->getNomeCompositor() . ' ' . $c->getApelidoCompositor()) ?></label></div>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                            <div class="participant-col">
                                <h4>Músicos</h4>
                                <div class="list-box" style="max-height:120px;">
                                    <?php if ($fx): foreach ($musicoDAO->listarPorCodigo($fx->getCodigoDisco()) as $m): ?>
                                            <div class="list-item"><input type="checkbox" name="musicos[]" id="fm<?= $m->getCodigoMusico() ?>" value="<?= $m->getCodigoMusico() ?>"><label for="fm<?= $m->getCodigoMusico() ?>"><?= h($m->getNomeMusico() . ' ' . $m->getApelidoMusico()) ?></label></div>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                            <div class="participant-col">
                                <h4>Cantores</h4>
                                <div class="list-box" style="max-height:120px;">
                                    <?php if ($fx): foreach ($cantorDAO->listarPorCodigo($fx->getCodigoDisco()) as $c): ?>
                                            <div class="list-item"><input type="checkbox" name="cantores[]" id="fv<?= $c->getCodigoCantor() ?>" value="<?= $c->getCodigoCantor() ?>"><label for="fv<?= $c->getCodigoCantor() ?>"><?= h($c->getNomeCantor() . ' ' . $c->getApelidoCantor()) ?></label></div>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label for="modal-adicionar-faixa-toggle" class="btn btn-secondary">Cancelar</label>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>s