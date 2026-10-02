# PirateSpotify

Sistema de gestão de discos compactos (projeto escolar em PHP, MySQL e XAMPP).

## Como executar

1. Iniciar o Apache e o MySQL do XAMPP.
2. Garantir que existe a base de dados `discocompacto` (config em `src/repository/connection.php`).
3. Abrir `http://localhost/PirateSpotify/` — o `index.php` reencaminha para o login
   (`src/view/pages/Login.php`) ou para o painel de administração, conforme a sessão.

## Estrutura

```
PirateSpotify/
├── index.php                 # entrada (redireciona para login/painel)
├── MAPEAMENTO-PHP.md         # documentação do mapeamento view → controller → DAO → model
└── src/
    ├── controller/           # regras de aplicação
    ├── model/                # entidades (POJO) + Sessao
    ├── repository/           # DAOs + connection.php
    └── view/
        ├── css/              # 1 CSS por página
        ├── resources/        # imagens e fotos dos utilizadores
        └── pages/            # 1 página PHP por tela
```

Consulte `MAPEAMENTO-PHP.md` para o detalhe de cada tela e o *changelog* das correções.

