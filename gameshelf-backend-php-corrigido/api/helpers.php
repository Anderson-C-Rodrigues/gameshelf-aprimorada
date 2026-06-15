<?php
function jsonEntrada(): array {
    $body = file_get_contents('php://input');
    $dados = json_decode($body, true);
    return is_array($dados) ? $dados : [];
}

function responder(array $dados, int $codigo = 200): void {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function buscarUrl(string $url): ?string {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 GameShelf/2.0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\nAccept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7\r\n",
            'timeout' => 12,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ]
    ]);

    $html = @file_get_contents($url, false, $ctx);
    return $html === false ? null : $html;
}

function gamertagPelaUrl(string $url): string {
    $path = parse_url($url, PHP_URL_PATH) ?: '';
    $partes = array_values(array_filter(explode('/', $path)));
    return $partes ? urldecode($partes[0]) : '';
}

function textoLimpo(string $html): string {
    $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texto = preg_replace('/\s+/', ' ', $texto);
    return trim($texto);
}

function numeroInteiroVisual(string $valor): string {
    $valor = trim($valor);
    $valor = preg_replace('/[^0-9,.]/', '', $valor);

    if ($valor === '') {
        return '';
    }

    if (preg_match('/^\d{1,3}([,.]\d{3})+$/', $valor)) {
        return preg_replace('/[,.]/', '', $valor);
    }

    return str_replace(',', '.', $valor);
}

function maiorNumeroPorLabel(string $texto, string $label): string {
    $padrao = '/([0-9][0-9.,]*)\s*' . preg_quote($label, '/') . '\b/i';
    preg_match_all($padrao, $texto, $matches);

    $maiorOriginal = '';
    $maiorValor = -1;

    foreach ($matches[1] ?? [] as $valor) {
        $normalizado = numeroInteiroVisual($valor);
        $comparavel = (float) str_replace(',', '.', $normalizado);
        if ($comparavel > $maiorValor) {
            $maiorValor = $comparavel;
            $maiorOriginal = $normalizado;
        }
    }

    return $maiorOriginal;
}

function primeiroNumeroPorPadrao(string $texto, array $padroes): string {
    foreach ($padroes as $padrao) {
        if (preg_match($padrao, $texto, $m)) {
            return numeroInteiroVisual($m[1]);
        }
    }
    return '';
}

function primeiroTextoPorPadrao(string $texto, array $padroes): string {
    foreach ($padroes as $padrao) {
        if (preg_match($padrao, $texto, $m)) {
            return trim($m[1]);
        }
    }
    return '';
}

function extrairAvatar(string $html, string $gamertag): string {
    if (preg_match_all('/<img[^>]+>/i', $html, $imgs)) {
        foreach ($imgs[0] as $img) {
            $alt = primeiroTextoPorPadrao($img, ['/alt=["\']([^"\']*)["\']/i']);
            $src = primeiroTextoPorPadrao($img, ['/src=["\']([^"\']+)["\']/i']);

            if ($src && $alt && mb_strtolower($alt) === mb_strtolower($gamertag)) {
                return str_starts_with($src, 'http') ? $src : '';
            }
        }
    }

    return '';
}

function extrairGeneros(string $texto): string {
    $generos = [];

    if (preg_match('/Top genres\s+(.+?)\s+Activity map/i', $texto, $m)) {
        $possiveis = preg_split('/\s+/', trim($m[1]));
        foreach ($possiveis as $genero) {
            $genero = trim($genero, " ,.;:|-/");
            if ($genero && !is_numeric($genero) && mb_strlen($genero) > 2) {
                $generos[] = $genero;
            }
        }
    }

    if (!$generos) {
        foreach (['Action','Adventure','RPG','Shooter','Simulator','Simulation','Indie','Platform','Racing','Sports','Strategy','Fighting','Puzzle'] as $genero) {
            if (preg_match('/\b' . preg_quote($genero, '/') . '\b/i', $texto)) {
                $generos[] = $genero;
            }
        }
    }

    $generos = array_values(array_unique($generos));
    return implode(', ', array_slice($generos, 0, 6));
}

function extrairPlataformas(string $texto): array {
    $resultado = [
        'playstation' => '',
        'xbox' => '',
        'pc' => '',
        'nintendo' => '',
        'mobile' => '',
        'principal' => '',
    ];

    $mapa = [
        'PlayStation' => 'playstation',
        'Xbox' => 'xbox',
        'PC' => 'pc',
        'Steam' => 'pc',
        'Nintendo' => 'nintendo',
        'Switch' => 'nintendo',
        'Mobile' => 'mobile',
    ];

    foreach ($mapa as $nome => $campo) {
        if (preg_match_all('/\b' . preg_quote($nome, '/') . '\b\s+([0-9][0-9.,]*)\b/i', $texto, $matches)) {
            $maior = 0;
            foreach ($matches[1] as $valor) {
                $normalizado = (int) preg_replace('/[^0-9]/', '', numeroInteiroVisual($valor));
                if ($normalizado > $maior) {
                    $maior = $normalizado;
                }
            }
            if ($maior > 0) {
                $resultado[$campo] = (string) $maior;
            }
        }
    }

    $principal = primeiroTextoPorPadrao($texto, ['/Highlights\s+([A-Za-z0-9 ]{2,30})\s+Most Played Platform/i']);
    if ($principal) {
        $resultado['principal'] = trim($principal);
    }

    if (!$resultado['principal']) {
        $contagens = [
            'PlayStation' => (int) $resultado['playstation'],
            'Xbox' => (int) $resultado['xbox'],
            'PC' => (int) $resultado['pc'],
            'Nintendo' => (int) $resultado['nintendo'],
            'Mobile' => (int) $resultado['mobile'],
        ];
        arsort($contagens);
        $maiorNome = array_key_first($contagens);
        $resultado['principal'] = ($contagens[$maiorNome] ?? 0) > 0 ? $maiorNome : '';
    }

    return $resultado;
}

function perfilYourGamerProfile(string $url): array {
    $html = buscarUrl($url);
    $gamertag = gamertagPelaUrl($url);

    $perfil = [
        'nome' => $gamertag,
        'gamertag' => $gamertag,
        'perfilExterno' => $url,
        'bio' => 'Perfil importado por link externo.',
        'plataforma' => '',
        'genero' => '',
        'avatar' => '',
        'totalJogosExternos' => '',
        'horasJogadas' => '',
        'conquistas' => '',
        'jogosCompletos' => '',
        'perfilVerificado' => '',
        'plataformaPrincipal' => '',
        'generosPrincipais' => '',
        'playstation' => '',
        'xbox' => '',
        'pc' => '',
        'nintendo' => '',
    ];

    if (!$html) {
        $perfil['bio'] = 'Link salvo. A leitura automática foi bloqueada ou o perfil não respondeu.';
        return $perfil;
    }

    $texto = textoLimpo($html);

    $perfil['avatar'] = extrairAvatar($html, $gamertag);
    $perfil['totalJogosExternos'] = maiorNumeroPorLabel($texto, 'Games');
    $perfil['jogosCompletos'] = maiorNumeroPorLabel($texto, 'Completed');
    $perfil['conquistas'] = maiorNumeroPorLabel($texto, 'Achievements');
    $perfil['horasJogadas'] = primeiroNumeroPorPadrao($texto, [
        '/([0-9][0-9.,]*)h\s*Playtime/i',
        '/([0-9][0-9.,]*)\s*hours?\s*played/i',
        '/Playtime\s*([0-9][0-9.,]*)h/i'
    ]);

    $perfil['perfilVerificado'] = primeiroNumeroPorPadrao($texto, ['/([0-9][0-9.,]*)%\s*Verified/i']);
    if ($perfil['perfilVerificado']) {
        $perfil['perfilVerificado'] .= '%';
    } elseif (stripos($texto, 'Verified') !== false || stripos($texto, 'Verificado') !== false) {
        $perfil['perfilVerificado'] = 'Sim';
    }

    $generos = extrairGeneros($texto);
    $perfil['generosPrincipais'] = $generos;
    $perfil['genero'] = $generos;

    $plataformas = extrairPlataformas($texto);
    $perfil['playstation'] = $plataformas['playstation'];
    $perfil['xbox'] = $plataformas['xbox'];
    $perfil['pc'] = $plataformas['pc'];
    $perfil['nintendo'] = $plataformas['nintendo'];
    $perfil['plataformaPrincipal'] = $plataformas['principal'];
    $perfil['plataforma'] = $plataformas['principal'];

    return $perfil;
}

function jogosYourGamerProfile(string $url): array {
    $urls = [$url];
    $base = rtrim(preg_replace('#/(profile|stats|reviews|gallery|badges|activity).*$#', '', $url), '/');

    foreach (['/lists/backlog', '/games', '/collection', '/profile'] as $sufixo) {
        $urls[] = $base . $sufixo;
    }

    $jogos = [];

    foreach (array_values(array_unique($urls)) as $alvo) {
        $html = buscarUrl($alvo);
        if (!$html) {
            continue;
        }

        $jogos = array_merge($jogos, extrairJogosDoHtml($html, gamertagPelaUrl($url)));
    }

    $vistos = [];
    $resultado = [];

    foreach ($jogos as $jogo) {
        $titulo = trim($jogo['titulo'] ?? '');
        if (!$titulo) {
            continue;
        }
        $chave = mb_strtolower($titulo);
        if (!isset($vistos[$chave])) {
            $vistos[$chave] = true;
            $resultado[] = $jogo;
        }
    }

    return array_slice($resultado, 0, 120);
}

function extrairJogosDoHtml(string $html, string $gamertag = ''): array {
    $jogos = [];

    if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $scripts)) {
        foreach ($scripts[1] as $json) {
            $dados = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            $pilha = [$dados];
            while ($pilha) {
                $item = array_pop($pilha);
                if (!is_array($item)) {
                    continue;
                }
                $tipo = $item['@type'] ?? '';
                if ((is_string($tipo) && stripos($tipo, 'VideoGame') !== false) && !empty($item['name'])) {
                    $jogos[] = criarJogoImportado($item['name'], $item['gamePlatform'] ?? '', $item['genre'] ?? '', $item['datePublished'] ?? '', $item['image'] ?? '');
                }
                foreach ($item as $valor) {
                    if (is_array($valor)) {
                        $pilha[] = $valor;
                    }
                }
            }
        }
    }

    if (preg_match_all('/<img[^>]+>/i', $html, $imgs)) {
        foreach ($imgs[0] as $img) {
            $alt = primeiroTextoPorPadrao($img, ['/alt=["\']([^"\']+)["\']/i]);
            $src = primeiroTextoPorPadrao($img, ['/src=["\']([^"\']+)["\']/i]);
            $alt = trim(html_entity_decode($alt, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if (tituloValidoDeJogo($alt, $gamertag)) {
                $jogos[] = criarJogoImportado($alt, '', '', '', $src);
            }
        }
    }

    if (preg_match_all('/(?:game-title|game__title|title-game|data-game-title)[^>]*[>="\']\s*([^<"\']{2,100})/i', $html, $m)) {
        foreach ($m[1] as $titulo) {
            $titulo = trim(html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (tituloValidoDeJogo($titulo, $gamertag)) {
                $jogos[] = criarJogoImportado($titulo);
            }
        }
    }

    return $jogos;
}

function tituloValidoDeJogo(string $titulo, string $gamertag = ''): bool {
    $titulo = trim($titulo);

    if ($titulo === '' || mb_strlen($titulo) < 2 || mb_strlen($titulo) > 90) {
        return false;
    }

    $bloqueados = [
        'your gamer profile', 'landing', 'search', 'supporters', 'sign in', 'profile', 'wishlist',
        'lists', 'gallery', 'stats', 'reviews', 'guides', 'badges', 'activity', 'backlog',
        'image', 'statistics', 'folders', 'likes'
    ];

    $lower = mb_strtolower($titulo);
    if ($gamertag && $lower === mb_strtolower($gamertag)) {
        return false;
    }

    foreach ($bloqueados as $termo) {
        if ($lower === $termo) {
            return false;
        }
    }

    return !preg_match('/^\d+$/', $titulo);
}

function criarJogoImportado(string $titulo, $plataforma = '', $genero = '', string $ano = '', string $capa = ''): array {
    return [
        'id' => 'imp_' . bin2hex(random_bytes(5)) . '_' . time(),
        'titulo' => trim($titulo),
        'plataforma' => is_array($plataforma) ? implode(', ', $plataforma) : trim((string) $plataforma),
        'genero' => is_array($genero) ? implode(', ', $genero) : trim((string) $genero),
        'ano' => preg_match('/\d{4}/', $ano, $m) ? $m[0] : '',
        'desenvolvedora' => '',
        'capa' => is_array($capa) ? ($capa[0] ?? '') : trim((string) $capa),
        'nota' => '',
        'status' => 'nao-iniciado'
    ];
}
