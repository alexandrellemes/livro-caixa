<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Livro Caixa</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/css/fullcalendar.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar">
            <h3>Livro Caixa <small class="sidebar-versao" title="Versão da aplicação">v<?= htmlspecialchars(\App\Support\Version::current()) ?></small></h3>
            <p class="text-muted">Usuário: <?= htmlspecialchars((string) ($_SESSION['auth']['user'] ?? 'visitante')) ?></p>
            <p class="text-muted">Perfil: <?= htmlspecialchars((string) ($_SESSION['auth']['role'] ?? 'viewer')) ?></p>
            <a href="/" class="<?= (($data['activeMenu'] ?? '') === 'dashboard') ? 'active' : '' ?>">Dashboard</a>
            <a href="/categorias/listar" class="<?= (($data['activeMenu'] ?? '') === 'categorias') ? 'active' : '' ?>">Categorias</a>
            <a href="/movimentos/listar" class="<?= (($data['activeMenu'] ?? '') === 'movimentos') ? 'active' : '' ?>">Movimentos</a>
            <?php if (($_SESSION['auth']['role'] ?? '') === 'admin'): ?>
                <a href="/pdv" class="<?= (($data['activeMenu'] ?? '') === 'pdv') ? 'active' : '' ?>">PDV</a>
                <a href="/logs" class="<?= (($data['activeMenu'] ?? '') === 'logs') ? 'active' : '' ?>">Logs</a>
            <?php endif; ?>
            <a href="/logout">Sair</a>
        </div>
        <div class="col-md-10 content">
