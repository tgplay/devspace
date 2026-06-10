<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isNew = $contract === null;
$statusLabel = ['draft' => 'Rascunho', 'sent' => 'Enviado', 'accepted' => 'Aceito', 'closed' => 'Encerrado'];
$statusBadge = ['draft' => 'secondary', 'sent' => 'warning', 'accepted' => 'success', 'closed' => 'dark'];

$action = $isNew ? '/admin/contracts' : "/admin/contracts/{$contract['id']}";
$title  = $isNew ? 'Novo Contrato' : esc($contract['title']);
?>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<div class="mb-4">
    <a href="/admin/contracts" class="text-muted small">&larr; Contratos</a>
    <div class="d-flex align-items-center gap-3 mt-1">
        <h4 class="mb-0"><?= $title ?></h4>
        <?php if (! $isNew): ?>
        <span class="badge text-bg-<?= $statusBadge[$contract['status']] ?? 'secondary' ?> fs-6">
            <?= $statusLabel[$contract['status']] ?? esc($contract['status']) ?>
        </span>
        <?php endif ?>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-8">
        <form method="post" action="<?= $action ?>" id="contract-form">
            <?= csrf_field() ?>

            <div class="card mb-4">
                <div class="card-header fw-semibold">Dados do contrato</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Título *</label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= esc(old('title', $contract['title'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cliente *</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($clients as $cl): ?>
                                <option value="<?= $cl['id'] ?>"
                                    <?= old('client_id', $contract['client_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                    <?= esc($cl['name']) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Projeto</label>
                            <select name="project_id" class="form-select">
                                <option value="">Nenhum</option>
                                <?php foreach ($projects as $pr): ?>
                                <option value="<?= $pr['id'] ?>"
                                    <?= old('project_id', $contract['project_id'] ?? '') == $pr['id'] ? 'selected' : '' ?>>
                                    <?= esc($pr['name']) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valor (R$)</label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0"
                                   value="<?= esc(old('value', $contract['value'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Início</label>
                            <input type="date" name="start_date" class="form-control"
                                   value="<?= esc(old('start_date', $contract['start_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Término</label>
                            <input type="date" name="end_date" class="form-control"
                                   value="<?= esc(old('end_date', $contract['end_date'] ?? '')) ?>">
                        </div>
                        <?php if (! $isNew): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statusLabel as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= ($contract['status'] ?? 'draft') === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Conteúdo do contrato</span>
                    <?php if (! empty($templates)): ?>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="template-select" class="form-select form-select-sm" style="max-width:220px">
                            <option value="">Carregar modelo...</option>
                            <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                        <button type="button" id="load-template" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Carregar
                        </button>
                    </div>
                    <?php endif ?>
                </div>
                <div class="card-body p-0">
                    <div id="quill-editor" style="min-height:450px; border:none;"></div>
                    <input type="hidden" name="content" id="content-input">
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= $isNew ? 'Criar contrato' : 'Salvar alterações' ?>
                </button>
                <a href="/admin/contracts" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <?php if (! $isNew): ?>
    <div class="col-lg-4 d-flex flex-column gap-3">

        <div class="card">
            <div class="card-header fw-semibold">Informações</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Criado em</div>
                    <?= fmt_dt($contract['created_at']) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Última atualização</div>
                    <?= fmt_dt($contract['updated_at']) ?>
                </li>
                <?php if ($contract['accepted_at']): ?>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Aceito pelo cliente em</div>
                    <strong><?= fmt_dt($contract['accepted_at']) ?></strong>
                    <?php if ($contract['accepted_ip']): ?>
                    <div class="text-muted small mt-1">IP: <?= esc($contract['accepted_ip']) ?></div>
                    <?php endif ?>
                </li>
                <?php endif ?>
            </ul>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Ações</div>
            <div class="card-body d-flex flex-column gap-2">

                <?php if ($contract['status'] === 'draft'): ?>
                <form method="post" action="/admin/contracts/<?= $contract['id'] ?>/send">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Enviar este contrato ao cliente?\nEle poderá visualizar e aceitar digitalmente.')">
                        <i class="bi bi-send me-1"></i> Enviar ao cliente
                    </button>
                </form>
                <p class="text-muted small mb-0">Após enviado, o contrato fica visível na área do cliente em Documentos.</p>
                <?php elseif ($contract['status'] === 'sent'): ?>
                <div class="alert alert-warning py-2 mb-0 small">
                    <i class="bi bi-clock me-1"></i> Aguardando aceite do cliente.
                </div>
                <?php elseif ($contract['status'] === 'accepted'): ?>
                <div class="alert alert-success py-2 mb-0 small">
                    <i class="bi bi-check-circle me-1"></i> Contrato aceito pelo cliente.
                </div>
                <?php elseif ($contract['status'] === 'closed'): ?>
                <div class="alert alert-secondary py-2 mb-0 small">
                    <i class="bi bi-archive me-1"></i> Contrato encerrado.
                </div>
                <?php endif ?>

                <form method="post" action="/admin/contracts/<?= $contract['id'] ?>/delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Excluir este contrato permanentemente?')">
                        <i class="bi bi-trash me-1"></i> Excluir contrato
                    </button>
                </form>
            </div>
        </div>

    </div>
    <?php endif ?>

</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ indent: '-1' }, { indent: '+1' }],
            ['link'],
            ['clean']
        ]
    }
});

quill.clipboard.dangerouslyPasteHTML(<?= json_encode(old('content', $contract['content'] ?? '')) ?>);

document.getElementById('contract-form').addEventListener('submit', function () {
    document.getElementById('content-input').value = quill.root.innerHTML;
});

<?php if (! empty($templates)): ?>
document.getElementById('load-template').addEventListener('click', function () {
    const id = document.getElementById('template-select').value;
    if (! id) return;

    const currentContent = quill.root.innerHTML.replace(/<p><br><\/p>/g, '').trim();
    if (currentContent && ! confirm('Substituir o conteúdo atual pelo modelo selecionado?')) return;

    fetch('/admin/contract-templates/' + id + '/content')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            quill.clipboard.dangerouslyPasteHTML(data.content || '');
        });
});
<?php endif ?>
</script>

<?= $this->endSection() ?>
