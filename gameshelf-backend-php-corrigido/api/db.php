<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        criarTabelas($pdo);
    }

    return $pdo;
}

function criarTabelas(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS jogos (
        id TEXT PRIMARY KEY,
        titulo TEXT NOT NULL,
        plataforma TEXT,
        genero TEXT,
        ano TEXT,
        desenvolvedora TEXT,
        capa TEXT,
        nota TEXT,
        status TEXT,
        created_at TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id TEXT PRIMARY KEY,
        titulo TEXT NOT NULL,
        plataforma TEXT,
        prioridade TEXT,
        observacao TEXT,
        created_at TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS perfil (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        dados TEXT NOT NULL,
        updated_at TEXT
    )");
}

function todos(string $tabela): array {
    $stmt = db()->query("SELECT * FROM {$tabela} ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function inserirJogo(array $jogo): array {
    $jogo['id'] = $jogo['id'] ?? gerarIdServidor();
    $jogo['created_at'] = date('c');

    $stmt = db()->prepare("INSERT INTO jogos (id, titulo, plataforma, genero, ano, desenvolvedora, capa, nota, status, created_at)
        VALUES (:id, :titulo, :plataforma, :genero, :ano, :desenvolvedora, :capa, :nota, :status, :created_at)");

    $stmt->execute([
        ':id' => $jogo['id'],
        ':titulo' => $jogo['titulo'] ?? '',
        ':plataforma' => $jogo['plataforma'] ?? '',
        ':genero' => $jogo['genero'] ?? '',
        ':ano' => $jogo['ano'] ?? '',
        ':desenvolvedora' => $jogo['desenvolvedora'] ?? '',
        ':capa' => $jogo['capa'] ?? '',
        ':nota' => $jogo['nota'] ?? '',
        ':status' => $jogo['status'] ?? 'nao-iniciado',
        ':created_at' => $jogo['created_at'],
    ]);

    return $jogo;
}

function salvarPerfilServidor(array $perfil): array {
    $stmt = db()->prepare("INSERT INTO perfil (id, dados, updated_at) VALUES (1, :dados, :updated_at)
        ON CONFLICT(id) DO UPDATE SET dados = excluded.dados, updated_at = excluded.updated_at");
    $stmt->execute([
        ':dados' => json_encode($perfil, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':updated_at' => date('c'),
    ]);

    return $perfil;
}

function carregarPerfilServidor(): array {
    $stmt = db()->query("SELECT dados FROM perfil WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [];
    }

    return json_decode($row['dados'], true) ?: [];
}

function gerarIdServidor(): string {
    return 'srv_' . bin2hex(random_bytes(8)) . '_' . time();
}
