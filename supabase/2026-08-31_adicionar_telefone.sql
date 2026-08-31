-- Biblioteca da Locação · adiciona o campo de telefone aos leads do Mapa gratuito
-- Rode este script uma vez no SQL Editor do Supabase (Dashboard → SQL Editor → New query → Run).
-- Só precisa ser rodado se a tabela public.leads_mapa já existir sem a coluna
-- "telefone" (instalações novas já criam a coluna direto pelo schema.sql).

alter table public.leads_mapa
  add column if not exists telefone text;

-- Sem "not null": os leads já cadastrados antes desta mudança não têm telefone
-- salvo, e forçar a coluna a ser obrigatória agora quebraria essas linhas
-- existentes. A obrigatoriedade do campo continua sendo aplicada no formulário
-- do site e no backend (api/subscribe.php), que passam a exigi-lo a partir de
-- agora para todo novo cadastro.
