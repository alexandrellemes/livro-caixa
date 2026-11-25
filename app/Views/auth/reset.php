<?php

use App\Support\Csrf;

require_once __DIR__ . '/../partials/header.php';

$errors = $data['errors'] ?? [];
$success = $data['success'] ?? '';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Definir nova senha</h3>
            </div>
            <div class="panel-body">
                <?php if ($errors): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div>
                <?php endif; ?>
                <form method="post" action="/redefinir-senha">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generate()) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($data['token'] ?? '')) ?>">
                    <div class="form-group">
                        <label for="password">Nova senha</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/login" class="btn btn-default">Voltar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
