<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Olá, <?= esc(session()->get('user_name')) ?> 👋</h4>

<?php if (! empty($pending_invoices)): ?>
<div class="alert alert-warning d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Você tem <strong class="mx-1"><?= count($pending_invoices) ?></strong> fatura(s) pendente(s).
    <a href="/app/financial" class="ms-auto btn btn-sm btn-warning">Ver faturas</a>
</div>
<?php endif ?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Projetos</div>
                <div class="fs-3 fw-bold"><?= count($projects) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Chamados abertos</div>
                <div class="fs-3 fw-bold"><?= $open_tickets ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Meus Projetos</span>
        <a href="/app/projects" class="btn btn-sm btn-outline-primary">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Projeto</th><th>Tipo</th><th>Status</th><th>Progresso</th></tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><a href="/app/projects/<?= $p['id'] ?>"><?= esc($p['name']) ?></a></td>
                    <td><?= esc($p['type']) ?></td>
                    <td>
                        <?php
                        $badges = [
                            'planning'    => 'secondary',
                            'development' => 'primary',
                            'review'      => 'warning',
                            'delivered'   => 'success',
                            'maintenance' => 'info',
                        ];
                        $badge = $badges[$p['status']] ?? 'secondary';
                        ?>
                        <span class="badge text-bg-<?= $badge ?>"><?= esc($p['status']) ?></span>
                    </td>
                    <td>
                        <div class="progress" style="height:8px;min-width:80px">
                            <div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $p['progress'] ?>%</small>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($projects)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Nenhum projeto ainda.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (! empty($notifications)): ?>
<div class="card">
    <div class="card-header">Notificações recentes</div>
    <ul class="list-group list-group-flush">
        <?php foreach ($notifications as $n): ?>
        <li class="list-group-item">
            <strong><?= esc($n['title']) ?></strong>
            <div class="small text-muted"><?= esc($n['message']) ?></div>
        </li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<?= $this->endSection() ?>
