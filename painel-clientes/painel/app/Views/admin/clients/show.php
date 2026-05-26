<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/clients" class="text-muted small">&larr; Clientes</a>
        <h4 class="mb-0 mt-1"><?= esc($client['name']) ?></h4>
    </div>
    <a href="/admin/clients/<?= $client['id'] ?>/login" class="btn btn-outline-secondary">
        <i class="bi bi-box-arrow-in-right me-1"></i> Entrar como cliente
    </a>
</div>

<div class="row g-4">
    <!-- Dados do cliente -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Dados</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="text-muted small">E-mail</div>
                    <?= esc($client['email']) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Telefone</div>
                    <?= esc($client['phone'] ?? '—') ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Cadastro</div>
                    <?= date('d/m/Y H:i', strtotime($client['created_at'])) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Status</div>
                    <?= $client['active'] ? '<span class="badge text-bg-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?>
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
                        <td><?= esc($p['name']) ?></td>
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
                        <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
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
                        <td><?= date('d/m/Y', strtotime($i['due_date'])) ?></td>
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

<?= $this->endSection() ?>
