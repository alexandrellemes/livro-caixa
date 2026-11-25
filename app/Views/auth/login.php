<?php

use App\Support\Csrf;

require_once __DIR__ . '/../partials/header.php';

$errors = $data['errors'] ?? [];
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Acesso ao Livro Caixa</h3>
            </div>
            <div class="panel-body">
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars(implode(' ', (array) $errors)) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generate()) ?>">
                    <div class="form-group">
                        <label for="username">Usuário</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                    <a href="/recuperar-senha" class="btn btn-default">Esqueci a senha</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
