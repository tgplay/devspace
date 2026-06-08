<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/clients" class="text-muted small">&larr; Clientes</a>
        <h4 class="mb-0 mt-1" id="page-title"><?= esc($client['name']) ?></h4>
    </div>
    <div class="d-flex gap-2">
        <button type="button" id="btn-salvar-tudo" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i> Salvar
        </button>
        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-reset-senha">
            <i class="bi bi-key me-1"></i> Redefinir senha
        </button>
        <a href="/admin/clients/<?= $client['id'] ?>/login" class="btn btn-outline-secondary">
            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar como cliente
        </a>
    </div>
</div>

<!-- Modal redefinir senha -->
<div class="modal fade" id="modal-reset-senha" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Redefinir senha</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small">Nova senha <span class="text-muted">(mín. 6 caracteres)</span></label>
                <div class="input-group">
                    <input type="password" id="nova-senha" class="form-control" placeholder="Nova senha">
                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-senha">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="senha-feedback" class="form-text text-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" id="btn-confirmar-reset">Redefinir</button>
            </div>
        </div>
    </div>
</div>

<!-- Notificação topo centralizado -->
<div id="notif-bar" class="position-fixed top-0 start-50 translate-middle-x mt-3 d-none" style="z-index:1100;min-width:320px;max-width:520px">
    <div id="notif-inner" class="alert shadow d-flex align-items-center gap-2 mb-0 py-3 px-4" role="alert">
        <i id="notif-icon" class="bi fs-5"></i>
        <span id="notif-msg" class="fw-semibold flex-grow-1"></span>
        <button type="button" class="btn-close ms-2" onclick="hideNotif()"></button>
    </div>
</div>

<div class="row g-4">
    <!-- Dados do cliente -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Dados</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <label class="text-muted small mb-1" for="field-name">Nome</label>
                    <input type="text" id="field-name" class="form-control form-control-sm"
                           value="<?= esc($client['name']) ?>" required>
                </li>
                <li class="list-group-item">
                    <label class="text-muted small mb-1" for="field-email">E-mail</label>
                    <input type="email" id="field-email" class="form-control form-control-sm"
                           value="<?= esc($client['email']) ?>" required>
                </li>
                <li class="list-group-item">
                    <label class="text-muted small mb-1" for="field-phone">Telefone</label>
                    <input type="text" id="field-phone" class="form-control form-control-sm"
                           value="<?= esc($client['phone'] ?? '') ?>" placeholder="—">
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Cadastro</div>
                    <?= fmt_dt($client['created_at']) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Status</div>
                    <?php $ativo = pg_bool($client['active']) ?>
                    <?= $ativo ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?>
                </li>
            </ul>
        </div>
    </div>

    <!-- Projetos -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Projetos</span>
                <a href="/admin/projects" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Nome</th><th>Tipo</th><th>Status</th><th>Progresso</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td>
                            <input type="text" class="form-control-plaintext form-control-sm project-name-input"
                                   data-id="<?= $p['id'] ?>" value="<?= esc($p['name']) ?>">
                        </td>
                        <td><?= esc($p['type']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= esc($p['status']) ?></span></td>
                        <td>
                            <div class="progress" style="height:6px;min-width:60px">
                                <div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div>
                            </div>
                            <small><?= $p['progress'] ?>%</small>
                        </td>
                        <td><a href="/admin/projects/<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if (empty($projects)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Nenhum projeto.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <!-- Chamados recentes -->
        <div class="card mb-4">
            <div class="card-header">Chamados recentes</div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Assunto</th><th>Status</th><th>Data</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td><?= esc($t['subject']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= esc($t['status']) ?></span></td>
                        <td><?= fmt_dt($t['created_at'], 'd/m/Y') ?></td>
                        <td><a href="/admin/support/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if (empty($tickets)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Nenhum chamado.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <!-- Faturas recentes -->
        <div class="card">
            <div class="card-header">Faturas recentes</div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $i): ?>
                    <?php
                        $badges = ['paid' => 'success', 'pending' => 'warning', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                        $labels = ['paid' => 'Paga', 'pending' => 'Pendente', 'overdue' => 'Vencida', 'cancelled' => 'Cancelada'];
                    ?>
                    <tr>
                        <td><?= esc($i['description']) ?></td>
                        <td>R$ <?= number_format($i['amount'], 2, ',', '.') ?></td>
                        <td><?= fmt_dt($i['due_date'], 'd/m/Y') ?></td>
                        <td><span class="badge text-bg-<?= $badges[$i['status']] ?? 'secondary' ?>"><?= $labels[$i['status']] ?? $i['status'] ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if (empty($invoices)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">Nenhuma fatura.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let _notifTimer = null;

function showToast(message, success = true) {
    const bar   = document.getElementById('notif-bar');
    const inner = document.getElementById('notif-inner');
    const icon  = document.getElementById('notif-icon');
    document.getElementById('notif-msg').textContent = message;

    inner.className = `alert shadow d-flex align-items-center gap-2 mb-0 py-3 px-4 alert-${success ? 'success' : 'danger'}`;
    icon.className  = `bi fs-5 ${success ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}`;

    bar.classList.remove('d-none');

    clearTimeout(_notifTimer);
    if (success) {
        _notifTimer = setTimeout(hideNotif, 3000);
    }
}

function hideNotif() {
    document.getElementById('notif-bar').classList.add('d-none');
}

document.getElementById('btn-salvar-tudo').addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;

    const clientBody = new URLSearchParams({
        name:  document.getElementById('field-name').value.trim(),
        email: document.getElementById('field-email').value.trim(),
        phone: document.getElementById('field-phone').value.trim(),
    });

    const projectBody = new URLSearchParams();
    document.querySelectorAll('.project-name-input').forEach(input => {
        projectBody.append(`projects[${input.dataset.id}]`, input.value.trim());
    });

    const saveClient  = fetch('/admin/clients/<?= $client['id'] ?>/update', { method: 'POST', body: clientBody }).then(r => r.json());
    const hasProjects = document.querySelectorAll('.project-name-input').length > 0;
    const saveProjects = hasProjects
        ? fetch('/admin/projects/bulk-rename', { method: 'POST', body: projectBody }).then(r => r.json())
        : Promise.resolve({ success: true });

    Promise.all([saveClient, saveProjects])
        .then(([clientData, projectData]) => {
            if (clientData.success && projectData.success) {
                showToast('Dados salvos com sucesso!');
                setTimeout(() => { window.location.href = '/admin/clients'; }, 1500);
            } else {
                showToast(clientData.message || 'Erro ao salvar.', false);
                btn.disabled = false;
            }
        })
        .catch(() => {
            showToast('Erro ao salvar.', false);
            btn.disabled = false;
        });
});

document.getElementById('btn-toggle-senha').addEventListener('click', function () {
    const input = document.getElementById('nova-senha');
    const icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});

document.getElementById('btn-confirmar-reset').addEventListener('click', function () {
    const senha    = document.getElementById('nova-senha').value.trim();
    const feedback = document.getElementById('senha-feedback');

    if (senha.length < 6) {
        feedback.textContent = 'A senha deve ter no mínimo 6 caracteres.';
        feedback.classList.remove('d-none');
        return;
    }
    feedback.classList.add('d-none');

    fetch('/admin/clients/<?= $client['id'] ?>/reset-password', {
        method: 'POST',
        body:   new URLSearchParams({ password: senha }),
    })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modal-reset-senha')).hide();
            document.getElementById('nova-senha').value = '';
            showToast(data.message, data.success);
        })
        .catch(() => showToast('Erro ao processar a requisição.', false));
});
</script>

<?= $this->endSection() ?>
