<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isNew  = $template === null;
$action = $isNew ? '/admin/contract-templates' : "/admin/contract-templates/{$template['id']}";
$title  = $isNew ? 'Novo Modelo' : esc($template['name']);
?>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<div class="mb-4">
    <a href="/admin/contract-templates" class="text-muted small">&larr; Modelos</a>
    <h4 class="mb-0 mt-1"><?= $title ?></h4>
</div>

<form method="post" action="<?= $action ?>" id="template-form">
    <?= csrf_field() ?>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Dados do modelo</div>
        <div class="card-body">
            <label class="form-label fw-semibold">Nome do modelo *</label>
            <input type="text" name="name" class="form-control" style="max-width:400px"
                   value="<?= esc(old('name', $template['name'] ?? '')) ?>"
                   placeholder="Ex.: Contrato de Desenvolvimento de Site" required>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Conteúdo padrão</div>
        <div class="card-body p-0">
            <div id="quill-editor" style="min-height:500px; border:none;"></div>
            <input type="hidden" name="content" id="content-input">
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            <?= $isNew ? 'Criar modelo' : 'Salvar alterações' ?>
        </button>
        <a href="/admin/contract-templates" class="btn btn-outline-secondary">Cancelar</a>
        <?php if (! $isNew): ?>
        <form method="post" action="/admin/contract-templates/<?= $template['id'] ?>/delete" class="ms-auto">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger"
                    onclick="return confirm('Excluir este modelo? Os contratos criados a partir dele não serão afetados.')">
                <i class="bi bi-trash me-1"></i>Excluir modelo
            </button>
        </form>
        <?php endif ?>
    </div>
</form>

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

quill.clipboard.dangerouslyPasteHTML(<?= json_encode(old('content', $template['content'] ?? '')) ?>);

document.getElementById('template-form').addEventListener('submit', function () {
    document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>

<?= $this->endSection() ?>
