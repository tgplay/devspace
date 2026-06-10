<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/contracts" class="text-muted small">&larr; Contratos</a>
        <h4 class="mb-0 mt-1">Modelos de Contrato</h4>
    </div>
    <a href="/admin/contract-templates/new" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Novo modelo
    </a>
</div>

<?php if (empty($templates)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-file-earmark-ruled fs-3 d-block mb-2 opacity-25"></i>
        Nenhum modelo criado ainda.<br>
        <span class="small">Crie modelos reutilizáveis com as cláusulas padrão dos seus contratos.</span>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($templates as $t): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="fw-semibold mb-1">
                    <i class="bi bi-file-earmark-ruled text-muted me-2"></i><?= esc($t['name']) ?>
                </div>
                <div class="text-muted small">Atualizado em <?= fmt_dt($t['updated_at'], 'd/m/Y') ?></div>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="/admin/contract-templates/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<?= $this->endSection() ?>
