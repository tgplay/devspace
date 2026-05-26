<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/projects" class="text-muted small">&larr; Projetos</a>
        <h4 class="mb-0 mt-1"><?= esc($project['name']) ?></h4>
        <span class="text-muted small">Cliente: <a href="/admin/clients/<?= $client['id'] ?>"><?= esc($client['name']) ?></a></span>
    </div>
</div>

<div class="row g-4">
    <!-- Editar status e progresso -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Atualizar projeto</div>
            <div class="card-body">
                <form method="post" action="/admin/projects/<?= $project['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php
                            $statuses = [
                                'planning'    => 'Planejamento',
                                'development' => 'Desenvolvimento',
                                'review'      => 'Revisão',
                                'delivered'   => 'Entregue',
                                'maintenance' => 'Manutenção',
                            ];
                            foreach ($statuses as $val => $label):
                            ?>
                            <option value="<?= $val ?>" <?= $project['status'] === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Progresso: <span id="prog-val"><?= $project['progress'] ?></span>%</label>
                        <input type="range" name="progress" class="form-range" min="0" max="100"
                               value="<?= $project['progress'] ?>"
                               oninput="document.getElementById('prog-val').textContent=this.value">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prazo</label>
                        <input type="date" name="deadline" class="form-control"
                               value="<?= $project['deadline'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link de entrega</label>
                        <input type="url" name="delivery_url" class="form-control"
                               value="<?= esc($project['delivery_url'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Informações</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="text-muted small">Tipo</div><?= esc($project['type']) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Criado em</div>
                    <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                </li>
                <?php if ($project['delivery_url']): ?>
                <li class="list-group-item">
                    <div class="text-muted small">Link de entrega</div>
                    <a href="<?= esc($project['delivery_url']) ?>" target="_blank">Acessar</a>
                </li>
                <?php endif ?>
            </ul>
        </div>
    </div>

    <!-- Etapas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Etapas do projeto</div>
            <ul class="list-group list-group-flush">
                <?php
                $taskBadges = [
                    'pending'             => ['secondary', 'Pendente'],
                    'done'                => ['success', 'Concluída'],
                    'awaiting_approval'   => ['warning', 'Aguardando aprovação'],
                    'revision_requested'  => ['danger', 'Revisão solicitada'],
                ];
                ?>
                <?php foreach ($tasks as $t): ?>
                <?php [$color, $label] = $taskBadges[$t['status']] ?? ['secondary', $t['status']] ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div><?= esc($t['title']) ?></div>
                        <?php if ($t['description']): ?>
                        <small class="text-muted"><?= esc($t['description']) ?></small>
                        <?php endif ?>
                    </div>
                    <span class="badge text-bg-<?= $color ?>"><?= $label ?></span>
                </li>
                <?php endforeach ?>
                <?php if (empty($tasks)): ?>
                <li class="list-group-item text-center text-muted py-3">Nenhuma etapa cadastrada.</li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
