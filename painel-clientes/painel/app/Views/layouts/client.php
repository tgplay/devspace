<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Meu Painel' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <style>
        .sidebar { width: 240px; min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: #a0aec0; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: #16213e; }
        .main-content { flex: 1; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .sidebar .nav-label { font-size: 0.7rem; color: #4a5568; padding: 16px 20px 4px; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar .brand { color: #fff; font-weight: 700; }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary">
            <span class="brand fs-5">Meu Painel</span>
            <div class="text-muted small mt-1"><?= esc(session()->get('user_name')) ?></div>
        </div>
        <nav class="flex-grow-1 pt-2">
            <div class="nav-label">Principal</div>
            <a href="/app" class="<?= uri_string() === 'app' ? 'active' : '' ?>">
                <i class="bi bi-house me-2"></i> Início
            </a>
            <a href="/app/projects" class="<?= str_starts_with(uri_string(), 'app/projects') ? 'active' : '' ?>">
                <i class="bi bi-folder me-2"></i> Meus Projetos
            </a>
            <div class="nav-label">Comunicação</div>
            <a href="/app/support" class="<?= str_starts_with(uri_string(), 'app/support') ? 'active' : '' ?>">
                <i class="bi bi-headset me-2"></i> Suporte
            </a>
            <div class="nav-label">Financeiro</div>
            <a href="/app/financial" class="<?= str_starts_with(uri_string(), 'app/financial') ? 'active' : '' ?>">
                <i class="bi bi-receipt me-2"></i> Faturas
            </a>
            <a href="/app/documents" class="<?= (str_starts_with(uri_string(), 'app/documents') || str_starts_with(uri_string(), 'app/contracts')) ? 'active' : '' ?>">
                <i class="bi bi-file-earmark me-2"></i> Documentos
            </a>
        </nav>
        <div class="p-3 border-top border-secondary">
            <a href="/logout" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sair</a>
        </div>
    </div>
    <div class="main-content">
        <?php if (session()->get('impersonating')): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4 rounded-0 mb-0 px-4 py-2" style="border-left:4px solid #f59e0b">
            <i class="bi bi-eye-fill me-2"></i>
            Você está visualizando o painel como <strong class="mx-1"><?= esc(session()->get('user_name')) ?></strong>.
            <a href="http://localhost:8080/admin/stop-impersonating" class="btn btn-sm btn-warning ms-auto">
                <i class="bi bi-arrow-left me-1"></i> Voltar ao Admin
            </a>
        </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
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
