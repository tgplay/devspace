<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = ['sent' => 'Pendente', 'accepted' => 'Aceito', 'closed' => 'Encerrado'];
$statusBadge = ['sent' => 'warning', 'accepted' => 'success', 'closed' => 'secondary'];
$typeIcon    = ['contract' => 'bi-file-earmark-text', 'briefing' => 'bi-file-earmark-richtext', 'delivery' => 'bi-file-earmark-zip', 'other' => 'bi-file-earmark'];
$typeLabel   = ['contract' => 'Contrato', 'briefing' => 'Briefing', 'delivery' => 'Entrega', 'other' => 'Outro'];
?>

<h4 class="mb-4">Documentos</h4>

<ul class="nav nav-tabs mb-4" id="docs-tabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-contracts">
            <i class="bi bi-file-earmark-text me-1"></i> Contratos
            <?php if (! empty($contracts)): ?>
            <span class="badge text-bg-secondary ms-1"><?= count($contracts) ?></span>
            <?php endif ?>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-files">
            <i class="bi bi-file-earmark me-1"></i> Arquivos
            <?php if (! empty($documents)): ?>
            <span class="badge text-bg-secondary ms-1"><?= count($documents) ?></span>
            <?php endif ?>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Contratos -->
    <div class="tab-pane fade show active" id="tab-contracts">
        <?php if (empty($contracts)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-file-earmark-text fs-1 d-block mb-2 opacity-25"></i>
                Nenhum contrato disponível ainda.
            </div>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($contracts as $c): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-file-earmark-text fs-3 text-muted"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= esc($c['title']) ?></div>
                            <div class="text-muted small">
                                <?php if ($c['value']): ?>
                                R$ <?= number_format((float) $c['value'], 2, ',', '.') ?> &middot;
                                <?php endif ?>
                                <?php if ($c['start_date']): ?>
                                <?= date('d/m/Y', strtotime($c['start_date'])) ?>
                                <?php if ($c['end_date']): ?> até <?= date('d/m/Y', strtotime($c['end_date'])) ?><?php endif ?>
                                &middot;
                                <?php endif ?>
                                Recebido em <?= fmt_dt($c['created_at'], 'd/m/Y') ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-<?= $statusBadge[$c['status']] ?? 'secondary' ?>">
                                <?= $statusLabel[$c['status']] ?? esc($c['status']) ?>
                            </span>
                            <a href="/app/contracts/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <?= $c['status'] === 'sent' ? 'Ver e aceitar' : 'Visualizar' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach ?>
        </div>
        <?php endif ?>
    </div>

    <!-- Arquivos -->
    <div class="tab-pane fade" id="tab-files">
        <?php if (empty($documents)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-file-earmark fs-1 d-block mb-2 opacity-25"></i>
                Nenhum arquivo disponível ainda.
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Nome</th><th>Tipo</th><th>Data</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $d): ?>
                        <tr>
                            <td>
                                <i class="bi <?= $typeIcon[$d['type']] ?? 'bi-file-earmark' ?> me-2 text-muted"></i>
                                <?= esc($d['name']) ?>
                            </td>
                            <td><?= $typeLabel[$d['type']] ?? $d['type'] ?></td>
                            <td><?= fmt_dt($d['created_at'], 'd/m/Y') ?></td>
                            <td>
                                <a href="<?= esc($d['file_path']) ?>" target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Baixar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif ?>
    </div>

</div>

<?= $this->endSection() ?>
