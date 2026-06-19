<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Admin' ?> — Painel Admin</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        .sidebar { width: 240px; min-height: 100vh; background: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: #343a40; }
        .main-content { flex: 1; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .sidebar .nav-label { font-size: 0.7rem; color: #6c757d; padding: 16px 20px 4px; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary">
            <span class="text-white fw-bold fs-5">Admin</span>
            <div class="text-muted small mt-1"><?= esc(session()->get('user_name')) ?></div>
        </div>
        <nav class="flex-grow-1 pt-2">
            <div class="nav-label">Geral</div>
            <a href="/admin" class="<?= uri_string() === 'admin' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="/admin/clients" class="<?= str_starts_with(uri_string(), 'admin/clients') ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i> Clientes
            </a>
            <a href="/admin/projects" class="<?= str_starts_with(uri_string(), 'admin/projects') ? 'active' : '' ?>">
                <i class="bi bi-folder me-2"></i> Projetos
            </a>
            <div class="nav-label">Vendas</div>
            <a href="/admin/prospects" class="<?= str_starts_with(uri_string(), 'admin/prospects') && uri_string() !== 'admin/prospects/queue' ? 'active' : '' ?>">
                <i class="bi bi-person-plus me-2"></i> Prospectos
            </a>
            <a href="/admin/prospects/queue" class="<?= uri_string() === 'admin/prospects/queue' ? 'active' : '' ?>">
                <i class="bi bi-play-circle me-2"></i> Fila de abordagem
            </a>
            <a href="/admin/google-maps-import" class="<?= str_starts_with(uri_string(), 'admin/google-maps-import') ? 'active' : '' ?>">
                <i class="bi bi-geo-alt me-2"></i> Captação Maps
            </a>
            <a href="/admin/contracts" class="<?= str_starts_with(uri_string(), 'admin/contracts') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Contratos
            </a>
            <a href="/admin/contract-templates" class="<?= str_starts_with(uri_string(), 'admin/contract-templates') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-ruled me-2"></i> Modelos
            </a>
            <div class="nav-label">Suporte</div>
            <a href="/admin/support" class="<?= str_starts_with(uri_string(), 'admin/support') ? 'active' : '' ?>">
                <i class="bi bi-headset me-2"></i> Chamados
            </a>
        </nav>
        <div class="p-3 border-top border-secondary">
            <a href="/logout" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sair</a>
        </div>
    </div>
    <div class="main-content">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
                <?php if ($pwd = session()->getFlashdata('temp_password')): ?>
                    <br><code class="user-select-all fs-6"><?= esc($pwd) ?></code>
                    <span class="small ms-1 text-muted">(copie antes de sair)</span>
                <?php endif ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?= $this->renderSection('content') ?>
    </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
