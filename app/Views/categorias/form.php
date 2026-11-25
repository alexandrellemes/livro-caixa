<?php

use App\Support\Csrf;
use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
?>
<div class="row">
    <div class="col-md-6">
        <h2><?= isset($data['categoria']) ? 'Editar categoria' : 'Nova categoria' ?></h2>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div><?php endif; ?>
        <form method="post" action="/categorias/<?= isset($data['categoria']) ? 'editar/' . (int) $data['categoria']['id'] : 'novo' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generate()) ?>">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?= isset($data['categoria']) ? htmlspecialchars($data['categoria']['nome']) : '' ?>" />
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="/categorias/listar" class="btn btn-default">Voltar</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
