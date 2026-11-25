# Livro Caixa — API REST v1

Base: `http://localhost:8080/api/v1` · Formato: JSON (UTF-8)

- Sucesso: `{"data": ...}` (listas também trazem `"meta"` com paginação).
- Erro: `{"error": {"status": 422, "message": "...", "details": {"campo": "motivo"}}}`.
- `GET /api/v1` lista todas as rotas.

## Autenticação

A API usa **tokens Bearer** (não usa a sessão do navegador).

```bash
# 1. Gerar token com usuário e senha da aplicação (válido por 90 dias por padrão)
curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H 'Content-Type: application/json' \
  -d '{"username": "admin", "password": "SUA_SENHA", "nome": "app-mobile", "expira_em_dias": 30}'
# => {"data": {"token": "3f9c...", "tipo": "Bearer", ...}}   <- guarde o token, ele não é exibido de novo

Exemplo via CLI:

curl -s http://localhost:8080/api/v1
TOKEN=$(curl -s -X POST http://localhost:8080/api/v1/auth/token -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"SUA_SENHA"}' | python3 -c 'import sys,json;print(json.load(sys.stdin)["data"]["token"])')

# 2. Usar o token nas demais chamadas
TOKEN=3f9c...
curl -s http://localhost:8080/api/v1/auth/me -H "Authorization: Bearer $TOKEN"

Exemplo via CLI:
curl -s http://localhost:8080/api/v1/categorias -H "Authorization: Bearer $TOKEN"

curl -s -X POST http://localhost:8080/api/v1/movimentos -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"data":"2026-11-25","tipo":"receita","descricao":"Teste API","valor":1000,"categoria_id":4}'

curl -s -X GET http://localhost:8080/api/v1/movimentos -H "Authorization: Bearer $TOKEN"
curl -s -X GET http://localhost:8080/api/v1/logs -H "Authorization: Bearer $TOKEN"
```

O token é gravado apenas como hash SHA-256 (tabela `api_tokens`, criada automaticamente).
Se o cliente não puder enviar `Authorization`, use o header `X-Api-Token: <token>`.

| Método | Rota | Descrição |
|---|---|---|
| POST | `/auth/token` | Gera token (`username`, `password`, `nome`?, `expira_em_dias`? 1–3650) |
| GET | `/auth/me` | Usuário e perfil do token |
| GET | `/auth/tokens` | Tokens do usuário (sem o valor) |
| DELETE | `/auth/token` | Revoga o token atual (logout) |
| DELETE | `/auth/tokens/{id}` | Revoga outro token do usuário |

**Perfis:** `viewer` só consulta; `admin` consulta e altera; `/logs` é exclusivo de `admin`.

## Categorias

| Método | Rota | Perfil | Corpo |
|---|---|---|---|
| GET | `/categorias?busca=` | viewer | — |
| GET | `/categorias/{id}` | viewer | — (inclui `total_movimentos`) |
| POST | `/categorias` | admin | `{"nome": "Aluguel"}` |
| PUT/PATCH | `/categorias/{id}` | admin | `{"nome": "Moradia"}` |
| DELETE | `/categorias/{id}` | admin | — (409 se houver movimentos) |

```bash
curl -s -X POST http://localhost:8080/api/v1/categorias \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"nome": "Aluguel"}'
```

## Movimentos (lançamentos)

Campos:

| Campo | Formato |
|---|---|
| `data` | `"2026-11-25"` ou `"25/11/2026"` |
| `tipo` | `"receita"` / `"despesa"` (ou `1` / `0`) |
| `descricao` | texto |
| `valor` | número JSON `1000.50` ou texto `"1.000,50"` / `"R$ 1.000,50"` — sempre positivo; o `tipo` define se entra ou sai |
| `categoria_id` | id de categoria existente |

| Método | Rota | Perfil | Observação |
|---|---|---|---|
| GET | `/movimentos` | viewer | filtros: `mes`, `ano`, `categoria_id`, `tipo`, `de`, `ate` (AAAA-MM-DD), `busca`, `pagina`, `por_pagina` (máx. 200) |
| GET | `/movimentos/{id}` | viewer | |
| POST | `/movimentos` | admin | todos os campos obrigatórios |
| PUT | `/movimentos/{id}` | admin | substitui: todos os campos obrigatórios |
| PATCH | `/movimentos/{id}` | admin | altera só os campos enviados |
| DELETE | `/movimentos/{id}` | admin | |

```bash
# Lançar uma receita
curl -s -X POST http://localhost:8080/api/v1/movimentos \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"data": "2026-11-25", "tipo": "receita", "descricao": "Venda", "valor": 1000.00, "categoria_id": 1}'

# Despesas de novembro/2026
curl -s 'http://localhost:8080/api/v1/movimentos?mes=11&ano=2026&tipo=despesa' -H "Authorization: Bearer $TOKEN"

# Corrigir só o valor
curl -s -X PATCH http://localhost:8080/api/v1/movimentos/15 \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d '{"valor": "1.250,00"}'
```

Resposta de um movimento:

```json
{"id": 15, "data": "2026-11-25", "tipo": "receita", "descricao": "Venda", "valor": 1000.0,
 "categoria": {"id": 1, "nome": "Vendas"}}
```

## Resumo

`GET /resumo?mes=11&ano=2026` (viewer) → totais de entradas, saídas e resultado do mês e geral.

## Logs (admin)

| Método | Rota | Observação |
|---|---|---|
| GET | `/logs` | filtros: `nivel`, `acao`, `usuario`, `entidade`, `de`, `ate`, `busca`, `pagina`, `por_pagina` |
| GET | `/logs/{id}` | inclui `contexto` (antes/depois) e dados da requisição |
| DELETE | `/logs?dias=90` | remove logs com mais de N dias |

Todas as alterações feitas pela API são registradas nos logs com o usuário do token.

## Códigos de status

`200` ok · `201` criado (header `Location`) · `204` removido/sem conteúdo · `400` JSON inválido ·
`401` sem token / token inválido · `403` perfil insuficiente · `404` não encontrado ·
`405` método não permitido · `409` conflito (ex.: categoria com movimentos) · `422` validação · `500` erro interno.

## Outros

- **CORS:** para chamar a API de um front-end em outro domínio, defina `API_CORS_ORIGIN` no `.env` (ex.: `https://app.exemplo.com`).
- **Clientes sem PUT/PATCH/DELETE:** envie `POST` com o header `X-HTTP-Method-Override: PATCH`.
