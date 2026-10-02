<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logs | Sistema de Gestão</title>
<link rel="stylesheet" href="../css/logs.css">
</head>
<body>

<div class="app-container">
  <aside>
    <div class="title" id="logs"><h1>DiscoGest</h1></div>
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
        <li><a href="administracao.php">Administração</a></li>
        <li><a href="#logs" class="active">Logs</a></li>
        <li><a href="LogOut.php">Sair</a></li>
      </ul>
    </nav>
  </aside>

  <div class="containermain">
    <header>
      <div class="pagetitle">
        <h2>Sistema de Gestão de Discos</h2>
        <p>Auditoria do sistema</p>
      </div>
      <div class="userdetails">
        <div class="userdetailstxt"><!-- PHP: nome do utilizador autenticado --><p>Nome Apelido</p><!-- PHP: perfil do utilizador --><p>Perfil</p></div>
        <img src="../resources/user.png" alt="Foto de Perfil">
      </div>
    </header>

    <main>
      <div class="card">
        <div class="card-header">
          <h3>Logs do Sistema</h3>
          <form method="get"><input type="search" name="q" class="search-input" placeholder="Pesquisar nos logs..."></form>
        </div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>ID</th><th>Nome Completo</th><th>E-mail Corporativo</th><th>Perfil</th><th>Acção</th><th>Data</th></tr></thead>
            <tbody>
              <!-- PHP: repetir por linha -->
              <tr><td>ID</td><td>Nome Completo</td><td>E-mail</td><td>Perfil</td><td>Acção</td><td>Data</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

</body>
</html>
