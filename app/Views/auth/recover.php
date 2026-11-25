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
                <h3 class="panel-title">Recuperar senha</h3>
            </div>
            <div class="panel-body">
                <?php if ($errors): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div>
                <?php endif; ?>
                <form method="post" action="/recuperar-senha">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generate()) ?>">
                    <div class="form-group">
                        <label for="identifier">Usuário ou e-mail</label>
                        <input type="text" id="identifier" name="identifier" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar link</button>
                    <a href="/login" class="btn btn-default">Voltar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
