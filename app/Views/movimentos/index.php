<?php

use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
?>
<div class="row">
    <div class="col-md-12">
        <h2>Movimentos</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div><?php endif; ?>
        <p><a href="/movimentos/novo" class="btn btn-primary">Novo movimento</a></p>
        <ul class="list-group">
            <?php foreach ($data['movimentos'] as $movimento): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong><?= htmlspecialchars((string) $movimento['dia'] . '/' . $movimento['mes'] . '/' . $movimento['ano']) ?></strong>
                        - <?= htmlspecialchars($movimento['descricao']) ?>
                        - <?= htmlspecialchars((string) $movimento['valor']) ?>
                    </span>
                    <span>
                        <a href="/movimentos/detalhes/<?= (int) $movimento['id'] ?>" class="btn btn-link btn-xs">Detalhes</a>
                        <a href="/movimentos/editar/<?= (int) $movimento['id'] ?>" class="btn btn-link btn-xs">Editar</a>
                        <a href="/movimentos/remover/<?= (int) $movimento['id'] ?>" class="btn btn-link btn-xs text-danger" onclick="return confirm('Deseja remover este movimento?')">Remover</a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
