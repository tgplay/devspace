<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Clientes</h4>
</div>

<form method="post" action="/admin/clients/bulk-toggle" id="bulk-form">
    <?= csrf_field() ?>

    <!-- Barra de ações em massa (oculta até selecionar) -->
    <div id="bulk-bar" class="d-none mb-3 p-3 bg-white border rounded d-flex align-items-center gap-3">
        <span class="text-muted small"><span id="selected-count">0</span> selecionado(s)</span>
        <button type="submit" name="action" value="activate"
                class="btn btn-sm btn-success">
            <i class="bi bi-check-circle me-1"></i> Ativar selecionados
        </button>
        <button type="submit" name="action" value="deactivate"
                class="btn btn-sm btn-danger">
            <i class="bi bi-x-circle me-1"></i> Desativar selecionados
        </button>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" class="form-check-input" id="check-all">
                        </th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Cadastro</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                    <?php $ativo = pg_bool($c['active']) ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $c['id'] ?>"
                                   class="form-check-input row-check">
                        </td>
                        <td><?= esc($c['name']) ?></td>
                        <td><?= esc($c['email']) ?></td>
                        <td><?= esc($c['phone'] ?? '—') ?></td>
                        <td>
                            <?php
                            $typeLabels = ['site' => 'Site', 'app' => 'App', 'system' => 'Sistema', 'other' => 'Outro'];
                            $tipos = array_keys($projectTypes[$c['id']] ?? []);
                            if ($tipos):
                                foreach ($tipos as $t):
                            ?>
                                <span class="badge text-bg-info text-white me-1"><?= $typeLabels[$t] ?? esc($t) ?></span>
                            <?php endforeach; else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif ?>
                        </td>
                        <td><?= fmt_dt($c['created_at'], 'd/m/Y') ?></td>
                        <td>
                            <?php if ($ativo): ?>
                                <span class="badge text-bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inativo</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-sm <?= $ativo ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                    data-toggle="client"
                                    data-id="<?= $c['id'] ?>"
                                    data-active="<?= $ativo ? '1' : '0' ?>"
                                    onclick="toggleCliente(this)">
                                <?= $ativo ? 'Desativar' : 'Ativar' ?>
                            </button>
                            <a href="/admin/clients/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="/admin/clients/<?= $c['id'] ?>/login" class="btn btn-sm btn-outline-secondary">Entrar como</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                    <?php if (empty($clients)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<!-- Toast de notificação -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="toast-notif" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toast-msg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, success = true) {
    const toast = document.getElementById('toast-notif');
    toast.className = `toast align-items-center text-bg-${success ? 'success' : 'danger'} border-0`;
    document.getElementById('toast-msg').textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
}

function updateRow(id, active) {
    const row = document.querySelector(`input[name="ids[]"][value="${id}"]`)?.closest('tr');
    if (! row) return;
    const badge = row.querySelector('td:nth-child(6) .badge');
    const btn   = row.querySelector('button[data-toggle]');

    if (badge) {
        badge.className   = `badge text-bg-${active ? 'success' : 'secondary'}`;
        badge.textContent = active ? 'Ativo' : 'Inativo';
    }
    if (btn) {
        btn.className = `btn btn-sm ${active ? 'btn-outline-danger' : 'btn-outline-success'}`;
        btn.textContent   = active ? 'Desativar' : 'Ativar';
        btn.dataset.active = active ? '1' : '0';
    }
}

function toggleCliente(btn) {
    const id        = btn.dataset.id;
    const active    = btn.dataset.active === '1';
    const newActive = ! active;
    const label     = active ? 'desativar' : 'ativar';
    if (! confirm(`Deseja ${label} este cliente?`)) return;

    const body = new URLSearchParams({ active: newActive ? '1' : '0' });

    fetch(`/admin/clients/${id}/toggle`, { method: 'POST', body })
        .then(r => {
            if (! r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(data => {
            showToast(data.message, data.success);
            if (data.success) updateRow(id, data.active);
        })
        .catch(err => showToast('Erro ao processar a requisição. (' + err.message + ')', false));
}

// Bulk toggle via fetch
document.getElementById('bulk-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const action  = e.submitter?.value;
    const checked = [...document.querySelectorAll('.row-check:checked')];

    if (! checked.length || ! action) return;

    const body = new URLSearchParams();
    body.append('action', action);
    checked.forEach(cb => body.append('ids[]', cb.value));

    fetch('/admin/clients/bulk-toggle', { method: 'POST', body })
        .then(r => {
            if (! r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(data => {
            showToast(data.message, data.success);
            if (data.success) {
                data.ids.forEach(id => updateRow(id, data.active));
                checked.forEach(cb => cb.checked = false);
                updateBulkBar();
            }
        })
        .catch(err => showToast('Erro ao processar a requisição. (' + err.message + ')', false));
});

// Selecionar todos
document.getElementById('check-all').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar     = document.getElementById('bulk-bar');
    document.getElementById('selected-count').textContent = checked.length;

    if (checked.length > 0) {
        bar.classList.remove('d-none');
        bar.classList.add('d-flex');
    } else {
        bar.classList.add('d-none');
        bar.classList.remove('d-flex');
    }
}
</script>

<?= $this->endSection() ?>
