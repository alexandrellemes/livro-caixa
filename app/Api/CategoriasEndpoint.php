<?php

namespace App\Api;

use App\Services\LivroCaixaService;

final class CategoriasEndpoint
{
    private LivroCaixaService $service;

    public function __construct()
    {
        $this->service = new LivroCaixaService();
    }

    /** GET /categorias?busca= */
    public function listar(ApiRequest $request): array
    {
        $busca = mb_strtolower((string) $request->query('busca', ''));
        $categorias = $this->service->repository()->getCategorias();

        if ($busca !== '') {
            $categorias = array_values(array_filter(
                $categorias,
                static fn (array $c): bool => str_contains(mb_strtolower((string) $c['nome']), $busca)
            ));
        }

        return ['body' => ['data' => array_map([self::class, 'formatar'], $categorias)]];
    }

    /** GET /categorias/{id} */
    public function mostrar(ApiRequest $request): array
    {
        $categoria = $this->buscar($request->intParam('id'));
        $dados = self::formatar($categoria);
        $dados['total_movimentos'] = $this->service->repository()->countMovimentosDaCategoria((int) $categoria['id']);

        return ['body' => ['data' => $dados]];
    }

    /** POST /categorias {"nome"} */
    public function criar(ApiRequest $request): array
    {
        $nome = $this->validarNome($request->body());
        $id = $this->service->criarCategoria($nome);

        header('Location: /' . ApiKernel::PREFIX . '/categorias/' . $id);

        return ['status' => 201, 'body' => ['data' => self::formatar($this->buscar($id))]];
    }

    /** PUT|PATCH /categorias/{id} {"nome"} */
    public function atualizar(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        $this->buscar($id);

        $this->service->atualizarCategoria($id, $this->validarNome($request->body()));

        return ['body' => ['data' => self::formatar($this->buscar($id))]];
    }

    /** DELETE /categorias/{id} */
    public function remover(ApiRequest $request): array
    {
        $id = $request->intParam('id');
        $this->buscar($id);

        $total = $this->service->repository()->countMovimentosDaCategoria($id);
        if ($total > 0) {
            throw new ApiException(409, "A categoria possui {$total} movimento(s) e não pode ser removida.", ['total_movimentos' => $total]);
        }

        $this->service->removerCategoria($id);

        return ['status' => 204];
    }

    public static function formatar(array $categoria): array
    {
        return ['id' => (int) $categoria['id'], 'nome' => (string) $categoria['nome']];
    }

    private function buscar(int $id): array
    {
        return $this->service->buscarCategoria($id) ?? throw ApiException::notFound('Categoria');
    }

    private function validarNome(array $body): string
    {
        $nome = trim((string) ($body['nome'] ?? ''));

        if ($nome === '') {
            throw ApiException::validation(['nome' => 'Informe o nome da categoria.']);
        }
        if (mb_strlen($nome) > 255) {
            throw ApiException::validation(['nome' => 'Máximo de 255 caracteres.']);
        }

        return $nome;
    }
}
