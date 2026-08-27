<?php
/**
 * Template do e-mail enviado após o cadastro no Mapa da Locação grátis.
 * HTML de e-mail usa tabelas e estilo inline de propósito — é o jeito que
 * funciona de forma consistente em Gmail, Outlook e Apple Mail.
 */

declare(strict_types=1);

function bdl_email_assunto(): string {
  return 'Seu Mapa da Locação chegou 📍';
}

function bdl_email_html(string $nomeCompleto): string {
  $primeiroNome = trim(explode(' ', trim($nomeCompleto))[0] ?? '');
  $saudacao = $primeiroNome !== '' ? htmlspecialchars($primeiroNome, ENT_QUOTES, 'UTF-8') : 'tudo bem';

  $logoUrl = BDL_SITE_URL . '/img/logo.png';
  $advogadoUrl = BDL_SITE_URL . '/img/advogado.png';
  $linkPrecos = BDL_SITE_URL . '/#precos';
  $linkWhats = 'https://wa.me/5585921700545?text=Ol%C3%A1!%20Recebi%20o%20Mapa%20da%20Loca%C3%A7%C3%A3o%20e%20tenho%20uma%20d%C3%BAvida.';

  return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Seu Mapa da Locação</title>
<style>
  @media (max-width:600px){
    .bdl-wrap{ width:100% !important; }
    .bdl-pad{ padding-left:22px !important; padding-right:22px !important; }
    .bdl-hero-title{ font-size:22px !important; }
  }
</style>
</head>
<body style="margin:0; padding:0; background:#F2EDE2; -webkit-text-size-adjust:100%;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2EDE2; padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" class="bdl-wrap" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background:#FFFFFF; border-radius:18px; overflow:hidden; box-shadow:0 24px 60px -24px rgba(11,29,48,.35);">

          <!-- Cabeçalho -->
          <tr>
            <td align="center" style="background:#12161C; padding:28px 24px;">
              <img src="{$logoUrl}" width="30" height="40" alt="Biblioteca da Locação" style="display:block; margin:0 auto 10px;">
              <div style="font-family:Georgia,'Times New Roman',serif; font-weight:700; font-size:16px; letter-spacing:.02em; color:#FFFFFF;">Biblioteca da Locação</div>
              <div style="font-family:'Courier New',monospace; font-size:10.5px; letter-spacing:.14em; text-transform:uppercase; color:#E3AE4B; margin-top:4px;">Reforma Tributária na Locação</div>
            </td>
          </tr>

          <!-- Faixa de destaque -->
          <tr>
            <td style="background:linear-gradient(135deg,#EA6A1F,#C2410C); padding:14px 24px; text-align:center;">
              <span style="font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:700; color:#FFFFFF; letter-spacing:.02em;">📎 Seu PDF está anexado a este e-mail</span>
            </td>
          </tr>

          <!-- Corpo -->
          <tr>
            <td class="bdl-pad" style="padding:36px 40px 8px;">
              <h1 class="bdl-hero-title" style="margin:0 0 18px; font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:1.25; color:#12202E;">Oi, {$saudacao}. Seu Mapa da Locação chegou.</h1>
              <p style="margin:0 0 16px; font-family:Arial,Helvetica,sans-serif; font-size:15.5px; line-height:1.65; color:#45525F;">
                Está em anexo neste e-mail: 2 páginas, frente e verso. Na frente, os dois critérios que definem se o seu aluguel entra na cobrança do novo IBS/CBS. No verso, a linha do tempo da reforma até 2033.
              </p>
              <p style="margin:0 0 16px; font-family:Arial,Helvetica,sans-serif; font-size:15.5px; line-height:1.65; color:#45525F;">
                Guarde este e-mail: é por ele que eu aviso quando o cronograma da reforma mudar de novo — e ele <strong style="color:#12202E;">já mudou duas vezes só em 2026</strong>. Prefiro te avisar a deixar você descobrir tarde demais.
              </p>

              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0;">
                <tr>
                  <td style="background:#1C7A34; border-radius:10px;">
                    <a href="{$linkPrecos}" target="_blank" style="display:inline-block; padding:14px 26px; font-family:Arial,Helvetica,sans-serif; font-size:15px; font-weight:700; color:#FFFFFF; text-decoration:none;">Ver o guia completo →</a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 30px; font-family:Arial,Helvetica,sans-serif; font-size:14px; line-height:1.6; color:#7C8794;">
                Se o mapa te deixou com mais perguntas do que respostas, o <strong style="color:#45525F;">Guia Visual da Reforma na Locação</strong> tem as 30 páginas completas: fundamentos, enquadramento, linha do tempo oficial e o checklist do que fazer antes de dezembro de 2026 — por R$ 47.
              </p>
            </td>
          </tr>

          <!-- Assinatura do advogado -->
          <tr>
            <td class="bdl-pad" style="padding:0 40px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FBF9F4; border:1px solid #E4DECE; border-radius:14px;">
                <tr>
                  <td width="86" style="padding:20px 0 20px 20px;" valign="top">
                    <img src="{$advogadoUrl}" width="64" height="64" alt="Advogado imobiliário responsável pelo conteúdo" style="display:block; border-radius:50%; object-fit:cover; width:64px; height:64px;">
                  </td>
                  <td style="padding:20px 20px 20px 16px;" valign="top">
                    <div style="font-family:Arial,Helvetica,sans-serif; font-size:14.5px; font-weight:700; color:#12202E;">Escrito e revisado por advogado imobiliário</div>
                    <div style="font-family:'Courier New',monospace; font-size:11px; letter-spacing:.06em; text-transform:uppercase; color:#C2410C; margin:3px 0 8px;">Biblioteca da Locação</div>
                    <div style="font-family:Arial,Helvetica,sans-serif; font-size:13.5px; line-height:1.55; color:#45525F;">Qualquer dúvida sobre o mapa, é só responder este e-mail ou chamar no WhatsApp.</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Rodapé -->
          <tr>
            <td style="background:#12161C; padding:26px 40px; text-align:center;">
              <a href="{$linkWhats}" target="_blank" style="display:inline-block; margin-bottom:14px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:600; color:#E3AE4B; text-decoration:none;">Falar no WhatsApp</a>
              <p style="margin:0 0 8px; font-family:Arial,Helvetica,sans-serif; font-size:11.5px; line-height:1.6; color:#8695A6;">
                Você recebeu este e-mail porque baixou o Mapa da Locação em bibliotecadalocacao.com.br e autorizou o envio de comunicações sobre a Reforma Tributária na locação.
              </p>
              <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#5B6774;">
                Este é um material informativo e não substitui consultoria jurídica ou contábil individualizada. · © 2026 Biblioteca da Locação
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

function bdl_email_texto(string $nomeCompleto): string {
  $primeiroNome = trim(explode(' ', trim($nomeCompleto))[0] ?? '');
  $saudacao = $primeiroNome !== '' ? $primeiroNome : 'tudo bem';

  return "Oi, {$saudacao}. Seu Mapa da Locação chegou (em anexo, neste e-mail).\n\n"
    . "2 páginas, frente e verso: os dois critérios que definem se o seu aluguel entra na cobrança do novo IBS/CBS, e a linha do tempo da reforma até 2033.\n\n"
    . "Guarde este e-mail: é por ele que eu aviso quando o cronograma da reforma mudar de novo — e ele já mudou duas vezes só em 2026.\n\n"
    . "Quer o guia completo (30 páginas, R$ 47)? " . BDL_SITE_URL . "/#precos\n\n"
    . "Qualquer dúvida, é só responder este e-mail ou chamar no WhatsApp: https://wa.me/5585921700545\n\n"
    . "— Biblioteca da Locação\n"
    . "Este é um material informativo e não substitui consultoria jurídica ou contábil individualizada.";
}
