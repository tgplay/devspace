<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Chamados de Suporte</h4>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Assunto</th><th>Cliente</th><th>Prioridade</th><th>Status</th><th>Data</th><th></th></tr>
            </thead>
            <tbody>
                <?php
                $statusBadge = ['open' => 'danger', 'attending' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                $statusLabel = ['open' => 'Aberto', 'attending' => 'Em atendimento', 'resolved' => 'Resolvido', 'closed' => 'Fechado'];
                $prioLabel   = ['low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente'];
                $prioBadge   = ['low' => 'secondary', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                ?>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><?= esc($t['subject']) ?></td>
                    <td><?= esc($t['client_name']) ?></td>
                    <td>
                        <span class="badge text-bg-<?= $prioBadge[$t['priority']] ?? 'secondary' ?>">
                            <?= $prioLabel[$t['priority']] ?? $t['priority'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$t['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$t['status']] ?? $t['status'] ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td><a href="/admin/support/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Atender</a></td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($tickets)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum chamado.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
