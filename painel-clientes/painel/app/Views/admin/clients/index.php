<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Clientes</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
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
                <tr>
                    <td><?= esc($c['name']) ?></td>
                    <td><?= esc($c['email']) ?></td>
                    <td><?= esc($c['phone'] ?? '—') ?></td>
                    <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <?php if ($c['active']): ?>
                            <span class="badge text-bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inativo</span>
                        <?php endif ?>
                    </td>
                    <td class="text-end">
                        <a href="/admin/clients/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                        <a href="/admin/clients/<?= $c['id'] ?>/login" class="btn btn-sm btn-outline-secondary">Entrar como</a>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($clients)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
