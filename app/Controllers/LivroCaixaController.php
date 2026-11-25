<?php

namespace App\Controllers;

use App\Models\Database;
use App\Models\LivroCaixaRepository;
use App\Models\PDOConnection;
use App\Models\PDORepository;
use App\Services\LivroCaixaService;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Logger;
use App\Support\Mailer;
use App\Support\PasswordReset;
use App\Support\Session;
use App\Support\UserManager;
use App\Validation\FormValidator;

final class LivroCaixaController
{
    public function __construct(private LivroCaixaService $service)
    {
    }

    public static function fromGlobals(): self
    {
        $database = new Database();
        $repository = new LivroCaixaRepository($database);
        $service = new LivroCaixaService($repository);

        return new self($service);
    }

    public function index(): array
    {
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');
        $filtroCategoria = $_GET['filtro_cat'] ?? '';

        return $this->service->listarDados((string) $mes, (string) $ano, (string) $filtroCategoria);
    }

    public function indexAction(): void
    {
        $this->handleAction();
        Auth::requireRole('viewer');
        $data = $this->index();
        $data['activeMenu'] = 'dashboard';
        require __DIR__ . '/../Views/index.php';
    }

    public function loginAction(): void
    {
        $data = [
            'errors' => [],
            'activeMenu' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                $data['errors'] = ['Token de segurança inválido.'];
                require __DIR__ . '/../Views/auth/login.php';
                return;
            }

            if (Auth::attempt((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                header('Location: /');
                exit;
            }

            $data['errors'] = ['Credenciais inválidas.'];
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function categoriasAction(): void
    {
        $this->handleAction();
        $data = $this->index();
        require __DIR__ . '/../Views/index.php';
    }

    public function categoriasListarAction(): void
    {
        $this->handleAction();
        Auth::requireRole('viewer');
        $data = [
            'categorias' => $this->service->listarDados(date('m'), date('Y'))['categorias'],
            'activeMenu' => 'categorias',
        ];
        require __DIR__ . '/../Views/categorias/index.php';
    }

    public function categoriasDetalhesAction(int $id): void
    {
        $this->handleAction();
        Auth::requireRole('viewer');
        $data = [
            'categoria' => $this->service->buscarCategoria($id),
            'activeMenu' => 'categorias',
        ];
        require __DIR__ . '/../Views/categorias/show.php';
    }

    public function categoriasNovoAction(): void
    {
        $this->handleAction();
        Auth::requireRole('admin');
        $data = [
            'activeMenu' => 'categorias',
        ];
        require __DIR__ . '/../Views/categorias/form.php';
    }

    public function categoriasEditarAction(int $id): void
    {
        $this->handleAction();
        Auth::requireRole('admin');
        $data = [
            'categoria' => $this->service->buscarCategoria($id),
            'activeMenu' => 'categorias',
        ];
        require __DIR__ . '/../Views/categorias/form.php';
    }

    public function movimentosAction(): void
    {
        $this->handleAction();
        $data = $this->index();
        require __DIR__ . '/../Views/index.php';
    }

    public function movimentosListarAction(): void
    {
        $this->handleAction();
        Auth::requireRole('viewer');
        $data = [
            'movimentos' => $this->service->listarDados(date('m'), date('Y'))['movimentos'],
            'activeMenu' => 'movimentos',
        ];
        require __DIR__ . '/../Views/movimentos/index.php';
    }

    public function movimentosDetalhesAction(int $id): void
    {
        $this->handleAction();
        Auth::requireRole('viewer');
        $data = [
            'movimento' => $this->service->buscarMovimento($id),
            'activeMenu' => 'movimentos',
        ];
        require __DIR__ . '/../Views/movimentos/show.php';
    }

    public function movimentosNovoAction(): void
    {
        $this->handleAction();
        Auth::requireRole('admin');
        $data = [
            'categorias' => $this->service->listarDados(date('m'), date('Y'))['categorias'],
            'activeMenu' => 'movimentos',
        ];
        require __DIR__ . '/../Views/movimentos/form.php';
    }

    public function movimentosEditarAction(int $id): void
    {
        $this->handleAction();
        Auth::requireRole('admin');
        $data = [
            'movimento' => $this->service->buscarMovimento($id),
            'categorias' => $this->service->listarDados(date('m'), date('Y'))['categorias'],
            'activeMenu' => 'movimentos',
        ];
        require __DIR__ . '/../Views/movimentos/form.php';
    }

    public function categoriasRemoverAction(int $id): void
    {
        Auth::requireRole('admin');
        $this->service->removerCategoria($id);
        Session::setFlash('success', 'Categoria removida com sucesso.');
        header('Location: /categorias/listar');
        exit;
    }

    public function movimentosRemoverAction(int $id): void
    {
        Auth::requireRole('admin');
        $this->service->removerMovimento($id);
        Session::setFlash('success', 'Movimento removido com sucesso.');
        header('Location: /movimentos/listar');
        exit;
    }

    public function recoverAction(): void
    {
        $data = [
            'errors' => [],
            'success' => '',
            'activeMenu' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                $data['errors'] = ['Token de segurança inválido.'];
                require __DIR__ . '/../Views/auth/recover.php';
                return;
            }

            $identifier = trim((string) ($_POST['identifier'] ?? ''));
            $user = UserManager::getUser($identifier);
            if ($user === null) {
                Logger::warning('auth.recuperar_senha', 'Recuperação de senha solicitada para identificador inexistente.', ['identificador' => $identifier]);
                $data['errors'] = ['Usuário ou e-mail não encontrado.'];
                require __DIR__ . '/../Views/auth/recover.php';
                return;
            }

            $token = PasswordReset::createToken($user['username']);
            $link = rtrim((string) (getenv('APP_URL') ?: 'http://localhost'), '/') . '/redefinir-senha/' . $token;
            $message = "Recebemos uma solicitação para redefinir sua senha.\n\nAcesse: {$link}\n\nSe você não fez esta solicitação, ignore este e-mail.";
            $sent = Mailer::send($user['email'] ?? $user['username'], 'Recuperação de senha', $message);

            Logger::log(
                $sent ? Logger::INFO : Logger::ERROR,
                'auth.recuperar_senha',
                $sent ? "Link de recuperação enviado para {$user['username']}." : "Falha ao enviar e-mail de recuperação para {$user['username']}.",
                ['username' => $user['username']]
            );

            if ($sent) {
                $data['success'] = 'Um link de recuperação foi enviado para o e-mail cadastrado.';
            } else {
                $data['errors'] = ['Não foi possível enviar o e-mail. Configure SENDGRID_API_KEY e MAIL_FROM_ADDRESS para habilitar a entrega real.'];
            }
            require __DIR__ . '/../Views/auth/recover.php';
            return;
        }

        require __DIR__ . '/../Views/auth/recover.php';
    }

    public function resetAction(?string $token = null): void
    {
        $data = [
            'errors' => [],
            'success' => '',
            'activeMenu' => '',
            'token' => $token,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                $data['errors'] = ['Token de segurança inválido.'];
                require __DIR__ . '/../Views/auth/reset.php';
                return;
            }

            $password = trim((string) ($_POST['password'] ?? ''));
            if ($password === '') {
                $data['errors'] = ['Informe uma nova senha.'];
                require __DIR__ . '/../Views/auth/reset.php';
                return;
            }

            $token = trim((string) ($_POST['token'] ?? ''));
            $payload = PasswordReset::consumeToken($token);
            if ($payload === null) {
                Logger::warning('auth.redefinir_senha', 'Tentativa de redefinição com token inválido ou expirado.');
                $data['errors'] = ['Token de recuperação inválido ou expirado.'];
                require __DIR__ . '/../Views/auth/reset.php';
                return;
            }

            $user = UserManager::getUser((string) ($payload['identifier'] ?? ''));
            if ($user === null) {
                $data['errors'] = ['Usuário não encontrado.'];
                require __DIR__ . '/../Views/auth/reset.php';
                return;
            }

            UserManager::setPassword($user['username'], $password);
            Logger::info('auth.redefinir_senha', "Senha redefinida para o usuário {$user['username']}.", ['username' => $user['username']]);
            $data['success'] = 'Senha atualizada com sucesso. Você pode entrar com a nova senha.';
        }

        require __DIR__ . '/../Views/auth/reset.php';
    }

    public function handleAction(): void
    {
        $requestPath = $_SERVER['REQUEST_URI'] ?? '/';
        $path = trim(parse_url($requestPath, PHP_URL_PATH), '/');

        if (preg_match('#^categorias/(novo|editar)/?(\d+)?$#', $path, $matches)) {
            if ($matches[1] === 'novo') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                        Session::setFlash('errors', ['Token de segurança inválido.']);
                        header('Location: /categorias/novo');
                        exit;
                    }
                    $errors = FormValidator::validateCategoria($_POST);
                    if ($errors === []) {
                        $this->service->criarCategoria((string) $_POST['nome']);
                        Session::setFlash('success', 'Categoria cadastrada com sucesso.');
                        header('Location: /categorias/listar');
                        exit;
                    }
                    Session::setFlash('errors', $errors);
                    header('Location: /categorias/novo');
                    exit;
                }
            }

            if ($matches[1] === 'editar') {
                $id = (int) ($matches[2] ?? 0);
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                        Session::setFlash('errors', ['Token de segurança inválido.']);
                        header('Location: /categorias/editar/' . $id);
                        exit;
                    }
                    $errors = FormValidator::validateCategoria($_POST);
                    if ($errors === []) {
                        $this->service->atualizarCategoria($id, (string) $_POST['nome']);
                        Session::setFlash('success', 'Categoria atualizada com sucesso.');
                        header('Location: /categorias/listar');
                        exit;
                    }
                    Session::setFlash('errors', $errors);
                    header('Location: /categorias/editar/' . $id);
                    exit;
                }
            }
        }

        if (preg_match('#^movimentos/(novo|editar)/?(\d+)?$#', $path, $matches)) {
            if ($matches[1] === 'novo') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                        Session::setFlash('errors', ['Token de segurança inválido.']);
                        header('Location: /movimentos/novo');
                        exit;
                    }
                    $errors = FormValidator::validateMovimento($_POST);
                    if ($errors === []) {
                        $data = (string) $_POST['data'];
                        [$dia, $mes, $ano] = explode('/', $data);
                        $this->service->criarMovimento([
                            'dia' => $dia,
                            'mes' => $mes,
                            'ano' => $ano,
                            'tipo' => (string) $_POST['tipo'],
                            'descricao' => (string) $_POST['descricao'],
                            'valor' => (string) $_POST['valor'],
                            'categoria_id' => (string) $_POST['categoria'],
                        ]);
                        Session::setFlash('success', 'Movimento cadastrado com sucesso.');
                        header('Location: /movimentos/listar');
                        exit;
                    }
                    Session::setFlash('errors', $errors);
                    header('Location: /movimentos/novo');
                    exit;
                }
            }

            if ($matches[1] === 'editar') {
                $id = (int) ($matches[2] ?? 0);
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
                        Session::setFlash('errors', ['Token de segurança inválido.']);
                        header('Location: /movimentos/editar/' . $id);
                        exit;
                    }
                    $errors = FormValidator::validateMovimento($_POST);
                    if ($errors === []) {
                        $data = (string) $_POST['data'];
                        [$dia, $mes, $ano] = explode('/', $data);
                        $this->service->atualizarMovimento($id, [
                            'dia' => $dia,
                            'mes' => $mes,
                            'ano' => $ano,
                            'tipo' => (string) $_POST['tipo'],
                            'descricao' => (string) $_POST['descricao'],
                            'valor' => (string) $_POST['valor'],
                            'categoria_id' => (string) $_POST['categoria'],
                        ]);
                        Session::setFlash('success', 'Movimento atualizado com sucesso.');
                        header('Location: /movimentos/listar');
                        exit;
                    }
                    Session::setFlash('errors', $errors);
                    header('Location: /movimentos/editar/' . $id);
                    exit;
                }
            }
        }
        if (isset($_GET['sair'])) {
            header('Location: /');
            exit;
        }

        if (isset($_GET['acao']) && $_GET['acao'] === 'apagar') {
            $this->service->removerMovimento((int) $_GET['id']);
            header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&ok=2');
            exit;
        }

        if (isset($_POST['acao']) && $_POST['acao'] === 'editar_cat') {
            $this->service->atualizarCategoria((int) $_POST['id'], (string) $_POST['nome']);
            header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&cat_ok=3');
            exit;
        }

        if (isset($_GET['acao']) && $_GET['acao'] === 'apagar_cat') {
            $this->service->removerCategoria((int) $_GET['id']);
            header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&cat_ok=2');
            exit;
        }

        if (isset($_POST['acao']) && $_POST['acao'] === 'editar_mov') {
            $this->service->atualizarMovimento((int) $_POST['id'], [
                'dia' => (string) $_POST['dia'],
                'tipo' => (string) $_POST['tipo'],
                'categoria_id' => (string) $_POST['categoria'],
                'descricao' => (string) $_POST['descricao'],
                'valor' => (string) $_POST['valor'],
            ]);
            header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&ok=3');
            exit;
        }

        if (isset($_POST['acao']) && $_POST['acao'] === '2') {
            $errors = FormValidator::validateCategoria($_POST);
            if ($errors === []) {
                $this->service->criarCategoria((string) $_POST['nome']);
                Session::setFlash('success', 'Categoria cadastrada com sucesso.');
                header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&cat_ok=1');
            } else {
                Session::setFlash('errors', $errors);
                header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&cat_err=1');
            }
            exit;
        }

        if (isset($_POST['acao']) && $_POST['acao'] === '1') {
            $errors = FormValidator::validateMovimento($_POST);
            if ($errors === []) {
                $data = (string) $_POST['data'];
                [$dia, $mes, $ano] = explode('/', $data);
                $this->service->criarMovimento([
                    'dia' => $dia,
                    'mes' => $mes,
                    'ano' => $ano,
                    'tipo' => (string) $_POST['tipo'],
                    'descricao' => (string) $_POST['descricao'],
                    'valor' => (string) $_POST['valor'],
                    'categoria_id' => (string) $_POST['categoria'],
                ]);
                Session::setFlash('success', 'Movimento cadastrado com sucesso.');
                header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&ok=1');
            } else {
                Session::setFlash('errors', $errors);
                header('Location: ?mes=' . ($_GET['mes'] ?? date('m')) . '&ano=' . ($_GET['ano'] ?? date('Y')) . '&ok=0');
            }
            exit;
        }
    }
}
