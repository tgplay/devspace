<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/app/support" class="text-muted small">&larr; Suporte</a>
    <h4 class="mb-0 mt-1"><?= esc($ticket['subject']) ?></h4>
    <?php
    $statusBadge = ['open' => 'danger', 'attending' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
    $statusLabel = ['open' => 'Aberto', 'attending' => 'Em atendimento', 'resolved' => 'Resolvido', 'closed' => 'Fechado'];
    ?>
    <span class="badge text-bg-<?= $statusBadge[$ticket['status']] ?? 'secondary' ?>">
        <?= $statusLabel[$ticket['status']] ?? $ticket['status'] ?>
    </span>
</div>

<!-- Histórico de mensagens -->
<div class="card mb-3" style="max-width:720px">
    <div class="card-header">Conversa</div>
    <div class="card-body" style="max-height:420px;overflow-y:auto">
        <?php foreach ($messages as $m): ?>
        <?php $isClient = ($m['sender_id'] === session()->get('user_id')) ?>
        <div class="d-flex mb-3 <?= $isClient ? 'flex-row-reverse' : '' ?>">
            <div class="px-3 py-2 rounded-3 <?= $isClient ? 'bg-primary text-white ms-5' : 'bg-light me-5' ?>"
                 style="max-width:80%">
                <div class="small fw-bold mb-1"><?= $isClient ? 'Você' : 'Suporte' ?></div>
                <?= nl2br(esc($m['message'])) ?>
                <div class="small opacity-75 mt-1"><?= fmt_dt($m['created_at']) ?></div>
            </div>
        </div>
        <?php endforeach ?>
        <?php if (empty($messages)): ?>
        <p class="text-muted text-center py-3">Nenhuma mensagem.</p>
        <?php endif ?>
    </div>
</div>

<!-- Responder -->
<?php if ($ticket['status'] !== 'closed' && $ticket['status'] !== 'resolved'): ?>
<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="post" action="/app/support/<?= $ticket['id'] ?>/reply">
            <?= csrf_field() ?>
            <div class="mb-3">
                <textarea name="message" class="form-control" rows="3"
                          placeholder="Digite sua mensagem..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>
