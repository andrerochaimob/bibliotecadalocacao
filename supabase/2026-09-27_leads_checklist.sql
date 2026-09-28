-- Biblioteca da Locação · leads do Checklist da Locação grátis
-- Rode este script uma vez no SQL Editor do Supabase (Dashboard → SQL Editor → New query → Run).
--
-- O Checklist da Locação deixou de ser vendido (era R$ 9,90 via Hotmart em
-- /checklistdalocacao) e virou isca digital gratuita, capturada por um
-- formulário próprio (nome completo + e-mail + telefone), com envio do PDF
-- por e-mail via Resend. Esta tabela é separada de leads_mapa porque é uma
-- isca diferente, com origem e conteúdo diferentes — misturar as duas dentro
-- da mesma tabela perderia a distinção entre quem pediu o quê.

create table if not exists public.leads_checklist (
  id uuid primary key default gen_random_uuid(),
  nome text not null,
  email text not null,
  telefone text not null,
  consentimento boolean not null default false,
  origem text not null default 'checklist_gratis',
  ip text,
  user_agent text,
  email_enviado boolean not null default false,
  email_erro text,
  criado_em timestamptz not null default now(),
  atualizado_em timestamptz not null default now()
);

-- Um e-mail só aparece uma vez: novos envios atualizam o registro em vez de
-- duplicar (mesmo padrão de leads_mapa — ver api/subscribe.php).
create unique index if not exists leads_checklist_email_key on public.leads_checklist (email);

-- RLS ligado e SEM policies: só a service_role key (usada apenas no backend
-- PHP, nunca no navegador) consegue ler ou escrever nesta tabela.
alter table public.leads_checklist enable row level security;

-- Mantém "atualizado_em" em dia a cada upsert.
create or replace function public.leads_checklist_set_atualizado_em()
returns trigger
language plpgsql
as $$
begin
  new.atualizado_em = now();
  return new;
end;
$$;

drop trigger if exists trg_leads_checklist_atualizado_em on public.leads_checklist;
create trigger trg_leads_checklist_atualizado_em
  before update on public.leads_checklist
  for each row
  execute function public.leads_checklist_set_atualizado_em();
