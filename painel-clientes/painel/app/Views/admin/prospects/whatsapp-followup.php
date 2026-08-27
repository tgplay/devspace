<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$monthNames = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
];

$groups = [];
foreach ($prospects as $p) {
    $key = $p['whatsapp_followup_at'] ? fmt_dt($p['whatsapp_followup_at'], 'Y-m') : 'sem-data';
    $groups[$key][] = $p;
}
krsort($groups);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/prospects" class="text-muted small">&larr; Prospectos</a>
        <h4 class="mb-0 mt-1">Follow-up WhatsApp</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-success fs-6 px-3"><?= count($prospects) ?> pendente<?= count($prospects) !== 1 ? 's' : '' ?></span>
        <a href="/admin/prospects/whatsapp-followup/export" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exportar CSV
        </a>
    </div>
</div>

<?php if (empty($prospects)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-whatsapp fs-1 text-muted d-block mb-3 opacity-25"></i>
        <h5>Nenhum lead aguardando resposta.</h5>
        <p class="text-muted mb-0">Leads marcados como "Contatado" na fila de abordagem ficam aqui até você confirmar um retorno de verdade.</p>
    </div>
</div>
<?php else: ?>

<?php foreach ($groups as $key => $items): ?>
<?php
if ($key === 'sem-data') {
    $groupLabel = 'Sem data';
} else {
    [$y, $m] = explode('-', $key);
    $groupLabel = $monthNames[(int) $m] . ' de ' . $y;
}
?>
<h6 class="text-muted text-uppercase small fw-bold mt-4 mb-2"><?= $groupLabel ?></h6>
<div class="card mb-3">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Última interação</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $p): ?>
                <?php $lastCall = $lastCalls[$p['id']] ?? null; ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td>
                        <div class="fw-semibold">
                            <?= esc($p['name']) ?>
                            <?php if (! empty($p['maps_url'])): ?>
                            <a href="<?= esc($p['maps_url']) ?>" target="_blank" rel="noopener" class="text-muted ms-1" title="Ver no Google Maps">
                                <i class="bi bi-geo-alt-fill" style="font-size:.75rem"></i>
                            </a>
                            <?php endif ?>
                        </div>
                        <?php if (! empty($p['rating']) || ! empty($p['reviews_count'])): ?>
                        <div class="mt-1">
                            <?php if (! empty($p['rating'])): ?>
                            <span class="badge text-bg-warning text-dark" style="font-size:.7rem">
                                ⭐ <?= number_format((float) $p['rating'], 1) ?>
                            </span>
                            <?php endif ?>
                            <?php if (! empty($p['reviews_count'])): ?>
                            <span class="text-muted" style="font-size:.75rem"><?= number_format((int) $p['reviews_count']) ?> avaliações</span>
                            <?php endif ?>
                        </div>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if (! empty($p['phone'])): ?>
                        <?php $waPhone = preg_replace('/\D/', '', $p['phone']); ?>
                        <a href="https://wa.me/55<?= $waPhone ?>" target="_blank" rel="noopener" class="text-decoration-none">
                            <i class="bi bi-whatsapp text-success me-1"></i><?= esc($p['phone']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($lastCall): ?>
                        <div class="small"><?= fmt_dt($lastCall['created_at'], 'd/m/Y') ?></div>
                        <?php if (! empty($lastCall['notes'])): ?>
                        <div class="text-muted small text-truncate" style="max-width:280px"><?= esc($lastCall['notes']) ?></div>
                        <?php endif ?>
                        <?php else: ?>
                        <span class="text-muted small">Nenhuma interação registrada</span>
                        <?php endif ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="/admin/prospects/<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver ficha">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-primary" onclick="setStatus(<?= $p['id'] ?>, 'contacted')">
                            <i class="bi bi-check-lg me-1"></i>Contatado
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setStatus(<?= $p['id'] ?>, 'lost')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach ?>

<?php endif ?>

<script>
function setStatus(id, status) {
    fetch('/admin/prospects/' + id + '/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'status=' + status,
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                const row = document.getElementById('row-' + id);
                if (row) row.remove();
            }
        });
}
</script>

<?= $this->endSection() ?>
