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
                <li class="list-group-item clearfix">
                    <span class="pull-right">
                        <a href="/categorias/detalhes/<?= (int) $categoria['id'] ?>" class="btn btn-default btn-xs">Detalhes</a>
                        <a href="/categorias/editar/<?= (int) $categoria['id'] ?>" class="btn btn-primary btn-xs">Editar</a>
                        <a href="/categorias/remover/<?= (int) $categoria['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Deseja remover esta categoria?')">Remover</a>
                    </span>
                    <?= htmlspecialchars((string) $categoria['nome']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
