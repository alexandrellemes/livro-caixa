<?php

require_once __DIR__ . '/../partials/header.php';
?>
<div class="row">
    <div class="col-md-8">
        <h2>Detalhes do movimento</h2>
        <?php if (empty($data['movimento'])): ?>
            <div class="alert alert-warning">Movimento não encontrado.</div>
        <?php else: ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= htmlspecialchars((string) $data['movimento']['descricao']) ?></h3>
                </div>
                <div class="panel-body">
                    <p><strong>ID:</strong> <?= (int) $data['movimento']['id'] ?></p>
                    <p><strong>Data:</strong> <?= htmlspecialchars((string) $data['movimento']['dia'] . '/' . $data['movimento']['mes'] . '/' . $data['movimento']['ano']) ?></p>
                    <p><strong>Tipo:</strong> <?= (int) $data['movimento']['tipo'] === 1 ? 'Receita' : 'Despesa' ?></p>
                    <p><strong>Categoria:</strong> <?= htmlspecialchars((string) ($data['movimento']['categoria_id'] ?? '')) ?></p>
                    <p><strong>Valor:</strong> R$ <?= htmlspecialchars((string) $data['movimento']['valor']) ?></p>
                    <a href="/movimentos/editar/<?= (int) $data['movimento']['id'] ?>" class="btn btn-primary">Editar</a>
                    <a href="/movimentos/listar" class="btn btn-default">Voltar</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
