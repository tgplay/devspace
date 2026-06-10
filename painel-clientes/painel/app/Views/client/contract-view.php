<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = ['sent' => 'Aguardando aceite', 'accepted' => 'Aceito', 'closed' => 'Encerrado'];
$statusBadge = ['sent' => 'warning', 'accepted' => 'success', 'closed' => 'secondary'];
?>

<div class="mb-4">
    <a href="/app/documents" class="text-muted small">&larr; Documentos</a>
    <div class="d-flex align-items-center gap-3 mt-1">
        <h4 class="mb-0"><?= esc($contract['title']) ?></h4>
        <span class="badge text-bg-<?= $statusBadge[$contract['status']] ?? 'secondary' ?>">
            <?= $statusLabel[$contract['status']] ?? esc($contract['status']) ?>
        </span>
    </div>
</div>

<?php if ($contract['status'] === 'sent'): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>
        <strong>Leia o contrato abaixo e confirme o aceite.</strong><br>
        <span class="small">Ao aceitar, fica registrado seu nome, data/hora e IP de acesso.</span>
    </div>
</div>
<?php elseif ($contract['status'] === 'accepted'): ?>
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle fs-5"></i>
    <div>
        <strong>Contrato aceito em <?= fmt_dt($contract['accepted_at']) ?>.</strong>
    </div>
</div>
<?php endif ?>

<!-- Metadados -->
<div class="row g-3 mb-4">
    <?php if ($contract['value']): ?>
    <div class="col-auto">
        <div class="text-muted small">Valor</div>
        <div class="fw-semibold">R$ <?= number_format((float) $contract['value'], 2, ',', '.') ?></div>
    </div>
    <?php endif ?>
    <?php if ($contract['start_date']): ?>
    <div class="col-auto">
        <div class="text-muted small">Vigência</div>
        <div class="fw-semibold">
            <?= date('d/m/Y', strtotime($contract['start_date'])) ?>
            <?php if ($contract['end_date']): ?>
            até <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
            <?php endif ?>
        </div>
    </div>
    <?php endif ?>
    <div class="col-auto">
        <div class="text-muted small">Emitido em</div>
        <div class="fw-semibold"><?= fmt_dt($contract['created_at'], 'd/m/Y') ?></div>
    </div>
</div>

<!-- Conteúdo do contrato -->
<div class="card mb-4">
    <div class="card-body contract-body" style="font-size:0.95rem; line-height:1.8;">
        <?= $contract['content'] ?>
    </div>
</div>

<!-- Ação de aceite -->
<?php if ($contract['status'] === 'sent'): ?>
<div class="card border-success mb-4">
    <div class="card-body">
        <p class="mb-3">
            Ao clicar em <strong>Li e aceito os termos</strong>, você confirma que leu e concorda com
            todas as cláusulas do contrato acima. Esta ação ficará registrada com data, hora e IP.
        </p>
        <form method="post" action="/app/contracts/<?= $contract['id'] ?>/accept">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success btn-lg"
                    onclick="return confirm('Confirmar o aceite deste contrato?')">
                <i class="bi bi-check-circle me-2"></i> Li e aceito os termos
            </button>
        </form>
    </div>
</div>
<?php endif ?>

<style>
.contract-body h1, .contract-body h2, .contract-body h3 { margin-top: 1.5em; }
.contract-body p { margin-bottom: 1em; }
.contract-body ol, .contract-body ul { padding-left: 1.5em; margin-bottom: 1em; }
@media print {
    .sidebar, nav, .alert, form { display: none !important; }
    .main-content { padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

<?= $this->endSection() ?>
