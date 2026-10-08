# 🎮 GameShelf Full Stack

Aplicação web para gerenciamento de uma biblioteca pessoal de videogames. O projeto permite organizar uma coleção, acompanhar o progresso dos jogos, manter uma wishlist e visualizar informações da biblioteca através de um dashboard.

Esta é uma evolução do projeto original GameShelf, adicionando backend em PHP e persistência com SQLite.

## ✨ Principais funcionalidades

- Dashboard com estatísticas da coleção
- Cadastro, edição e exclusão de jogos
- Busca, filtros e ordenação
- Upload de capas
- Controle de status dos jogos
- Wishlist com prioridades
- Transferência de jogos da wishlist para a coleção
- Perfil personalizável
- Registro de review, horas jogadas e data de conclusão
- Área dedicada a integrações externas

## 🛠️ Tecnologias

### Frontend
- HTML5
- CSS3
- JavaScript Vanilla

### Backend
- PHP
- API própria para comunicação com o frontend

### Banco de dados
- SQLite

## 🔌 Integrações

O projeto possui estrutura preparada para trabalhar com dados externos, incluindo:

- Importação de informações públicas de perfil
- Integração preparada para RAWG mediante chave de API
- Estrutura para futuras integrações com plataformas de jogos

A disponibilidade dos dados depende das APIs e informações públicas fornecidas por cada serviço externo.

## 📁 Estrutura atual

```text
gameshelf-aprimorada/
└── gameshelf-backend-php-corrigido/
    ├── api/
    │   ├── adapters/
    │   ├── config.php
    │   ├── db.php
    │   ├── helpers.php
    │   └── index.php
    ├── index.html
    ├── colecao.html
    ├── wishlist.html
    ├── detalhes.html
    ├── perfil.html
    ├── integracoes.html
    ├── storage.js
    └── style.css
```

## 🚀 Executando

Os arquivos da aplicação estão na pasta `gameshelf-backend-php-corrigido`.

Para utilizar os recursos do backend, execute o projeto em um ambiente com PHP e permissão de escrita para o banco SQLite.

## 📈 Evolução do projeto

O GameShelf começou como uma aplicação totalmente client-side utilizando HTML, CSS, JavaScript e `localStorage`. Esta versão amplia o projeto com backend PHP, banco de dados e estrutura para integrações externas.

Essa evolução permitiu trabalhar conceitos como persistência no servidor, APIs, organização de dados e integração entre frontend e backend.
