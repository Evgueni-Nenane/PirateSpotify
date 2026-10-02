# Mapeamento PHP — PirateSpotify

Este documento descreve **onde e como o PHP deve ser inserido** em cada tela (HTML) do sistema de gestão de discos, ligando a *view* aos *models*, *repositories* (DAOs) e *controllers* já existentes.

---

## 1. Arquitetura atual

```
PirateSpotify/
├── index.html                      # Dashboard (entrada)
├── src/
│   ├── controller/                 # Regras de aplicação (orquestra DAO)
│   ├── model/                      # Entidades (POJO)
│   ├── repository/                 # DAO + connection.php (MySQL)
│   └── view/
│       ├── css/                    # 1 CSS por página
│       ├── resources/             # imagens (user.png, login.png)
│       └── pages/                 # 1 HTML por tela
```

**Fluxo:** `pages/*.php` (view) → `controller/*.php` → `repository/*.php` (DAO) → `model/*.php` → MySQL.

**Base de dados:** `discocompacto` (ver `src/repository/connection.php`).
> ⚠️ `Connection::connectionDB()` faz `echo "Conexao estabelecida";` — **remover** em produção.

**Sessão:** `src/model/sessao.php` → `Sessao::getUtilizadorLogado()` devolve um `Utilizador`.

---

## 2. Convenção de marcadores

Todo o HTML ficou marcado com comentários `<!-- PHP: ... -->` (sem `<?php ?>`), para o PHP preencher depois:

| Marcador no HTML | Significado | Tradução em PHP |
|---|---|---|
| `<!-- PHP: repetir por linha (… base de dados) -->` | Linha de `<tbody>` repetida | `foreach ($lista as $item) { … }` |
| `<!-- PHP: repetir por item (… base de dados) -->` | Item de `.list-box` repetido | `foreach ($lista as $item) { … }` |
| `<!-- PHP: <campo> -->` | Valor único (texto de um elemento) | `<?= htmlspecialchars($obj->getX()) ?>` |
| `<!-- PHP: nome do utilizador autenticado -->` | Cabeçalho | `Sessao::getUtilizadorLogado()->getNomeCompleto()` |
| `<!-- PHP: perfil do utilizador -->` | Cabeçalho | `Sessao::getUtilizadorLogado()->getPerfil()->getNome()` |

**Regra de ouro:** a linha/item-modelo que acompanha cada marcador é um *template* — deve ser reescrita dentro do `foreach`. Nunca imprimir valores sem `htmlspecialchars()`.

### 2.1 Bootstrap sugerido (topo de cada página `.php`)

```php
<?php
require_once __DIR__ . '/../../model/sessao.php';
require_once __DIR__ . '/../../controller/...Controller.php';
$u = Sessao::getUtilizadorLogado();
if (!$u) { header('Location: login.php'); exit; }
?>
```

### 2.2 Cabeçalho de utilizador (comum a todas as páginas)

```php
<div class="userdetailstxt">
    <p><?= htmlspecialchars($u->getNomeCompleto()) ?></p>
    <p><?= htmlspecialchars($u->getPerfil()->getNome()) ?></p>
</div>
```

---

## 3. Mapa por página

### 3.1 `index.html` — Dashboard

| Elemento | Fonte (PHP) |
|---|---|
| `<!-- PHP: nome do utilizador -->` | `Sessao::getUtilizadorLogado()->getNomeCompleto()` |
| `<!-- PHP: perfil do utilizador -->` | `Sessao::getUtilizadorLogado()->getPerfil()->getNome()` |
| Marca "Poorify" | Texto estático (config) |

```php
<p><?= htmlspecialchars($u->getNomeCompleto()) ?></p>
<p><?= htmlspecialchars($u->getPerfil()->getNome()) ?></p>
```

### 3.2 `login.html` — Autenticação

| Elemento | Fonte (PHP) |
|---|---|
| `<form method="post">` | `LoginDAO::login($username, $senha, $logController)` |
| `name="username"` / `name="password"` | `$_POST['username']`, `$_POST['password']` |
| Redirecionamento pós-login | `if ($ok) header('Location: index.php');` |

```php
<?php
require_once __DIR__ . '/../../repository/login.php';
require_once __DIR__ . '/../../controller/logsController.php'; // a criar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dao = new LoginDAO();
    if ($dao->login($_POST['username'], $_POST['password'], new LogsController())) {
        header('Location: ../index.php'); exit;
    }
    $erro = 'Credenciais inválidas.';
}
?>
```

### 3.3 `registro.html` — Registar disco

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Lista de **géneros** (`<!-- PHP: repetir por item (géneros musicais…) -->`) | `GeneroDAO::listarTodos()` |
| Lista de **compositores** | `CompositorDAO::listarTodos()` |
| Lista de **músicos** | `MusicoDAO::listarTodos()` |
| Lista de **cantores** | `CantorDAO::listarTodos()` |
| Lista de **produtores** | `ProdutorDAO::listarTodos()` |
| Lista de **gravadoras** | `GravadoraDAO::listarTodos()` |
| Lista de **editoras** | `EditoraDAO::listarTodos()` |
| Detalhes do disco (`<span class="value">`) | `DiscoDAO::buscarPorCodigoComEdicao($id)` + `*DAO::listarPorCodigo($id)` |
| Tabela de faixas | `FaixaDAO::listarPorCodigo($codigoDisco)` |
| Formulário principal | `DiscoDAO::inserir()` + `EdicaoDAO::inserir()` + relações (`inserirRelacao*`) |
| Guardar disco | `DiscosController` (a criar) |
| Géneros do disco (editar) | `GeneroDAO::listarPorDisco($id)` |

```php
<!-- lista de géneros -->
<?php foreach ((new GeneroController())->listarGeneros() as $g): ?>
  <div class="list-item">
    <input type="checkbox" id="g<?= $g->getCodigoGenero() ?>" name="generos[]" value="<?= $g->getCodigoGenero() ?>">
    <label for="g<?= $g->getCodigoGenero() ?>"><?= htmlspecialchars($g->getNomeGenero()) ?></label>
  </div>
<?php endforeach; ?>
```

### 3.4 `listagemdiscos.html` — Listar discos

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela de discos (`<!-- PHP: repetir por linha (discos…) -->`) | `DiscoDAO::listarTodos()` |
| Detalhes — informações | `DiscoDAO::buscarPorCodigoComEdicao($id)` |
| Detalhes — produção | `ProdutorDAO::listarPorCodigo()` / `GravadoraDAO::listarPorCodigo()` / `EditoraDAO::listarPorCodigo()` |
| Detalhes — participantes | `CompositorDAO::listarPorCodigo()` / `MusicoDAO::listarPorCodigo()` / `CantorDAO::listarPorCodigo()` |
| Detalhes — faixas | `FaixaDAO::listarPorCodigo($id)` |
| Editar — info atual | `DiscoDAO::buscarPorCodigo($id)` |
| Editar — géneros disponíveis | `GeneroDAO::listarTodos()` |
| Editar — géneros selecionados | `GeneroDAO::listarPorDisco($id)` |
| Editar — guardar | `DiscoDAO::atualizar()` + `GeneroDAO::removerRelacoesPorDisco()` + `inserirRelacaoGeneroDisco()` |
| Adicionar faixa — participantes | relações (`FaixaDAO::InserRelacao*`) |

```php
<?php foreach ((new DiscoDAO())->listarTodos() as $d): ?>
  <tr>
    <td><?= htmlspecialchars($d->getTitulo()) ?></td>
    <td><?= htmlspecialchars($d->getGeneroMusicalTxt()) ?></td>
    <td><?= number_format((float) $d->getPreco(), 2) ?></td>
    <td><?= htmlspecialchars($d->getAnoEdicao()) ?></td>
  </tr>
<?php endforeach; ?>
```

### 3.5 `artistas.html` — Músicos, compositores e cantores

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela **músicos** (`<!-- PHP: repetir por linha (… músicos …) -->`) | `MusicoDAO::listarTodos()` |
| Tabela **compositores** | `CompositorDAO::listarTodos()` |
| Tabela **cantores** | `CantorDAO::listarTodos()` |
| Editar artista | `CantorDAO::atualizar()` / `CompositorDAO::atualizar()` / `MusicoDAO::atualizar()` |
| Lista de **instrumentos** (`<!-- PHP: repetir por item (instrumentos…) -->`) | `InstrumentoDAO::listarTodos()` |
| Cadastrar artista | `MusicoDAO::inserir()` / `CompositorDAO::inserir()` / `CantorDAO::inserir()` + `InstrumentoDAO::inserirRelacaoMusicoInstrumento()` |

```php
<?php foreach ((new MusicoDAO())->listarTodos() as $m): ?>
  <tr>
    <td><?= htmlspecialchars($m->getNomeCompleto()) ?></td>
    <td><?= htmlspecialchars(implode(', ', array_map(fn($i) => $i->getNome(), $m->getInstrumento() ?? []))) ?></td>
    <td><?= htmlspecialchars($m->getEmailMusico() ?: 'Não informado') ?></td>
    <td><?= htmlspecialchars($m->getContactoMusico() ?: 'Não informado') ?></td>
  </tr>
<?php endforeach; ?>
```

### 3.6 `producao.html` — Produtores, gravadoras e editoras

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela **produtores** (`<!-- PHP: repetir por linha -->`) | `ProdutorDAO::listarTodos()` |
| Tabela **gravadoras** | `GravadoraDAO::listarTodos()` |
| Tabela **editoras** | `EditoraDAO::listarTodos()` |
| Adicionar / Editar / Remover | `*DAO::inserir()` / `*DAO::atualizar()` / `*DAO::remover()` (via `ProdutorController`, `GravadoraController`, `EditoraController`) |

```php
<?php foreach ((new GravadoraController())->listarGravadoras() as $g): ?>
  <tr>
    <td><input type="radio" name="sel-grav" value="<?= $g->getCodigoGravadora() ?>"></td>
    <td><?= htmlspecialchars($g->getNomeGravadora()) ?></td>
    <td><?= htmlspecialchars($g->getContactoGravadora() ?: '—') ?></td>
    <td><?= htmlspecialchars($g->getEmailGravadora() ?: '—') ?></td>
    <td><?= htmlspecialchars($g->getEnderecoGravadora() ?: '—') ?></td>
  </tr>
<?php endforeach; ?>
```

### 3.7 `administracao.html` — Utilizadores

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| `<select>` de perfil | `NivelAcessoDAO::listarNiveis()` |
| Cadastrar utilizador | `UtilizadorDAO::inserir()` (perfil = `NivelAcesso`) |
| Senha inicial | gerar (`LoginDAO::resetarSenha()` / função de geração) |
| Tabela de utilizadores (`<!-- PHP: repetir por linha -->`) | `UtilizadorDAO::listarTodos()` |
| Editar utilizador | `UtilizadorDAO::atualizarUser()` |
| Resetar senha | `LoginDAO::resetarSenha($codigo)` |
| Remover utilizador | `UtilizadorDAO::remover($codigo)` |
| Foto | `UtilizadorDAO::adicionarFoto()` / `buscarFoto()` |

```php
<?php foreach ((new UtilizadorDAO())->listarTodos() as $ut): ?>
  <tr>
    <td><input type="radio" name="sel-user" value="<?= $ut->getCodigo() ?>"></td>
    <td><?= $ut->getCodigo() ?></td>
    <td><?= htmlspecialchars($ut->getNomeCompleto()) ?></td>
    <td><?= htmlspecialchars($ut->getEmail()) ?></td>
    <td><?= htmlspecialchars($ut->getGenero()) ?></td>
    <td><?= htmlspecialchars($ut->getPerfil()->getNome()) ?></td>
    <td><?= htmlspecialchars($ut->getContacto() ?: '—') ?></td>
  </tr>
<?php endforeach; ?>
```

### 3.8 `logs.html` — Auditoria

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela de logs (`<!-- PHP: repetir por linha -->`) | `LogsDAO::listarLogs()` |
| Pesquisa (`name="q"`) | `WHERE` filtrado em `LogsDAO` |

```php
<?php foreach ((new LogsDAO())->listarLogs() as $l): ?>
  <tr>
    <td><?= $l->getCodigo() ?></td>
    <td><?= htmlspecialchars($l->getNome() . ' ' . $l->getApelido()) ?></td>
    <td><?= htmlspecialchars($l->getEmail()) ?></td>
    <td><?= htmlspecialchars($l->getPerfil()) ?></td>
    <td><?= htmlspecialchars($l->getAccao()) ?></td>
    <td><?= htmlspecialchars($l->getDataHora()) ?></td>
  </tr>
<?php endforeach; ?>
```

### 3.9 `instrumentos.html` — Instrumentos *(tela nova)*

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela (`<!-- PHP: repetir por linha (instrumentos…) -->`) | `InstrumentoDAO::listarTodos()` (via `InstrumentoController::listarInstrumentos()`) |
| Formulário cadastrar (`name="acao" value="salvar"`) | `InstrumentoController::adicionarInstrumento()` |
| Modal remover (`name="acao" value="remover"`, `name="id"`) | `InstrumentoController::remover($id)` |

```php
<?php
require_once __DIR__ . '/../../model/instrumento.php';
require_once __DIR__ . '/../../controller/instrumentoController.php';
$ctrl = new InstrumentoController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'salvar') {
        $ctrl->adicionarInstrumento(new Instrumento(null, $_POST['nome']));
        header('Location: instrumentos.php'); exit;
    }
    if (($_POST['acao'] ?? '') === 'remover') {
        $ctrl->remover((int) $_POST['id']);
        header('Location: instrumentos.php'); exit;
    }
}
?>
```

```php
<?php foreach ($ctrl->listarInstrumentos() as $i): ?>
  <tr>
    <td><input type="radio" name="sel-instr" value="<?= $i->getCodigo() ?>"></td>
    <td><?= $i->getCodigo() ?></td>
    <td><?= htmlspecialchars($i->getNome()) ?></td>
  </tr>
<?php endforeach; ?>
```
> ⚠️ `InstrumentoDAO` só tem `inserir`, `listarTodos`, `listarPorCodigo` e `remover` — **não tem `atualizar()`** (edição ainda não suportada).

### 3.10 `generos.html` — Géneros *(tela nova)*

| Marcador | Fonte (PHP) |
|---|---|
| header utilizador | `Sessao::getUtilizadorLogado()` |
| Tabela (`<!-- PHP: repetir por linha (géneros…) -->`) | `GeneroDAO::listarTodos()` (via `GeneroController::listarGeneros()`) |
| Formulário cadastrar (`name="acao" value="salvar"`) | `GeneroController::adicionarGenero()` |
| Modal remover (`name="acao" value="remover"`, `name="id"`) | `GeneroController::removerGenero($id)` |

```php
<?php foreach ((new GeneroController())->listarGeneros() as $g): ?>
  <tr>
    <td><input type="radio" name="sel-genero" value="<?= $g->getCodigoGenero() ?>"></td>
    <td><?= $g->getCodigoGenero() ?></td>
    <td><?= htmlspecialchars($g->getNomeGenero()) ?></td>
  </tr>
<?php endforeach; ?>
```

---

## 4. Inventário de backend (existente vs. em falta)

| Camada | Já existe | Falta criar |
|---|---|---|
| **Controllers** | `Instrumento`, `Genero`, `Cantor`, `Compositor`, `Gravadora`, `Editora` | `Musico`, `Disco`, `Faixa`, `Utilizador`, `Logs`, `Login`, `Produtor` |
| **Repositories (DAO)** | todos (inclui `musico`, `disco`, `faixa`, `edicao`, `utilizador`, `logs`, `login`, `nivelAcesso`, `produtor`) | — |
| **Models** | todas as entidades | — |

### Inconsistências a corrigir
- `InstrumentoDAO::listarPorCodigo($codigo)` recebe, na verdade, o **código do músico** (faz JOIN com `Musico_Instrumento`) — renomear para `listarPorMusico()`.
- `MusicoDAO` chama `getNomeArtistico()/setNomeArtistico()` que **não existem** em `model/musico.php` — adicionar os métodos.
- `repository/produtorRepository.php` (código inválido), `gravadoraRepository.php` e `editoraRepository.php` (vazios) são **obsoletos** — remover.
- `connection.php` imprime `echo "Conexao estabelecida";` a cada conexão — **remover** o `echo`.
- `model/sexo.php` (enum) e `model/banda.php` não são usados nas telas atuais.

---

## 5. Telas ainda em falta

| Tela | Estado | Backend |
|---|---|---|
| **Instrumentos** | ✅ criado (`instrumentos.html` + `instrumentos.css`) | `InstrumentoController` (completo) |
| **Géneros** | ✅ criado (`generos.html` + `generos.css`) | `GeneroController` (completo) |
| **Utilizador** (perfil próprio) | ❌ falta | `UtilizadorDAO::buscarPorId()` |
| **Exportar** (discos → CSV/PDF) | ❌ falta | nenhum (criar) |

---

## 6. Checklist de integração (ordem sugerida)

1. **Bootstrap** em cada página: `require` de `Sessao` + Controller; redirecionar para `login.php` se não autenticado.
2. Renomear `.html` → `.php` (ou usar um *router* que faça `include` da view).
3. Trocar cada `<!-- PHP: repetir por ... -->` por um `foreach`.
4. Trocar cada `<!-- PHP: <campo> -->` por `<?= htmlspecialchars($obj->getX()) ?>`.
5. Ligar o menu: substituir `href="#"` pelos ficheiros reais (ex.: `registro.php`, `artistas.php`, `instrumentos.php`, `generos.php`).
6. Tratar os `POST` dos formulários/modais com validação e `LogsDAO::inserir()` para auditoria.
7. Criar os **Controllers em falta** (Musico, Disco, Faixa, Utilizador, Logs, Login, Produtor).
8. Ligar os botões **Adicionar / Editar / Remover** aos respetivos métodos DAO.





---

## 7. Correções aplicadas (revisão final)

Esta secção registra o que foi corrigido/ligado para o sistema ficar funcional.
Serve de *changelog* face às secções anteriores (que descrevem o estado antigo).

### 7.1 Bugs de backend corrigidos

| Ficheiro | Problema | Correção |
|---|---|---|
| `repository/disco.php` | `DiscoDAO::inserir()` chamava `$disco->getGenero()`, método **inexistente** em `DiscoCompacto` → erro fatal ao registar disco | Insere apenas `Titulo, Preco, Ano_Edicao`; o género é gravado na relação `Disco_Genero` (como já fazia o resto da app) |
| `repository/musico.php` | `montarMusico()` escrevia o **nome artístico por cima do nome real** (`setNomeMusico`); `atualizar()` chamava `getNomeArtistico()` inexistente | O modelo `Musico` ganhou `$nomeArtistico`, `getNomeArtistico()`/`setNomeArtistico()`; o DAO usa-os |
| `repository/instrumento.php` | `inserir()` inseria a PK `Codigo` (AUTO_INCREMENT) à mão | Insere só `NomeInstrumento` e devolve `insert_id` |
| `repository/instrumento.php` | `listarPorCodigo()` filtra na verdade pelo **código do músico** (`Musico_Instrumento`) — nome enganador | Renomeado para **`listarPorMusico()`** |
| `controller/instrumentoController.php` | `buscarPorCodigo()` chamava o método anterior | Renomeado para **`buscarPorMusico()`** |
| `controller/editoraController.php` | Fazia `require` de `repository/edicao.php` mas instanciava `EditoraDAO` | `require` corrigido para `repository/editora.php` |
| `repository/produtor.php`, `gravadora.php`, `editora.php` | `atualizar()` só gravava contacto/e-mail | Passou a gravar também o nome (e apelido no produtor), para o *Editar* funcionar |
| `view/pages/administracao.php` | Link "Sair" apontava para `logout.php` (não existe; Linux é *case-sensitive*) | Corrigido para **`LogOut.php`** |
| `view/pages/artistas.php` | `$m->setNomeMusico('')` apagava o nome do músico antes de inserir | Linha removida |

> `Connection::connectionDB()` já **não** imprime `echo "Conexao estabelecida";` — o problema indicado na secção 1 está resolvido.

### 7.2 Views integradas com PHP (deixaram de ser só HTML)

| Ficheiro | O que foi ligado |
|---|---|
| `view/pages/instrumentos.php` | Bootstrap de sessão + `InstrumentoController`; `foreach` da lista; `POST acao=salvar/remover`; modais ligados via `form=` |
| `view/pages/generos.php` | Bootstrap + `GeneroController`; lista via `GeneroDAO::listarTodos()`; `POST acao=salvar/remover` |
| `view/pages/logs.php` | Bootstrap + `LogsController::listarLogs()`; cabeçalho com utilizador/perfil; pesquisa `?q=` (filtra em memória) |
| `view/pages/producao.php` | Bootstrap + controllers de `Produtor`/`Gravadora`/`Editora`; 3 abas com `foreach`; CRUD completo (`adicionar`/`atualizar`/`remover`); modais de edição pré-preenchidos via `GET acao=editar&tipo=...&sel=...` |

Todas as páginas começam agora com o *bootstrap* de sessão (`Sessao::getUtilizadorLogado()` + `header('Location: Login.php')` se não autenticado) e mostram **nome + perfil** reais no cabeçalho.

### 7.3 Estrutura / limpeza

- **`index.php`** criado na **raiz**: encaminha para `src/view/pages/administracao.php` se houver sessão, senão para `src/view/pages/Login.php`.
- Removidos os ficheiros obsoletos indicados na secção 4:
  `repository/produtorRepository.php`, `repository/gravadoraRepository.php`,
  `repository/editoraRepository.php` e `view/pages/login.html` (substituído por `Login.php`).
- Removidos os modelos não usados `model/sexo.php` e `model/banda.php`.

### 7.4 Como executar

1. Arrancar o Apache e o MySQL do XAMPP (`/opt/lampp/lampp start`).
2. Base de dados `discocompacto` (ver `src/repository/connection.php`).
3. Abrir `http://localhost/PirateSpotify/` — o `index.php` reencaminha para o login.

### 7.5 Ainda por fazer (não bloqueia o funcionamento)

- **Exportar** (discos → CSV/PDF): continua uma opção de menu por implementar.
- As caixas `.search-input` das listagens (exceto `logs.php`) são apenas visuais — a pesquisa no cliente ainda não está ligada.
- `InstrumentoDAO` continua **sem `atualizar()`** (só inserir/listar/remover).
- As palavras-passe são guardadas em texto simples (projeto académico) — não usar em produção.

