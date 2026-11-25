# Livro Caixa

Livro Caixa é um sistema em PHP/MySQL para o controle mensal de caixa: categorias, lançamentos de receitas e despesas, resumo mensal e geral, logs de auditoria e uma API REST para integração com outros sistemas e apps.

Teve como ideia inicial o projeto Livro-Caixa de Felipe Ismael Barth ([fibbarth](https://github.com/fibbarth)).

## Novidades da versão 2.1

* **Nova estrutura MVC** (`app/`): roteador, controllers, services, repositórios PDO e views, com `public/` como raiz do servidor web.
* **Interface renovada** com Bootstrap: dashboard mensal, listagens de categorias e movimentos com botões de Detalhes / Editar / Remover e valores formatados em reais (R$ 1.000,00).
* **Perfis de acesso**: `admin` (consulta e altera) e `viewer` (somente consulta).
* **Segurança**: proteção CSRF nos formulários, senhas com hash, consultas com *prepared statements* e páginas de erro 403/404/500.
* **Logs de auditoria em banco de dados**: login/logout, tentativas de acesso negado, recuperação de senha, criação/edição/remoção de categorias e movimentos (com o estado *antes* e *depois*) e erros da aplicação. Tela de consulta em `/logs` (admin) com filtros, paginação e limpeza de registros antigos.
* **API REST v1** (`/api/v1`) com autenticação por token: categorias, movimentos, resumo e logs — veja [docs/API.md](docs/API.md).
* **Ambiente Docker**: PHP 8.2 + Apache e MariaDB 10.11 via `docker compose`.
* Correções: valores monetários gravados corretamente (antes `1.000,00` virava `1`) e edição de movimento passa a salvar a alteração de mês/ano.
* **PDV integrado** (`/pdv`): exige login de admin, grava pelo service (com validação e log) e calcula o troco.
* **Recuperação de senha** por link com token (`/recuperar-senha`), com envio por SendGrid ou SMTP. Antes o e-mail nunca era enviado, porque as configurações do `.env` não eram lidas.
* Removidos arquivos legados inseguros: o antigo `api.php` (sem autenticação, expunha a tabela de usuários), o `pdv.php` (gravava sem login e com SQL concatenado), o `process.php` (trocava a senha de qualquer e-mail, sem token), `recover.php`, `sendgrid.php`, `login.old.php` e a biblioteca phpSecurePages. Os endereços antigos redirecionam para as novas rotas.

## Requisitos

* PHP >= 8.1 (o ambiente Docker usa PHP 8.2) com as extensões `pdo_mysql`, `mysqli`, `mbstring` e `json`
* MySQL 5.7+ ou MariaDB 10.x
* Apache com `mod_rewrite` (o `public/.htaccess` encaminha as rotas para `public/index.php`)
* [Composer](https://getcomposer.org/)

## Instalação com Docker (recomendado)

1. Crie o `.env` a partir do modelo e preencha as senhas (veja [Configuração](#configuração)). Para o Docker use `DB_HOST=app_db` e defina também `DB_ROOT_PASSWORD`:

   ```bash
   cp .env.example .env
   ```
2. Suba os containers:

   ```bash
   docker compose build web
   docker compose up -d
   ```

3. Instale as dependências do PHP (se a pasta `vendor/` não existir):

   ```bash
   docker exec -it app_web composer install
   ```

4. Acesse `http://localhost:8080`.

O banco é criado automaticamente na primeira execução a partir de `docs/livro_caixa.sql`. As tabelas `logs` e `api_tokens` também são criadas automaticamente no primeiro uso. Se o usuário do banco não tiver permissões suficientes, aplique `docs/grants_livro_caixa.sql`.

## Instalação manual

1. Aponte o *DocumentRoot* do Apache para a pasta `public/` e habilite o `mod_rewrite` com `AllowOverride All`. Os aliases de `/css`, `/js`, `/images` e `/fonts` estão em `docker/localhost.conf` como referência.
2. Rode `composer install` na raiz do projeto.
3. Configure o `.env`.
4. Importe `docs/livro_caixa.sql` no MySQL (ou deixe a aplicação criar o banco na primeira execução).

## Configuração

Copie `.env.example` para `.env` e edite os valores. O `.env` contém senhas e chaves e **não deve ser versionado** (já está no `.gitignore`). Não use linhas de comentário no `.env`: toda linha precisa ter o formato `CHAVE=valor`.

| Variável | Descrição |
|---|---|
| `APP_VERSAO` | Opcional: só é usada se o arquivo `VERSION` não existir |
| `APP_ENV` | `development`, `testing` ou `production` (em `development` os erros são exibidos) |
| `APP_URL` | URL pública da aplicação (usada nos links de recuperação de senha) |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexão com o banco (`DB_HOST=app_db` no Docker) |
| `DB_ROOT_PASSWORD` | Senha root do MariaDB (somente Docker) |
| `APP_PASSWORD`, `APP_EMAIL` | Senha e e-mail iniciais do usuário `admin` |
| `APP_VIEWER_PASSWORD`, `APP_VIEWER_EMAIL` | Senha e e-mail iniciais do usuário `viewer` |
| `SENDGRID_API_KEY` | Envio de e-mail pela API do SendGrid (tem prioridade sobre o SMTP) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` | Envio de e-mail por SMTP (PHPMailer), usado quando não há `SENDGRID_API_KEY`. `MAIL_ENCRYPTION`: `tls`, `ssl` ou `none` |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_REPLAYTO_MAIL`, `MAIL_REPLAYTO_NAME` | Remetente e endereço de resposta dos e-mails |
| `LOG_LEVEL` | Nível mínimo gravado nos logs: `debug`, `info`, `warning`, `error` ou `critical` (padrão: `debug` em development, `info` nos demais) |
| `API_CORS_ORIGIN` | Opcional: origem liberada para chamar a API de um navegador (ex.: `https://app.exemplo.com`) |

## Versão

A versão exibida no menu lateral e em `GET /api/v1` vem do arquivo `VERSION` na raiz do projeto. Atualize-o a cada release.

## Acesso

Os usuários ficam em `app/Storage/users.json`, criado automaticamente na primeira execução:

| Usuário | Perfil | Senha inicial |
|---|---|---|
| `admin` | admin | valor de `APP_PASSWORD` (padrão: `123`) |
| `viewer` | viewer | valor de `APP_VIEWER_PASSWORD` (padrão: `viewer123`) |

> **Altere as senhas padrão** antes de usar em produção — defina-as no `.env` antes da primeira execução ou use a recuperação de senha.

## Funcionalidades

| Rota | Descrição | Perfil |
|---|---|---|
| `/` | Dashboard do mês: lançamentos, categorias e resultado | viewer |
| `/categorias/listar` | Categorias (cadastro, edição, remoção) | viewer / admin para alterar |
| `/movimentos/listar` | Movimentos de receita e despesa | viewer / admin para alterar |
| `/pdv` | PDV: lançamento rápido do dia (dinheiro, cartão ou despesa) com cálculo de troco | admin |
| `/logs` | Logs de auditoria com filtros e limpeza | admin |
| `/recuperar-senha` | Recuperação de senha por e-mail | público |
| `/api/v1` | API REST (documentação em [docs/API.md](docs/API.md)) | token |

### API

Exemplo rápido:

```bash
# Gerar token
curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H 'Content-Type: application/json' -d '{"username": "admin", "password": "SUA_SENHA"}'

# Lançar uma receita
curl -s -X POST http://localhost:8080/api/v1/movimentos \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"data": "2026-11-25", "tipo": "receita", "descricao": "Venda", "valor": 1000.00, "categoria_id": 1}'
```

## Estrutura do projeto

```
app/
  Api/           API REST v1 (roteamento, autenticação por token, endpoints)
  Controllers/   Controllers das telas
  Models/        Repositórios (PDO) e conexão com o banco
  Services/      Regras de negócio
  Support/       Autenticação, sessão, CSRF, logs, e-mail, tratamento de erros
  Validation/    Validação de formulários
  Views/         Telas
config/          Leitura do .env e configuração
docker/          Dockerfile e configuração do Apache
docs/            Script do banco, modelagem (MySQL Workbench) e documentação da API
public/          Raiz do servidor web (index.php, .htaccess)
```

## Histórico (versões anteriores)

* Inclusão do Bootstrap e do phpSecurePages
* Autenticação no banco com hashing na senha
* Ajustes para uso mobile e modelagem do banco
* PHPMailer para recuperação de senha
* Visão dos lançamentos com FullCalendar
* PDV (hoje em `/pdv`, integrado à aplicação)
* Login via Facebook SDK, como exemplo
* Início do app Flutter com uso da API

### Frameworks/Bibliotecas
* [twbs/bootstrap](https://github.com/twbs/bootstrap)
* [jquery/jquery](https://github.com/jquery/jquery)
* [jquery/jquery-ui](https://github.com/jquery/jquery-ui)
* [jquery-maskmoney](https://github.com/plentz/jquery-maskmoney)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer) / [SendGrid](https://github.com/sendgrid/sendgrid-php)
* [FullCalendar](http://fullcalendar.io)
* [Facebook](https://developers.facebook.com)

### Tutoriais
#### Facebook
* [Acessando API Facebook](https://imasters.com.br/back-end/acessando-api-facebook-em-php-com-o-php-graph-sdk)

### Créditos
* Felipe Barth fibbarth@gmail.com
* Augusto Cezar Perez augustoperez696@live.com
* Alexandre LLemes - alexandre.llemes@gmail.com
