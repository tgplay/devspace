<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = ['draft' => 'Rascunho', 'sent' => 'Enviado', 'accepted' => 'Aceito', 'closed' => 'Encerrado'];
$statusBadge = ['draft' => 'secondary', 'sent' => 'warning', 'accepted' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Contratos</h4>
    <div class="d-flex gap-2">
        <a href="/admin/contract-templates" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-ruled me-1"></i> Modelos
        </a>
        <a href="/admin/contracts/new" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Novo contrato
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Aceito em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= esc($c['title']) ?></td>
                    <td>
                        <div><?= esc($c['client_name']) ?></div>
                        <div class="text-muted small"><?= esc($c['client_email']) ?></div>
                    </td>
                    <td><?= $c['value'] ? 'R$ ' . number_format((float) $c['value'], 2, ',', '.') : '—' ?></td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$c['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$c['status']] ?? esc($c['status']) ?>
                        </span>
                    </td>
                    <td class="text-nowrap"><?= fmt_dt($c['created_at'], 'd/m/Y') ?></td>
                    <td class="text-nowrap"><?= $c['accepted_at'] ? fmt_dt($c['accepted_at'], 'd/m/Y H:i') : '—' ?></td>
                    <td class="text-end">
                        <a href="/admin/contracts/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($contracts)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-text fs-3 d-block mb-2 opacity-25"></i>
                        Nenhum contrato criado ainda.
                    </td>
                </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
