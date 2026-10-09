<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';

Sessao::iniciar();

// Só quem já fez login (mesmo que seja primeiro acesso) pode chegar aqui
if (!Sessao::estaLogado()) {
    header('Location: login.php');
    exit;
}

$utilizador = Sessao::getUtilizadorLogado();
$username = $utilizador->getUser_name();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
        $erro = 'Preenche todos os campos.';
    } elseif ($novaSenha !== $confirmarSenha) {
        $erro = 'A nova senha e a confirmação não coincidem.';
    } elseif (strlen($novaSenha) < 6) {
        $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
    } elseif ($novaSenha === $senhaAtual) {
        $erro = 'A nova senha não pode ser igual à senha atual.';
    } else {
        $loginController = new LoginController();
        $sucesso = $loginController->atualizarSenha($username, $senhaAtual, $novaSenha);

        if ($sucesso) {
            header('Location: administracao.php');
            exit;
        } else {
            $erro = 'Não foi possível atualizar a senha. Confirma a senha atual.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>Mudar Senha | Spotify</title>
</head>

<body>
    <div class="container">
        <div class="img">
            <img src="../resources/login.png" alt="bro">
        </div>
        <div class="card">
            <div class="title">
                <h1>Primeiro Acesso</h1>
            </div>
            <p>Olá, <?= htmlspecialchars($utilizador->getNome()) ?>. Como é o teu primeiro acesso, define uma nova senha.</p>

            <?php if ($erro !== ''): ?>
                <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="campo">
                    <label for="senha_atual">Senha atual</label>
                    <input type="text" id="senha_atual" name="senha_atual" class="pw" placeholder="Senha atual" autocomplete="off">
                </div>

                <div class="campo">
                    <label for="nova_senha">Nova senha</label>
                    <input type="text" id="nova_senha" name="nova_senha" class="pw" placeholder="Nova senha" autocomplete="off">
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar nova senha</label>
                    <input type="text" id="confirmar_senha" name="confirmar_senha" class="pw" placeholder="Repete a nova senha" autocomplete="off">
                </div>

                <div class="campo">
                    <label class="mostrar">
                        <input type="checkbox" id="mostrar"> Mostrar senhas
                    </label>
                </div>

                <div class="center">
                    <button type="submit">Guardar nova senha</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>