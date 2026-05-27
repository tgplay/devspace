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
                        <th>Cadastro</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                    <?php $ativo = filter_var($c['active'], FILTER_VALIDATE_BOOLEAN) ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $c['id'] ?>"
                                   class="form-check-input row-check">
                        </td>
                        <td><?= esc($c['name']) ?></td>
                        <td><?= esc($c['email']) ?></td>
                        <td><?= esc($c['phone'] ?? '—') ?></td>
                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
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
                                    onclick="toggleCliente(<?= $c['id'] ?>, <?= $ativo ? 'true' : 'false' ?>)">
                                <?= $ativo ? 'Desativar' : 'Ativar' ?>
                            </button>
                            <a href="/admin/clients/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="/admin/clients/<?= $c['id'] ?>/login" class="btn btn-sm btn-outline-secondary">Entrar como</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                    <?php if (empty($clients)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<!-- Form hidden para toggle individual -->
<form method="post" id="toggle-form" style="display:none">
    <?= csrf_field() ?>
</form>

<script>
// Toggle individual
function toggleCliente(id, isActive) {
    const label = isActive ? 'desativar' : 'ativar';
    if (! confirm(`Deseja ${label} este cliente?`)) return;

    const form = document.getElementById('toggle-form');
    form.action = `/admin/clients/${id}/toggle`;
    form.submit();
}

// Selecionar todos
document.getElementById('check-all').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

// Atualizar barra ao marcar/desmarcar
document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar     = document.getElementById('bulk-bar');
    const count   = document.getElementById('selected-count');

    count.textContent = checked.length;

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
