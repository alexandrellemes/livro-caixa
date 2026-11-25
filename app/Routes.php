<?php

namespace App;

use App\Api\ApiKernel;
use App\Controllers\LivroCaixaController;
use App\Controllers\LogController;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Session;

final class Routes
{
    public static function dispatch(string $path = null): void
    {
        $path ??= parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = trim((string) $path, '/');

        // API REST: autenticação própria por token, respostas em JSON.
        if (ApiKernel::isApiPath($path)) {
            ApiKernel::handle($path);
            return;
        }

        $controller = LivroCaixaController::fromGlobals();

        if ($path === 'login') {
            $controller->loginAction();
            return;
        }

        if ($path === 'recuperar-senha') {
            $controller->recoverAction();
            return;
        }

        if (preg_match('#^redefinir-senha(?:/(.+))?$#', $path, $matches)) {
            $controller->resetAction($matches[1] ?? null);
            return;
        }

        if ($path === 'logout') {
            Auth::logout();
            header('Location: /login');
            exit;
        }

        if (!Auth::isAuthenticated()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'login') {
                $controller->loginAction();
            } else {
                header('Location: /login');
                exit;
            }
        }

        if ($path === 'logs') {
            LogController::fromGlobals()->indexAction();
            return;
        }

        if ($path === 'logs/limpar') {
            LogController::fromGlobals()->limparAction();
            return;
        }

        if (preg_match('#^logs/detalhes/(\d+)$#', $path, $matches)) {
            LogController::fromGlobals()->detalhesAction((int) $matches[1]);
            return;
        }

        $routes = [
            '' => 'indexAction',
            'categorias' => 'categoriasAction',
            'categorias/listar' => 'categoriasListarAction',
            'categorias/novo' => 'categoriasNovoAction',
            'categorias/editar' => 'categoriasListarAction',
            'movimentos' => 'movimentosAction',
            'movimentos/listar' => 'movimentosListarAction',
            'movimentos/novo' => 'movimentosNovoAction',
            'movimentos/editar' => 'movimentosListarAction',
        ];

        if (preg_match('#^categorias/editar/(\d+)$#', $path, $matches)) {
            $controller->categoriasEditarAction((int) $matches[1]);
            return;
        }

        if (preg_match('#^categorias/detalhes/(\d+)$#', $path, $matches)) {
            $controller->categoriasDetalhesAction((int) $matches[1]);
            return;
        }

        if (preg_match('#^categorias/remover/(\\d+)$#', $path, $matches)) {
            $controller->categoriasRemoverAction((int) $matches[1]);
            return;
        }

        if (preg_match('#^movimentos/editar/(\\d+)$#', $path, $matches)) {
            $controller->movimentosEditarAction((int) $matches[1]);
            return;
        }

        if (preg_match('#^movimentos/detalhes/(\\d+)$#', $path, $matches)) {
            $controller->movimentosDetalhesAction((int) $matches[1]);
            return;
        }

        if (preg_match('#^movimentos/remover/(\\d+)$#', $path, $matches)) {
            $controller->movimentosRemoverAction((int) $matches[1]);
            return;
        }

        $action = $routes[$path] ?? null;
        if ($action === null) {
            throw new \RuntimeException('Página não encontrada.', 404);
        }

        $controller->{$action}();
    }
}
