<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/clients" class="card text-bg-primary text-decoration-none h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold"><?= $total_clients ?></div>
                <div class="small">Clientes</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/projects" class="card text-bg-success text-decoration-none h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold"><?= $total_projects ?></div>
                <div class="small">Projetos</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/support" class="card text-bg-warning text-decoration-none h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold"><?= $open_tickets ?></div>
                <div class="small">Chamados abertos</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="/admin/prospects?status=new" class="card text-bg-danger text-decoration-none h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold"><?= $new_prospects ?></div>
                <div class="small">Prospectos novos</div>
            </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Clientes recentes</span>
        <a href="/admin/clients" class="btn btn-sm btn-outline-primary">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Cadastro</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_clients as $c): ?>
                <tr>
                    <td><?= esc($c['name']) ?></td>
                    <td><?= esc($c['email']) ?></td>
                    <td><?= fmt_dt($c['created_at'], 'd/m/Y') ?></td>
                    <td>
                        <a href="/admin/clients/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($recent_clients)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Nenhum cliente ainda.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
