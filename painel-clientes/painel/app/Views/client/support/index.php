<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Suporte</h4>
    <a href="/app/support/new" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo chamado
    </a>
</div>

<?php if (empty($tickets)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-headset fs-1 d-block mb-2"></i>
        Nenhum chamado aberto. Clique em "Novo chamado" para solicitar suporte.
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Assunto</th><th>Status</th><th>Data</th><th></th></tr>
            </thead>
            <tbody>
                <?php
                $statusBadge = ['open' => 'danger', 'attending' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                $statusLabel = ['open' => 'Aberto', 'attending' => 'Em atendimento', 'resolved' => 'Resolvido', 'closed' => 'Fechado'];
                ?>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><?= esc($t['subject']) ?></td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$t['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$t['status']] ?? $t['status'] ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                    <td><a href="/app/support/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>
