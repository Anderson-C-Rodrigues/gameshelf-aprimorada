<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    responder(['ok' => true]);
}

$resource = $_GET['resource'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($resource === 'games' && $method === 'GET') {
        responder(['ok' => true, 'games' => todos('jogos')]);
    }

    if ($resource === 'games' && $method === 'POST') {
        $dados = jsonEntrada();
        if (empty($dados['titulo'])) {
            responder(['ok' => false, 'message' => 'Título obrigatório.'], 400);
        }
        responder(['ok' => true, 'game' => inserirJogo($dados)]);
    }

    if ($resource === 'profile' && $method === 'GET') {
        responder(['ok' => true, 'profile' => carregarPerfilServidor()]);
    }

    if ($resource === 'profile' && $method === 'POST') {
        responder(['ok' => true, 'profile' => salvarPerfilServidor(jsonEntrada())]);
    }

    if ($resource === 'import_profile' && $method === 'POST') {
        $dados = jsonEntrada();
        $url = $dados['url'] ?? '';
        if (!$url) {
            responder(['ok' => false, 'message' => 'Informe a URL do perfil.'], 400);
        }
        $perfil = perfilYourGamerProfile($url);
        responder(['ok' => true, 'profile' => $perfil]);
    }

    if ($resource === 'import_games' && $method === 'POST') {
        $dados = jsonEntrada();
        $url = $dados['url'] ?? '';
        if (!$url) {
            responder(['ok' => false, 'message' => 'Informe a URL do perfil.'], 400);
        }
        $jogos = jogosYourGamerProfile($url);
        responder([
            'ok' => true,
            'games' => $jogos,
            'message' => count($jogos) ? 'Jogos públicos encontrados e importados.' : 'A plataforma não disponibilizou a lista completa de jogos no HTML público. Use a importação por texto na Coleção ou configure uma API/adapter específico.'
        ]);
    }

    if ($resource === 'rawg_search' && $method === 'POST') {
        $dados = jsonEntrada();
        $query = trim($dados['query'] ?? '');

        if (!RAWG_API_KEY) {
            responder(['ok' => false, 'message' => 'Configure RAWG_API_KEY em api/config.php.'], 400);
        }

        if (!$query) {
            responder(['ok' => false, 'message' => 'Informe o nome do jogo.'], 400);
        }

        $url = 'https://api.rawg.io/api/games?key=' . urlencode(RAWG_API_KEY) . '&search=' . urlencode($query);
        $json = buscarUrl($url);
        responder(['ok' => true, 'raw' => $json ? json_decode($json, true) : null]);
    }

    if ($resource === 'playstation_import' && $method === 'POST') {
        responder([
            'ok' => false,
            'message' => 'Conexão PlayStation preparada no backend, mas a importação real exige autenticação/autorização da conta e acesso a uma API compatível. Não use tokens pessoais no front-end.'
        ], 501);
    }

    responder(['ok' => false, 'message' => 'Endpoint não encontrado.'], 404);
} catch (Throwable $e) {
    responder(['ok' => false, 'message' => $e->getMessage()], 500);
}
