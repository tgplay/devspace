<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/app/projects" class="text-muted small">&larr; Meus Projetos</a>
    <h4 class="mb-0 mt-1"><?= esc($project['name']) ?></h4>
</div>

<div class="row g-4">
    <!-- Info do projeto -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Status do projeto</div>
            <div class="card-body">
                <?php
                $badges = [
                    'planning'    => ['secondary', 'Planejamento'],
                    'development' => ['primary',   'Desenvolvimento'],
                    'review'      => ['warning',   'Revisão'],
                    'delivered'   => ['success',   'Entregue'],
                    'maintenance' => ['info',      'Manutenção'],
                ];
                [$color, $label] = $badges[$project['status']] ?? ['secondary', $project['status']];
                ?>
                <div class="mb-3">
                    <span class="badge text-bg-<?= $color ?> fs-6"><?= $label ?></span>
                </div>
                <div class="mb-1 small d-flex justify-content-between">
                    <span>Progresso geral</span><span><?= $project['progress'] ?>%</span>
                </div>
                <div class="progress mb-3" style="height:10px">
                    <div class="progress-bar" style="width:<?= $project['progress'] ?>%"></div>
                </div>
                <?php if ($project['deadline']): ?>
                <div class="small text-muted">
                    <i class="bi bi-calendar me-1"></i> Prazo: <?= fmt_dt($project['deadline'], 'd/m/Y') ?>
                </div>
                <?php endif ?>
                <?php if ($project['delivery_url']): ?>
                <a href="<?= esc($project['delivery_url']) ?>" target="_blank"
                   class="btn btn-success w-100 mt-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Acessar entrega
                </a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- Etapas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Etapas & Entregas</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($tasks as $t): ?>
                <?php
                $taskInfo = [
                    'pending'            => ['secondary', 'Pendente',              false],
                    'done'               => ['success',   'Concluída',             false],
                    'awaiting_approval'  => ['warning',   'Aguardando aprovação',  true],
                    'revision_requested' => ['danger',    'Revisão solicitada',    false],
                ];
                [$tColor, $tLabel, $canApprove] = $taskInfo[$t['status']] ?? ['secondary', $t['status'], false];
                ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium"><?= esc($t['title']) ?></div>
                            <?php if ($t['description']): ?>
                            <small class="text-muted"><?= esc($t['description']) ?></small>
                            <?php endif ?>
                            <?php if ($t['status'] === 'done' && $t['approved_at']): ?>
                            <small class="text-success d-block">
                                <i class="bi bi-check-circle me-1"></i>
                                Aprovada em <?= fmt_dt($t['approved_at'], 'd/m/Y') ?>
                            </small>
                            <?php endif ?>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge text-bg-<?= $tColor ?>"><?= $tLabel ?></span>
                            <?php if ($canApprove): ?>
                            <form method="post" action="/app/tasks/<?= $t['id'] ?>/approve" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                            </form>
                            <form method="post" action="/app/tasks/<?= $t['id'] ?>/request-revision" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Revisar</button>
                            </form>
                            <?php endif ?>
                        </div>
                    </div>
                </li>
                <?php endforeach ?>
                <?php if (empty($tasks)): ?>
                <li class="list-group-item text-center text-muted py-3">Nenhuma etapa cadastrada ainda.</li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
