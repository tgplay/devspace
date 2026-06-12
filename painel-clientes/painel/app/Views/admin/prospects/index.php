<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = [
    'new'           => 'Novo',
    'contacted'     => 'Contatado',
    'qualified'     => 'Qualificado',
    'proposal_sent' => 'Proposta enviada',
    'won'           => 'Ganho',
    'lost'          => 'Perdido',
];
$statusBadge = [
    'new'           => 'primary',
    'contacted'     => 'info',
    'qualified'     => 'warning',
    'proposal_sent' => 'secondary',
    'won'           => 'success',
    'lost'          => 'danger',
];
$interestLabel = ['site' => 'Site', 'app' => 'Aplicativo', 'system' => 'Sistema', 'other' => 'Outro'];
$sourceLabel   = ['website' => 'Site', 'referral' => 'Indicação', 'social' => 'Redes sociais', 'email' => 'E-mail', 'other' => 'Outro', 'google_maps' => 'Google Maps'];

$total = array_sum($statusCounts);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Prospectos</h4>
    <div class="d-flex gap-2">
        <a href="/admin/prospects/import" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-upload me-1"></i> Importar CSV
        </a>
        <a href="/admin/prospects/new" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Novo prospecto
        </a>
    </div>
</div>

<!-- Pipeline summary -->
<div class="row g-3 mb-4">
    <?php foreach ($statusLabel as $key => $label): ?>
    <?php $count = $statusCounts[$key] ?? 0; ?>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/admin/prospects?status=<?= $key ?>"
           class="card text-decoration-none h-100 <?= $activeFilter === $key ? 'border-primary shadow-sm' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-4 fw-bold text-<?= $statusBadge[$key] ?>"><?= $count ?></div>
                <div class="small text-muted"><?= $label ?></div>
            </div>
        </a>
    </div>
    <?php endforeach ?>
</div>

<!-- Filtros de status -->
<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <a href="/admin/prospects"
       class="btn btn-sm <?= $activeFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">
        Todos (<?= $total ?>)
    </a>
    <?php foreach ($statusLabel as $key => $label): ?>
    <a href="/admin/prospects?status=<?= $key ?>"
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
                    <th>Interesse</th>
                    <th>Origem</th>
                    <th>Status</th>
                    <th>Entrada</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prospects as $p): ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td>
                        <div class="fw-semibold">
                            <?= esc($p['name']) ?>
                            <?php if (! empty($p['maps_url'])): ?>
                            <a href="<?= esc($p['maps_url']) ?>" target="_blank" rel="noopener"
                               class="text-muted ms-1" title="Ver no Google Maps">
                                <i class="bi bi-geo-alt-fill" style="font-size:.75rem"></i>
                            </a>
                            <?php endif ?>
                        </div>
                        <?php if (! empty($p['email'])): ?>
                        <div class="text-muted small"><?= esc($p['email']) ?></div>
                        <?php endif ?>
                        <?php if (! empty($p['rating']) || ! empty($p['reviews_count'])): ?>
                        <div class="mt-1">
                            <?php if (! empty($p['rating'])): ?>
                            <span class="badge text-bg-warning text-dark" style="font-size:.7rem">
                                ⭐ <?= number_format((float)$p['rating'], 1) ?>
                            </span>
                            <?php endif ?>
                            <?php if (! empty($p['reviews_count'])): ?>
                            <span class="text-muted" style="font-size:.75rem">
                                <?= number_format((int)$p['reviews_count']) ?> avaliações
                            </span>
                            <?php endif ?>
                        </div>
                        <?php endif ?>
                    </td>
                    <td><?= esc($p['company'] ?? '—') ?></td>
                    <td><?= $interestLabel[$p['interest']] ?? esc($p['interest']) ?></td>
                    <td>
                        <?php if ($p['source'] === 'google_maps'): ?>
                        <span class="badge text-bg-light border text-dark" style="font-size:.75rem">
                            <i class="bi bi-geo-alt me-1"></i>Google Maps
                        </span>
                        <?php else: ?>
                        <?= $sourceLabel[$p['source']] ?? esc($p['source']) ?>
                        <?php endif ?>
                    </td>
                    <td>
                        <select class="form-select form-select-sm status-select"
                                data-id="<?= $p['id'] ?>"
                                style="min-width:150px">
                            <?php foreach ($statusLabel as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $p['status'] === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach ?>
                        </select>
                    </td>
                    <td class="text-nowrap"><?= fmt_dt($p['created_at'], 'd/m/Y') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="/admin/prospects/<?= $p['id'] ?>"
                           class="btn btn-sm btn-outline-primary">Ver</a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($prospects)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-person-plus fs-3 d-block mb-2 opacity-25"></i>
                        Nenhum prospecto encontrado.
                    </td>
                </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="toast-notif" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toast-msg"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, success) {
    const t = document.getElementById('toast-notif');
    t.className = 'toast align-items-center border-0 text-bg-' + (success ? 'success' : 'danger');
    document.getElementById('toast-msg').textContent = message;
    bootstrap.Toast.getOrCreateInstance(t, { delay: 3000 }).show();
}

document.querySelectorAll('.status-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
        const id     = this.dataset.id;
        const status = this.value;
        const body   = new URLSearchParams({ status });

        fetch('/admin/prospects/' + id + '/status', { method: 'POST', body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                showToast(data.success ? 'Status atualizado.' : data.message, data.success);
            })
            .catch(function () { showToast('Erro ao atualizar status.', false); });
    });
});
</script>

<?= $this->endSection() ?>
