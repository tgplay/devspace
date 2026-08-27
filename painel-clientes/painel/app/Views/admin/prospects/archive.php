<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = [
    'contacted'          => 'Contatado',
    'qualified'          => 'Qualificado',
    'proposal_sent'      => 'Proposta enviada',
    'won'                => 'Ganho',
    'lost'               => 'Perdido',
    'phone_followup'     => 'Aguardando ligação',
    'whatsapp_followup'  => 'Aguardando resposta',
];
$statusBadge = [
    'contacted'          => 'info',
    'qualified'          => 'warning',
    'proposal_sent'      => 'secondary',
    'won'                => 'success',
    'lost'               => 'danger',
    'phone_followup'     => 'dark',
    'whatsapp_followup'  => 'success',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/prospects" class="text-muted small">&larr; Prospectos</a>
        <h4 class="mb-0 mt-1">Arquivo</h4>
    </div>
</div>

<!-- Busca -->
<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="q" value="<?= esc($query) ?>" class="form-control"
               placeholder="Buscar por nome, empresa, telefone ou e-mail...">
        <?php if ($activeFilter !== 'all'): ?>
        <input type="hidden" name="status" value="<?= esc($activeFilter) ?>">
        <?php endif ?>
        <button type="submit" class="btn btn-outline-secondary">
            <i class="bi bi-search"></i>
        </button>
        <?php if ($query !== ''): ?>
        <a href="/admin/prospects/archive<?= $activeFilter !== 'all' ? '?status='.$activeFilter : '' ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i>
        </a>
        <?php endif ?>
    </div>
</form>

<!-- Filtros de status -->
<div class="mb-3 d-flex flex-wrap gap-2">
    <?php $qParam = $query !== '' ? '&q='.urlencode($query) : '' ?>
    <a href="/admin/prospects/archive<?= $qParam ? '?'.ltrim($qParam,'&') : '' ?>"
       class="btn btn-sm <?= $activeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">
        Todos
    </a>
    <?php foreach ($statusLabel as $key => $label): ?>
    <a href="/admin/prospects/archive?status=<?= $key ?><?= $qParam ?>"
       class="btn btn-sm <?= $activeFilter === $key ? 'btn-'.$statusBadge[$key] : 'btn-outline-'.$statusBadge[$key] ?>">
        <?= $label ?>
    </a>
    <?php endforeach ?>
</div>

<!-- Tabela -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Empresa</th>
                    <th>Status</th>
                    <th>Última interação</th>
                    <th>Atualizado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prospects as $p): ?>
                <?php $lastCall = $lastCalls[$p['id']] ?? null; ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= esc($p['name']) ?></div>
                        <?php if (! empty($p['phone'])): ?>
                        <div class="text-muted small"><?= esc($p['phone']) ?></div>
                        <?php endif ?>
                    </td>
                    <td><?= esc($p['company'] ?? '—') ?></td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$p['status']] ?? 'secondary' ?>">
                            <?= $statusLabel[$p['status']] ?? esc($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($lastCall): ?>
                        <div class="small text-muted"><?= fmt_dt($lastCall['created_at'], 'd/m/Y') ?></div>
                        <?php if (! empty($lastCall['notes'])): ?>
                        <div class="small text-truncate" style="max-width:260px"><?= esc($lastCall['notes']) ?></div>
                        <?php endif ?>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif ?>
                    </td>
                    <td class="text-nowrap small text-muted"><?= fmt_dt($p['updated_at'], 'd/m/Y') ?></td>
                    <td class="text-end">
                        <a href="/admin/prospects/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($prospects)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-archive fs-3 d-block mb-2 opacity-25"></i>
                        Nenhum registro encontrado.
                    </td>
                </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<?php if ($pager && $pager->getPageCount('archive') > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted small">
        Página <?= $pager->getCurrentPage('archive') ?> de <?= $pager->getPageCount('archive') ?>
    </div>
    <?= $pager->links('archive') ?>
</div>
<?php endif ?>

<?= $this->endSection() ?>
