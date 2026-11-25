<?php

use App\Support\Csrf;
use App\Support\Session;

require_once __DIR__ . '/../partials/header.php';

$errors = Session::getFlash('errors') ?? [];
$success = Session::getFlash('success');
$f = $data['filtros'];
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$classeNivel = static fn (string $nivel): string => match ($nivel) {
    'debug' => 'default',
    'info' => 'info',
    'warning' => 'warning',
    'error', 'critical' => 'danger',
    default => 'default',
};

$linkPagina = static function (int $pagina) use ($f): string {
    return '/logs?' . http_build_query(array_filter($f + ['pagina' => $pagina], static fn ($v) => $v !== '' && $v !== null));
};
?>
<div class="row">
    <div class="col-md-12">
        <h2>Logs do sistema</h2>
        <?php if ($success): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= $e(implode(' ', (array) $errors)) ?></div><?php endif; ?>

        <form method="get" action="/logs" class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label for="nivel">Nível</label>
                        <select name="nivel" id="nivel" class="form-control input-sm">
                            <option value="">Todos</option>
                            <?php foreach ($data['niveis'] as $nivel): ?>
                                <option value="<?= $e($nivel) ?>" <?= $f['nivel'] === $nivel ? 'selected' : '' ?>><?= $e($nivel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="acao">Ação</label>
                        <select name="acao" id="acao" class="form-control input-sm">
                            <option value="">Todas</option>
                            <?php foreach ($data['acoes'] as $acao): ?>
                                <option value="<?= $e($acao) ?>" <?= $f['acao'] === $acao ? 'selected' : '' ?>><?= $e($acao) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="usuario">Usuário</label>
                        <input type="text" name="usuario" id="usuario" class="form-control input-sm" value="<?= $e($f['usuario']) ?>">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="de">De</label>
                        <input type="date" name="de" id="de" class="form-control input-sm" value="<?= $e($f['de']) ?>">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="ate">Até</label>
                        <input type="date" name="ate" id="ate" class="form-control input-sm" value="<?= $e($f['ate']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label for="entidade">Entidade</label>
                        <select name="entidade" id="entidade" class="form-control input-sm">
                            <option value="">Todas</option>
                            <?php foreach (['categoria', 'movimento'] as $entidade): ?>
                                <option value="<?= $e($entidade) ?>" <?= $f['entidade'] === $entidade ? 'selected' : '' ?>><?= $e($entidade) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5 form-group">
                        <label for="busca">Texto (mensagem ou contexto)</label>
                        <input type="text" name="busca" id="busca" class="form-control input-sm" value="<?= $e($f['busca']) ?>">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                            <a href="/logs" class="btn btn-default btn-sm">Limpar filtros</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <p class="text-muted"><?= (int) $data['total'] ?> registro(s) encontrado(s).</p>

        <div class="table-responsive">
            <table class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Data/hora</th>
                    <th>Nível</th>
                    <th>Ação</th>
                    <th>Mensagem</th>
                    <th>Usuário</th>
                    <th>IP</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if ($data['logs'] === []): ?>
                    <tr><td colspan="8" class="text-center text-muted">Nenhum registro encontrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($data['logs'] as $log): ?>
                    <tr>
                        <td><?= (int) $log['id'] ?></td>
                        <td style="white-space: nowrap"><?= $e(date('d/m/Y H:i:s', strtotime((string) $log['created_at']))) ?></td>
                        <td><span class="label label-<?= $classeNivel((string) $log['nivel']) ?>"><?= $e($log['nivel']) ?></span></td>
                        <td><code><?= $e($log['acao']) ?></code></td>
                        <td><?= $e($log['mensagem']) ?></td>
                        <td><?= $e($log['usuario'] ?? '—') ?></td>
                        <td><?= $e($log['ip'] ?? '') ?></td>
                        <td><a href="/logs/detalhes/<?= (int) $log['id'] ?>" class="btn btn-link btn-xs">Detalhes</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($data['paginas'] > 1): ?>
            <nav>
                <ul class="pager">
                    <li class="previous <?= $data['pagina'] <= 1 ? 'disabled' : '' ?>">
                        <a href="<?= $data['pagina'] <= 1 ? '#' : $e($linkPagina($data['pagina'] - 1)) ?>">&larr; Mais recentes</a>
                    </li>
                    <li>Página <?= (int) $data['pagina'] ?> de <?= (int) $data['paginas'] ?></li>
                    <li class="next <?= $data['pagina'] >= $data['paginas'] ? 'disabled' : '' ?>">
                        <a href="<?= $data['pagina'] >= $data['paginas'] ? '#' : $e($linkPagina($data['pagina'] + 1)) ?>">Mais antigos &rarr;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <hr>
        <form method="post" action="/logs/limpar" class="form-inline" onsubmit="return confirm('Remover definitivamente os logs antigos?')">
            <input type="hidden" name="csrf_token" value="<?= $e(Csrf::generate()) ?>">
            <label for="dias">Remover logs com mais de</label>
            <input type="number" name="dias" id="dias" min="1" value="90" class="form-control input-sm" style="width: 80px">
            <span>dias</span>
            <button type="submit" class="btn btn-danger btn-sm">Limpar</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
