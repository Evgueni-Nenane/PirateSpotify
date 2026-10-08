<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/loginController.php';


Sessao::iniciar();

// Se já estiver logado, não faz sentido ver o login outra vez — manda para o dashboard
if (Sessao::estaLogado()) {
    header('Location: administracao.php');
    exit;
}

$erro = '';
$usernamePreenchido = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $usernamePreenchido = $username;

    if ($username === '' || $password === '') {
        $erro = 'Preenche todos campos corretamente.';
    } else {
        $loginController = new LoginController();
        $sucesso = $loginController->login($username, $password);

        if ($sucesso) {
            // Se for o primeiro acesso, obriga a pessoa a mudar a senha antes de continuar
            if ($loginController->isPrimeiroAcesso($username, $password)) {
                header('Location: mudar-senha.php');
                exit;
            }
            header('Location: administracao.php');
            exit;
        } else {
            $erro = 'Utilizador ou Palavra-passe incorretos.';
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
    <title>Login | Spotify</title>
</head>

<body>
    <div class="container">
        <div class="img">
            <img src="../resources/login.png" alt="bro">
        </div>
        <div class="card">
            <div class="title">
                <h1>Login</h1>
            </div>

            <?php if ($erro !== ''): ?>
                <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>
            <form method="post">
                <div class="campo">
                    <label for="username">Nome de utilizador</label>
                    <input type="text" id="username" name="username" placeholder="John Doe">
                </div>

                <div class="campo">
                    <label for="password">Palavra-Passe</label>
                    <input type="text" id="password" name="password" class="pw" placeholder="Tua palavra-passe" autocomplete="off">
                    <label class="mostrar"><input type="checkbox" id="mostrar"> Mostrar senhas</label>
                   </div>


                <div class="campo">
                    <div class="center">
                        <button type="submit">Login</button>
                    </div>
                    <hr>
                    <div class="center"><a href="#">Pedir reset de senha</a></div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>