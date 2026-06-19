<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Equipe</h4>
    <a href="/admin/team/new" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Novo membro
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Nível de acesso</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td>
                        <?= esc($m['name']) ?>
                        <?php if ($m['id'] == session()->get('user_id')): ?>
                        <span class="badge text-bg-secondary ms-1 small">você</span>
                        <?php endif ?>
                    </td>
                    <td class="text-muted"><?= esc($m['email']) ?></td>
                    <td>
                        <?php if ($m['role'] === 'admin'): ?>
                        <span class="badge text-bg-primary">Administrador</span>
                        <?php else: ?>
                        <span class="badge text-bg-success">Agente de Vendas</span>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($m['active']): ?>
                        <span class="badge text-bg-light text-success border border-success">Ativo</span>
                        <?php else: ?>
                        <span class="badge text-bg-secondary">Inativo</span>
                        <?php endif ?>
                    </td>
                    <td class="text-end">
                        <a href="/admin/team/<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($m['id'] != session()->get('user_id')): ?>
                        <form method="post" action="/admin/team/<?= $m['id'] ?>/delete" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="if(confirm('Remover <?= esc(addslashes($m['name'])) ?>?')) this.closest('form').submit()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($members)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum membro cadastrado.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
