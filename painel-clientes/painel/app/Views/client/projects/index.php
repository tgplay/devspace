<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Meus Projetos</h4>

<?php if (empty($projects)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-folder fs-1 d-block mb-2"></i>
        Nenhum projeto encontrado.
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php
    $badges = [
        'planning'    => ['secondary', 'Planejamento'],
        'development' => ['primary',   'Desenvolvimento'],
        'review'      => ['warning',   'Revisão'],
        'delivered'   => ['success',   'Entregue'],
        'maintenance' => ['info',      'Manutenção'],
    ];
    ?>
    <?php foreach ($projects as $p): ?>
    <?php [$color, $label] = $badges[$p['status']] ?? ['secondary', $p['status']] ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0"><?= esc($p['name']) ?></h6>
                    <span class="badge text-bg-<?= $color ?>"><?= $label ?></span>
                </div>
                <div class="text-muted small mb-3"><?= esc($p['type']) ?></div>
                <div class="mb-1 small d-flex justify-content-between">
                    <span>Progresso</span><span><?= $p['progress'] ?>%</span>
                </div>
                <div class="progress mb-3" style="height:8px">
                    <div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div>
                </div>
                <?php if ($p['deadline']): ?>
                <div class="small text-muted mb-3">
                    <i class="bi bi-calendar me-1"></i> Prazo: <?= date('d/m/Y', strtotime($p['deadline'])) ?>
                </div>
                <?php endif ?>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/app/projects/<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm w-100">Ver detalhes</a>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<?= $this->endSection() ?>
