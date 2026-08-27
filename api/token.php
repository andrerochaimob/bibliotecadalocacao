<?php
/**
 * Emite um token curto assinado (HMAC) que o formulário do Mapa grátis
 * precisa devolver ao enviar os dados. Isso impede que um robô poste
 * direto em /api/subscribe.php sem antes carregar a página — é uma
 * camada de proteção própria do site, separada das chaves do Supabase
 * e do Resend (que ficam só no servidor e nunca aparecem aqui).
 */

declare(strict_types=1);

require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  http_response_code(405);
  echo json_encode(['erro' => 'method_not_allowed']);
  exit;
}

if (BDL_APP_TOKEN_SECRET === '') {
  http_response_code(500);
  echo json_encode(['erro' => 'server_misconfigured']);
  exit;
}

$ts = time();
$assinatura = hash_hmac('sha256', (string) $ts, BDL_APP_TOKEN_SECRET);

echo json_encode(['ts' => $ts, 'sig' => $assinatura]);
