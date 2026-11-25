<?php

namespace App\Controllers;

use App\Models\LogRepository;
use App\Models\PDOConnection;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Logger;
use App\Support\Session;

/**
 * Consulta e manutenção dos logs gravados no banco (somente administradores).
 */
final class LogController
{
    private const POR_PAGINA = 50;

    public function __construct(private LogRepository $repository)
    {
    }

    public static function fromGlobals(): self
    {
        return new self(new LogRepository(PDOConnection::getInstance()));
    }

    public function indexAction(): void
    {
        Auth::requireRole('admin');

        $filtros = [
            'nivel' => $this->filtro('nivel'),
            'acao' => $this->filtro('acao'),
            'usuario' => $this->filtro('usuario'),
            'entidade' => $this->filtro('entidade'),
            'de' => $this->filtroData('de'),
            'ate' => $this->filtroData('ate'),
            'busca' => $this->filtro('busca'),
        ];

        $total = $this->repository->count($filtros);
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));

        $data = [
            'activeMenu' => 'logs',
            'filtros' => $filtros,
            'logs' => $this->repository->search($filtros, self::POR_PAGINA, ($pagina - 1) * self::POR_PAGINA),
            'acoes' => $this->repository->distinctAcoes(),
            'niveis' => array_keys(Logger::LEVELS),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
        ];

        require __DIR__ . '/../Views/logs/index.php';
    }

    public function detalhesAction(int $id): void
    {
        Auth::requireRole('admin');

        $log = $this->repository->find($id);
        if ($log === null) {
            throw new \RuntimeException('Registro de log não encontrado.', 404);
        }

        $data = [
            'activeMenu' => 'logs',
            'log' => $log,
        ];

        require __DIR__ . '/../Views/logs/show.php';
    }

    public function limparAction(): void
    {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::validate($_POST['csrf_token'] ?? null)) {
            Session::setFlash('errors', ['Token de segurança inválido.']);
            header('Location: /logs');
            exit;
        }

        $dias = (int) ($_POST['dias'] ?? 0);
        if ($dias < 1) {
            Session::setFlash('errors', ['Informe um número de dias maior que zero.']);
            header('Location: /logs');
            exit;
        }

        $removidos = $this->repository->purgeOlderThan($dias);
        Logger::warning('logs.limpar', "{$removidos} registro(s) de log com mais de {$dias} dia(s) removido(s).", ['dias' => $dias, 'removidos' => $removidos]);

        Session::setFlash('success', "{$removidos} registro(s) removido(s).");
        header('Location: /logs');
        exit;
    }

    private function filtro(string $chave): string
    {
        return trim((string) ($_GET[$chave] ?? ''));
    }

    private function filtroData(string $chave): string
    {
        $valor = $this->filtro($chave);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : '';
    }
}
