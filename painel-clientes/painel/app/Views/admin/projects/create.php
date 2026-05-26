<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/admin/projects" class="text-muted small">&larr; Projetos</a>
    <h4 class="mb-0 mt-1">Novo Projeto</h4>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach (session()->getFlashdata('errors') as $err): ?>
        <li><?= esc($err) ?></li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="post" action="/admin/projects">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Cliente <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select" required>
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= old('client_id') == $c['id'] ? 'selected' : '' ?>>
                        <?= esc($c['name']) ?> — <?= esc($c['email']) ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nome do projeto <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required
                       placeholder="Ex: Site institucional, App Android..."
                       value="<?= old('name') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="site"   <?= old('type') === 'site'   ? 'selected' : '' ?>>Site</option>
                    <option value="app"    <?= old('type') === 'app'    ? 'selected' : '' ?>>Aplicativo</option>
                    <option value="system" <?= old('type') === 'system' ? 'selected' : '' ?>>Sistema</option>
                    <option value="other"  <?= old('type') === 'other'  ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Descreva o escopo do projeto..."><?= old('description') ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Prazo de entrega</label>
                <input type="date" name="deadline" class="form-control"
                       value="<?= old('deadline') ?>">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Criar projeto</button>
                <a href="/admin/projects" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
