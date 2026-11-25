<?php

require_once __DIR__ . '/../partials/header.php';

$log = $data['log'];
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$contexto = null;
if (!empty($log['contexto'])) {
    $decoded = json_decode((string) $log['contexto'], true);
    $contexto = $decoded === null
        ? (string) $log['contexto']
        : json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$campos = [
    'ID' => $log['id'],
    'Data/hora' => date('d/m/Y H:i:s', strtotime((string) $log['created_at'])),
    'Nível' => $log['nivel'],
    'Ação' => $log['acao'],
    'Entidade' => trim(($log['entidade'] ?? '') . ' #' . ($log['entidade_id'] ?? ''), ' #'),
    'Usuário' => $log['usuario'],
    'IP' => $log['ip'],
    'Requisição' => trim(($log['metodo'] ?? '') . ' ' . ($log['uri'] ?? '')),
    'User agent' => $log['user_agent'],
];
?>
<div class="row">
    <div class="col-md-10">
        <h2>Log #<?= (int) $log['id'] ?></h2>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= $e($log['mensagem']) ?></h3>
            </div>
            <div class="panel-body">
                <dl class="dl-horizontal">
                    <?php foreach ($campos as $rotulo => $valor): ?>
                        <dt><?= $e($rotulo) ?></dt>
                        <dd><?= ($valor === null || $valor === '') ? '<span class="text-muted">—</span>' : $e($valor) ?></dd>
                    <?php endforeach; ?>
                </dl>

                <?php if ($contexto !== null): ?>
                    <h4>Contexto</h4>
                    <pre style="max-height: 500px; overflow: auto"><?= $e($contexto) ?></pre>
                <?php endif; ?>

                <a href="javascript:history.back()" class="btn btn-default">Voltar</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
