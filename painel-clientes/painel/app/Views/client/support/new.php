<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/app/support" class="text-muted small">&larr; Suporte</a>
    <h4 class="mb-0 mt-1">Novo Chamado</h4>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="post" action="/app/support">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Assunto</label>
                <input type="text" name="subject" class="form-control" required
                       placeholder="Descreva brevemente o problema">
            </div>
            <div class="mb-4">
                <label class="form-label">Mensagem</label>
                <textarea name="message" class="form-control" rows="5" required
                          placeholder="Descreva em detalhes o que está acontecendo..."></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Abrir chamado</button>
                <a href="/app/support" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
