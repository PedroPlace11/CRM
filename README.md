# 🚀 CRM

CRM e uma aplicacao web para gestao comercial, acompanhamento de funil de vendas e automacao de follow-up, com interface moderna em Inertia + Vue.

O sistema inclui gestao de entidades e contactos, negociacoes em Kanban, calendario de atividades, envio de propostas por email, formularios publicos para captura de leads, sugestoes com IA e integracao com Google Calendar.

## 📋 Funcionalidades

### 💼 Gestao Comercial (Deals)
- Criacao, consulta, edicao e remocao de negocios.
- Pipeline em Kanban por estagios.
- Filtros por responsavel, estagio, datas e valor.
- Timeline de atividades por negocio.
- Associacao de negocio a entidade e pessoa.

### 👥 Entidades e Contactos
- CRUD de entidades (empresas/organizacoes).
- CRUD de pessoas/contactos.
- Associacao de contactos a entidades.
- Operacao de merge de contactos.

### 📄 Propostas e Email Comercial
- Upload de propostas (PDF e documentos Office/imagem).
- Download e remocao de propostas.
- Envio de proposta por email com anexo.
- Registo do email enviado no historico do negocio.

### 🤖 Follow-ups e Automacao
- Sequencias de follow-up iniciadas automaticamente por estagio.
- Jobs agendados para envio e avaliacao de regras.
- Regras de automacao configuraveis por trigger/action.
- Cancelamento manual de sequencia de follow-up.

### 📅 Calendario e Eventos
- CRUD de eventos de calendario.
- Lembretes de eventos via job agendado.
- Sugestoes aceites podem gerar eventos automaticamente.

### 🧾 Formularios Publicos de Leads
- Criacao e gestao de formularios publicos.
- Publicacao por slug e opcao de embed via iframe.
- Captura de submissoes e criacao de lead.
- Suporte a hCaptcha para protecao anti-bot.

### 🧠 IA no CRM
- Chat com IA por conversa (persistencia de historico).
- Respostas por streaming (SSE) ou JSON.
- Sugestoes de IA com fluxo de decisao (aceitar, adiar, descartar).

### 🔗 Integracoes
- Integracao com Google Calendar (OAuth).
- Sincronizacao manual de eventos para calendario Google.
- Suporte a Service Account Google Calendar (configuravel).

### 🔐 Seguranca e Acesso
- Autenticacao com Laravel Breeze.
- Middleware de 2FA com desafio por sessao.
- Gestao de roles/permissoes com Spatie Permission.
- Areas administrativas para utilizadores e acessos.

### 📊 Dashboard e Analitica
- Indicadores de pipeline, atividades e leads.
- Distribuicao de negocios por estagio.
- Estatisticas de produtos com exportacao Excel.

## 🛠️ Tecnologias Utilizadas

- Backend: Laravel 13, PHP 8.3+
- Frontend: Vue 3 + Inertia.js
- Build: Vite 8
- Styling: Tailwind CSS 4
- Base de Dados: MySQL (default do projeto)
- Filas e Jobs: Laravel Queue (database)
- Email: Resend (configurado por default)
- IA: OpenAI/Groq (OpenAI-compatible)
- Integracoes: Google Calendar
- Testes: Pest PHP

## ⚙️ Como Executar o Projeto

### ✅ 1) Pre-requisitos

Garante que tens instalado:
- PHP 8.3+
- Composer
- Node.js 18+
- npm
- MySQL

### 📦 2) Instalacao rapida

```bash
composer run setup
```

Esse comando ja executa:
- composer install
- copia do .env (se necessario)
- key generate
- migrate
- npm install
- npm run build

### 🔧 3) Configuracao do .env

No ficheiro .env, valida pelo menos:

```env
APP_NAME=CRM
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="CRM"
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx
INBOUND_EMAIL_TOKEN=defina-um-token-forte

OPENAI_API_KEY=
OPENAI_MODEL=gpt-5-nano

GROQ_API_KEY=
GROQ_MODEL=openai/gpt-oss-120b
GROQ_BASE_URL=https://api.groq.com/openai/v1

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

HCAPTCHA_SECRET=
HCAPTCHA_SITEKEY=
```

### 🧪 4) Ambiente de desenvolvimento

Opcao recomendada (server + queue + vite):

```bash
composer run dev
```

Ou em terminais separados:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### ⏱️ 5) Scheduler (producao)

Configura o scheduler do Laravel para correr periodicamente:

```bash
php artisan schedule:run
```

Jobs relevantes no projeto:
- DispatchDueFollowUps (cada 30 min em dias uteis)
- DispatchEventReminders (cada 5 min)
- EvaluateAutomationRules (de hora a hora)
- RunCommercialAgentDaily (dias uteis as 07:30)

## 📩 Email Inbound (Webhook)

Endpoint para resposta de clientes:

```text
POST /webhooks/email/inbound?token=SEU_INBOUND_EMAIL_TOKEN
```

Comportamento ao receber resposta valida:
- marca email relacionado como respondido,
- para sequencia de follow-up ativa,
- regista atividade na timeline do negocio.

Comando util para testar envio:

```bash
php artisan mail:test seu-email@dominio.com
```

## ✅ Testes

```bash
composer test
```

ou

```bash
php artisan test
```

## 📁 Estrutura Base do Projeto

```text
📁 CRM/
|- 📁 app/
|  |- 📁 Http/Controllers/
|  |  |- 📄 DealController.php
|  |  |- 📄 ProposalController.php
|  |  |- 📄 PublicFormController.php
|  |  |- 📄 AiChatController.php
|  |  |- 📄 CalendarEventController.php
|  |  |- 📄 AutomationRuleController.php
|  |  |- 📄 Integrations/GoogleCalendarController.php
|  |  \- 📄 Webhooks/InboundEmailController.php
|  |- 📁 Jobs/
|  |- 📁 Mail/
|  |- 📁 Models/
|  \- 📁 Services/
|- 📁 bootstrap/
|- 📁 config/
|- 📁 database/
|- 📁 resources/
|  \- 📁 js/Pages/
|- 📁 routes/
|  |- 📄 web.php
|  \- 📄 console.php
|- 📁 tests/
|- 📄 composer.json
|- 📄 package.json
\- 📄 README.md
```

## 📝 Observacoes

- A aplicacao usa rotas web com Inertia e paginas Vue.
- Nao existe atualmente um ficheiro routes/api.php neste projeto.
- O acesso principal esta protegido por auth + verificacao de email + middleware de 2FA.
- Existe area admin para gestao de utilizadores, roles e permissoes.
- O projeto usa queue database por default; garante migrations aplicadas para jobs.
