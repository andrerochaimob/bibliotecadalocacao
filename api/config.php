<?php
/**
 * Carrega o .env da raiz do site e expõe as configurações do backend.
 * O .env nunca é enviado ao navegador: só este arquivo PHP, rodando no
 * servidor, tem acesso a ele.
 */

declare(strict_types=1);

function bdl_carregar_env(string $caminho): array {
  $valores = [];
  if (!is_file($caminho)) {
    return $valores;
  }
  foreach (file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
    $linha = trim($linha);
    if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
      continue;
    }
    [$chave, $valor] = explode('=', $linha, 2);
    $chave = trim($chave);
    $valor = trim($valor);
    $valor = trim($valor, "\"'");
    $valores[$chave] = $valor;
  }
  return $valores;
}

$bdlEnv = bdl_carregar_env(__DIR__ . '/../.env');

function bdl_env(string $chave, string $padrao = ''): string {
  global $bdlEnv;
  if (isset($bdlEnv[$chave]) && $bdlEnv[$chave] !== '') {
    return $bdlEnv[$chave];
  }
  $doAmbiente = getenv($chave);
  return $doAmbiente !== false && $doAmbiente !== '' ? $doAmbiente : $padrao;
}

define('BDL_SUPABASE_URL', rtrim(bdl_env('SUPABASE_URL'), '/'));
define('BDL_SUPABASE_SERVICE_ROLE_KEY', bdl_env('SUPABASE_SERVICE_ROLE_KEY'));
define('BDL_RESEND_API_KEY', bdl_env('RESEND_API_KEY'));
define('BDL_RESEND_FROM_EMAIL', bdl_env('RESEND_FROM_EMAIL', 'nao-responder@bibliotecadalocacao.com.br'));
define('BDL_RESEND_FROM_NAME', bdl_env('RESEND_FROM_NAME', 'Biblioteca da Locação'));
define('BDL_APP_TOKEN_SECRET', bdl_env('APP_TOKEN_SECRET'));
define('BDL_SITE_URL', 'https://bibliotecadalocacao.com.br');
