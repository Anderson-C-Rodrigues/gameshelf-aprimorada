<?php
function importarPlayStation(string $onlineId): array {
    return [
        'ok' => false,
        'message' => 'Adaptador reservado para integração PlayStation. Para produção, implemente OAuth/autorização segura no servidor e nunca exponha tokens no navegador.',
        'onlineId' => $onlineId
    ];
}
