<?php

use App\Support\Csrf;
use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
?>
<div class="row">
    <div class="col-md-8">
        <h2><?= isset($data['movimento']) ? 'Editar movimento' : 'Novo movimento' ?></h2>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars((string) $success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= htmlspecialchars(implode(' ', (array) $errors)) ?></div><?php endif; ?>
        <form method="post" action="/movimentos/<?= isset($data['movimento']) ? 'editar/' . (int) $data['movimento']['id'] : 'novo' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generate()) ?>">
            <div class="form-group">
                <label for="data">Data</label>
                <input type="text" id="data" name="data" class="form-control" value="<?= isset($data['movimento']) ? htmlspecialchars($data['movimento']['dia'] . '/' . $data['movimento']['mes'] . '/' . $data['movimento']['ano']) : date('d/m/Y') ?>" />
            </div>
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control">
                    <option value="1" <?= (isset($data['movimento']) && (int) $data['movimento']['tipo'] === 1) ? 'selected' : '' ?>>Receita</option>
                    <option value="0" <?= (isset($data['movimento']) && (int) $data['movimento']['tipo'] === 0) ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" class="form-control">
                    <?php foreach ($data['categorias'] as $categoria): ?>
                        <option value="<?= (int) $categoria['id'] ?>" <?= (isset($data['movimento']) && (int) $data['movimento']['categoria_id'] === (int) $categoria['id']) ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" name="descricao" class="form-control" value="<?= isset($data['movimento']) ? htmlspecialchars($data['movimento']['descricao']) : '' ?>" />
            </div>
            <div class="form-group">
                <label for="valor">Valor</label>
                <input type="text" id="valor" name="valor" class="form-control" data-symbol="R$ " data-thousands="." data-decimal="," value="<?= isset($data['movimento']) ? htmlspecialchars(number_format((float) $data['movimento']['valor'], 2, ',', '.')) : '' ?>" />
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="/movimentos/listar" class="btn btn-default">Voltar</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
