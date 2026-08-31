-- Biblioteca da Locação · leads do Mapa gratuito
-- Rode este script uma vez no SQL Editor do Supabase (Dashboard → SQL Editor → New query → Run).

create table if not exists public.leads_mapa (
  id uuid primary key default gen_random_uuid(),
  nome text not null,
  email text not null,
  telefone text,
  consentimento boolean not null default false,
  origem text not null default 'mapa_gratuito',
  ip text,
  user_agent text,
  email_enviado boolean not null default false,
  email_erro text,
  criado_em timestamptz not null default now(),
  atualizado_em timestamptz not null default now()
);

-- Um e-mail só aparece uma vez: novos envios atualizam o registro em vez de duplicar.
-- O backend (api/subscribe.php) já normaliza o e-mail para minúsculas antes de
-- gravar, então um índice único simples na coluna é suficiente (e é o que o
-- "on_conflict=email" do upsert via PostgREST exige — não funciona com índice
-- funcional em lower(email)).
create unique index if not exists leads_mapa_email_key on public.leads_mapa (email);

-- RLS ligado e SEM policies: só a service_role key (usada apenas no backend PHP,
-- nunca no navegador) consegue ler ou escrever nesta tabela. A chave anon/publishable
-- não tem nenhum acesso, mesmo que vaze.
alter table public.leads_mapa enable row level security;

-- Mantém "atualizado_em" em dia a cada upsert.
create or replace function public.leads_mapa_set_atualizado_em()
returns trigger
language plpgsql
as $$
begin
  new.atualizado_em = now();
  return new;
end;
$$;

drop trigger if exists trg_leads_mapa_atualizado_em on public.leads_mapa;
create trigger trg_leads_mapa_atualizado_em
  before update on public.leads_mapa
  for each row
  execute function public.leads_mapa_set_atualizado_em();
