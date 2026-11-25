<?php

namespace App\Controllers;

use App\Services\LivroCaixaService;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Session;

/**
 * PDV: lançamento rápido de receitas/despesas com a data de hoje e cálculo de troco.
 */
final class PdvController
{
    public function __construct(private LivroCaixaService $service)
    {
    }

    public static function fromGlobals(): self
    {
        return new self(new LivroCaixaService());
    }

    public function indexAction(): void
    {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->lancar();
        }

        $data = [
            'activeMenu' => 'pdv',
            'categorias' => $this->service->repository()->getCategorias(),
            'formas' => LivroCaixaService::PDV_FORMAS,
            'ultimaCategoria' => (int) ($_SESSION['pdv']['categoria_id'] ?? 0),
            'ultimaForma' => (string) ($_SESSION['pdv']['forma'] ?? 'dinheiro'),
        ];

        require __DIR__ . '/../Views/pdv/index.php';
    }

    private function lancar(): never
    {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            Session::setFlash('errors', ['Token de segurança inválido. Recarregue a página e tente novamente.']);
            $this->voltar();
        }

        $forma = (string) ($_POST['forma'] ?? '');
        $categoriaId = (int) ($_POST['categoria'] ?? 0);

        try {
            $id = $this->service->lancarPdv(
                (string) ($_POST['valor'] ?? ''),
                $forma,
                $categoriaId,
                (string) ($_POST['observacao'] ?? '')
            );
        } catch (\InvalidArgumentException $e) {
            Session::setFlash('errors', [$e->getMessage()]);
            $this->voltar();
        }

        // Mantém forma e categoria para agilizar o próximo lançamento.
        $_SESSION['pdv'] = ['categoria_id' => $categoriaId, 'forma' => $forma];

        $movimento = $this->service->buscarMovimento($id);
        Session::setFlash('success', sprintf(
            'Lançamento #%d registrado: %s (%s).',
            $id,
            formata_dinheiro((float) ($movimento['valor'] ?? 0)),
            LivroCaixaService::PDV_FORMAS[$forma][1]
        ));

        $this->voltar();
    }

    private function voltar(): never
    {
        header('Location: /pdv');
        exit;
    }
}
