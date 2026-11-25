<?php

use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
?>
<div class="row">
    <div class="col-md-12">
        <h2>Categorias</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div><?php endif; ?>
        <p><a href="/categorias/novo" class="btn btn-primary">Nova categoria</a></p>
        <ul class="list-group">
            <?php foreach ($data['categorias'] as $categoria): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <a href="/categorias/detalhes/<?= (int) $categoria['id'] ?>" class="btn btn-link btn-xs">Detalhes</a>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </span>
                    <span>
                        <a href="/categorias/editar/<?= (int) $categoria['id'] ?>" class="btn btn-link btn-xs">Editar</a>
                        <a href="/categorias/remover/<?= (int) $categoria['id'] ?>" class="btn btn-link btn-xs text-danger" onclick="return confirm('Deseja remover esta categoria?')">Remover</a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
