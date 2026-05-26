<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Projetos</h4>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>Cliente</th><th>Tipo</th><th>Status</th><th>Progresso</th><th>Prazo</th><th></th></tr>
            </thead>
            <tbody>
                <?php
                $badges = [
                    'planning'    => 'secondary',
                    'development' => 'primary',
                    'review'      => 'warning',
                    'delivered'   => 'success',
                    'maintenance' => 'info',
                ];
                $labels = [
                    'planning'    => 'Planejamento',
                    'development' => 'Desenvolvimento',
                    'review'      => 'Revisão',
                    'delivered'   => 'Entregue',
                    'maintenance' => 'Manutenção',
                ];
                ?>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?= esc($p['name']) ?></td>
                    <td>
                        <a href="/admin/clients/<?= $p['client_id'] ?>"><?= esc($p['client_name']) ?></a>
                    </td>
                    <td><?= esc($p['type']) ?></td>
                    <td>
                        <span class="badge text-bg-<?= $badges[$p['status']] ?? 'secondary' ?>">
                            <?= $labels[$p['status']] ?? $p['status'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress" style="height:6px;min-width:70px">
                            <div class="progress-bar" style="width:<?= $p['progress'] ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $p['progress'] ?>%</small>
                    </td>
                    <td><?= $p['deadline'] ? date('d/m/Y', strtotime($p['deadline'])) : '—' ?></td>
                    <td><a href="/admin/projects/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($projects)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhum projeto cadastrado.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
