<?php

require_once __DIR__ . '/../partials/header.php';
?>
<div class="row">
    <div class="col-md-8">
        <h2>Detalhes da categoria</h2>
        <?php if (empty($data['categoria'])): ?>
            <div class="alert alert-warning">Categoria não encontrada.</div>
        <?php else: ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= htmlspecialchars((string) $data['categoria']['nome']) ?></h3>
                </div>
                <div class="panel-body">
                    <p><strong>ID:</strong> <?= (int) $data['categoria']['id'] ?></p>
                    <p><strong>Nome:</strong> <?= htmlspecialchars((string) $data['categoria']['nome']) ?></p>
                    <a href="/categorias/editar/<?= (int) $data['categoria']['id'] ?>" class="btn btn-primary">Editar</a>
                    <a href="/categorias/listar" class="btn btn-default">Voltar</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
