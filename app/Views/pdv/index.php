<?php

use App\Support\Csrf;
use App\Support\Session;

$data['scripts'] = ['/js/pdv.js'];

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="row">
    <div class="col-md-6">
        <h2>PDV <small>lançamento rápido — <?= $e(date('d/m/Y')) ?></small></h2>

        <?php if ($success): ?><div class="alert alert-success"><?= $e($success) ?> <a href="/movimentos/listar" class="alert-link">Ver movimentos</a></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= $e(implode(' ', (array) $errors)) ?></div><?php endif; ?>

        <?php if ($data['categorias'] === []): ?>
            <div class="alert alert-warning">Cadastre uma categoria antes de usar o PDV. <a href="/categorias/novo" class="alert-link">Nova categoria</a></div>
        <?php else: ?>
        <form method="post" action="/pdv" id="frmPDV" class="panel panel-default" autocomplete="off">
            <div class="panel-body">
                <input type="hidden" name="csrf_token" value="<?= $e(Csrf::generate()) ?>">

                <div class="form-group">
                    <label>Forma</label>
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <?php foreach ($data['formas'] as $chave => [$tipo, $rotulo]): ?>
                            <?php $ativo = $data['ultimaForma'] === $chave; ?>
                            <label class="btn btn-default<?= $ativo ? ' active' : '' ?>">
                                <input type="radio" name="forma" value="<?= $e($chave) ?>" <?= $ativo ? 'checked' : '' ?>>
                                <?= $e(['dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'despesa' => 'Despesa'][$chave] ?? $rotulo) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pdv-valor">Valor</label>
                    <input type="text" id="pdv-valor" name="valor" class="form-control input-lg" required autofocus
                           data-symbol="R$ " data-thousands="." data-decimal=",">
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <?php foreach ($data['categorias'] as $categoria): ?>
                            <option value="<?= (int) $categoria['id'] ?>" <?= (int) $categoria['id'] === $data['ultimaCategoria'] ? 'selected' : '' ?>><?= $e($categoria['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="observacao">Observação <small class="text-muted">(opcional)</small></label>
                    <input type="text" id="observacao" name="observacao" class="form-control" maxlength="200">
                </div>

                <div id="pdv-troco-box" class="well well-sm">
                    <div class="form-group">
                        <label for="pdv-pago">Valor pago pelo cliente <small class="text-muted">(só para calcular o troco, não é gravado)</small></label>
                        <input type="text" id="pdv-pago" class="form-control"
                               data-symbol="R$ " data-thousands="." data-decimal=",">
                    </div>
                    <p class="lead" style="margin: 0">Troco: <strong id="pdv-troco">R$ 0,00</strong></p>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Lançar</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
