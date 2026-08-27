<?php
/**
 * Recebe o cadastro do pop-up do Mapa da Locação grátis: valida os dados,
 * grava o lead no Supabase (via service_role key, só usada aqui no
 * servidor) e envia o PDF por e-mail através do Resend. As chaves nunca
 * chegam ao navegador — o front-end só fala com este endpoint.
 */

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/email_template.php';

header('Content-Type: application/json; charset=utf-8');

function bdl_responder(int $status, array $corpo) {
  http_response_code($status);
  echo json_encode($corpo);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  bdl_responder(405, ['erro' => 'method_not_allowed']);
}

$bruto = file_get_contents('php://input');
$dados = json_decode($bruto ?: '', true);
if (!is_array($dados)) {
  bdl_responder(400, ['erro' => 'json_invalido']);
}

// Honeypot: campo invisível para humanos. Se veio preenchido, é robô.
if (trim((string) ($dados['website'] ?? '')) !== '') {
  bdl_responder(200, ['ok' => true]); // finge sucesso pro bot, não faz nada
}

// Token anti-spam assinado (ver api/token.php).
$ts = (int) ($dados['ts'] ?? 0);
$sig = (string) ($dados['sig'] ?? '');
$agora = time();
if (BDL_APP_TOKEN_SECRET === '' || $ts <= 0 || $sig === ''
  || $ts > $agora + 60
  || $ts < $agora - 1800
  || !hash_equals(hash_hmac('sha256', (string) $ts, BDL_APP_TOKEN_SECRET), $sig)
) {
  bdl_responder(400, ['erro' => 'token_invalido']);
}

$nome = trim((string) ($dados['nome'] ?? ''));
$email = mb_strtolower(trim((string) ($dados['email'] ?? '')));
$consentimento = (bool) ($dados['consentimento'] ?? false);

$nome = preg_replace('/\s+/', ' ', strip_tags($nome)) ?? '';

if (mb_strlen($nome) < 2 || mb_strlen($nome) > 120) {
  bdl_responder(422, ['erro' => 'nome_invalido']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
  bdl_responder(422, ['erro' => 'email_invalido']);
}
if (!$consentimento) {
  bdl_responder(422, ['erro' => 'consentimento_obrigatorio']);
}

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
if (is_string($ip) && str_contains($ip, ',')) {
  $ip = trim(explode(',', $ip)[0]);
}
$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300);

function bdl_supabase_upsert(string $nome, string $email, ?string $ip = null, ?string $userAgent = null): array {
  $url = BDL_SUPABASE_URL . '/rest/v1/leads_mapa?on_conflict=email';
  $corpo = json_encode([
    'nome' => $nome,
    'email' => $email,
    'consentimento' => true,
    'origem' => 'mapa_gratuito',
    'ip' => $ip,
    'user_agent' => $userAgent,
  ]);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $corpo,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
      'apikey: ' . BDL_SUPABASE_SERVICE_ROLE_KEY,
      'Authorization: Bearer ' . BDL_SUPABASE_SERVICE_ROLE_KEY,
      'Content-Type: application/json',
      'Prefer: resolution=merge-duplicates,return=minimal',
    ],
  ]);
  $resposta = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $erroCurl = curl_error($ch);
  curl_close($ch);

  return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'resposta' => $resposta, 'erro_curl' => $erroCurl];
}

function bdl_supabase_marcar_email(string $email, bool $enviado, ?string $erro = null): void {
  $url = BDL_SUPABASE_URL . '/rest/v1/leads_mapa?email=eq.' . rawurlencode($email);
  $corpo = json_encode(['email_enviado' => $enviado, 'email_erro' => $erro]);

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS => $corpo,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
      'apikey: ' . BDL_SUPABASE_SERVICE_ROLE_KEY,
      'Authorization: Bearer ' . BDL_SUPABASE_SERVICE_ROLE_KEY,
      'Content-Type: application/json',
      'Prefer: return=minimal',
    ],
  ]);
  curl_exec($ch);
  curl_close($ch);
}

function bdl_resend_enviar(string $paraNome, string $paraEmail): array {
  $caminhoPdf = __DIR__ . '/../pdf/Mapa_da_Locacao.pdf';
  if (!is_file($caminhoPdf)) {
    return ['ok' => false, 'erro' => 'pdf_ausente'];
  }

  $payload = [
    'from' => BDL_RESEND_FROM_NAME . ' <' . BDL_RESEND_FROM_EMAIL . '>',
    'to' => [$paraEmail],
    'subject' => bdl_email_assunto(),
    'html' => bdl_email_html($paraNome),
    'text' => bdl_email_texto($paraNome),
    'attachments' => [[
      'filename' => 'Mapa-da-Reforma-Tributaria-na-Locacao.pdf',
      'content' => base64_encode((string) file_get_contents($caminhoPdf)),
    ]],
  ];

  $ch = curl_init('https://api.resend.com/emails');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . BDL_RESEND_API_KEY,
      'Content-Type: application/json',
    ],
  ]);
  $resposta = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $erroCurl = curl_error($ch);
  curl_close($ch);

  return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'resposta' => $resposta, 'erro_curl' => $erroCurl];
}

$upsert = bdl_supabase_upsert($nome, $email, $ip, $userAgent);
if (!$upsert['ok']) {
  error_log('[bdl] Falha ao gravar lead no Supabase: ' . $upsert['status'] . ' ' . $upsert['resposta'] . ' ' . $upsert['erro_curl']);
  bdl_responder(502, ['erro' => 'falha_ao_salvar']);
}

$envio = bdl_resend_enviar($nome, $email);
if ($envio['ok']) {
  bdl_supabase_marcar_email($email, true);
} else {
  $detalhe = $envio['erro'] ?? (($envio['status'] ?? '') . ' ' . ($envio['resposta'] ?? '') . ' ' . ($envio['erro_curl'] ?? ''));
  error_log('[bdl] Falha ao enviar e-mail via Resend: ' . $detalhe);
  bdl_supabase_marcar_email($email, false, substr((string) $detalhe, 0, 500));
}

// O download acontece no navegador de qualquer forma — mesmo que o e-mail
// falhe, a pessoa não fica sem o PDF que veio pedir.
bdl_responder(200, ['ok' => true, 'email_enviado' => $envio['ok']]);
