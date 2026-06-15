# GameShelf - Versão com Backend PHP

Versão avançada do GameShelf com front-end em HTML, CSS e JavaScript e backend em PHP + SQLite.

## Principais recursos

- Dashboard com estatísticas da coleção.
- Coleção com cadastro, edição, exclusão, busca, filtros, ordenação e upload de capa.
- Wishlist com prioridade e opção de mover jogo para a coleção.
- Perfil personalizável.
- Página Detalhes com review, horas jogadas e data de conclusão.
- Integrações com backend em `integracoes.html`.
- Importação de perfil público do Your Gamer Profile.
- Tentativa de importação de jogos públicos do Your Gamer Profile.
- Endpoint preparado para RAWG, exigindo chave em `api/config.php`.
- Área preparada para integração PlayStation futura com autenticação segura.

## Observação sobre integrações externas

A leitura de dados externos depende do que cada plataforma disponibiliza publicamente. Quando a lista completa de jogos não aparece no HTML público, o sistema pode importar apenas dados visíveis ou indicar o uso da importação por texto na página Coleção.

## Estrutura

```text
gameshelf-backend-php/
├── index.html
├── colecao.html
├── wishlist.html
├── detalhes.html
├── perfil.html
├── integracoes.html
├── storage.js
├── style.css
├── api/
│   ├── index.php
│   ├── helpers.php
│   ├── db.php
│   ├── config.php
│   └── adapters/
└── data/
```

## Como usar

Envie todos os arquivos para a pasta do projeto no servidor. A pasta `data/` precisa permitir escrita para o SQLite.
