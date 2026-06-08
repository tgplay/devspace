<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Financeiro</h4>

<?php if (empty($invoices)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
        Nenhuma fatura encontrada.
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php
                $statusBadge = ['paid' => 'success', 'pending' => 'warning', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                $statusLabel = ['paid' => 'Paga', 'pending' => 'Pendente', 'overdue' => 'Vencida', 'cancelled' => 'Cancelada'];
                ?>
                <?php foreach ($invoices as $i): ?>
                <tr>
                    <td><?= esc($i['description']) ?></td>
                    <td class="fw-medium">R$ <?= number_format($i['amount'], 2, ',', '.') ?></td>
                    <td><?= fmt_dt($i['due_date'], 'd/m/Y') ?></td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$i['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$i['status']] ?? $i['status'] ?>
                        </span>
                    </td>
                    <td class="d-flex gap-1">
                        <?php if ($i['boleto_url']): ?>
                        <a href="<?= esc($i['boleto_url']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Boleto
                        </a>
                        <?php endif ?>
                        <?php if ($i['nf_url']): ?>
                        <a href="<?= esc($i['nf_url']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark me-1"></i>NF
                        </a>
                        <?php endif ?>
                        <?php if (! $i['boleto_url'] && ! $i['nf_url']): ?>
                        <span class="text-muted small">—</span>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>
