<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/admin/support" class="text-muted small">&larr; Chamados</a>
    <h4 class="mb-0 mt-1"><?= esc($ticket['subject']) ?></h4>
    <span class="text-muted small">Chamado #<?= $ticket['id'] ?></span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Histórico de mensagens -->
        <div class="card mb-3">
            <div class="card-header">Mensagens</div>
            <div class="card-body" style="max-height:420px;overflow-y:auto">
                <?php foreach ($messages as $m): ?>
                <?php $isAdmin = ($m['sender_id'] !== $ticket['client_id']) ?>
                <div class="d-flex mb-3 <?= $isAdmin ? 'flex-row-reverse' : '' ?>">
                    <div class="px-3 py-2 rounded-3 <?= $isAdmin ? 'bg-primary text-white ms-5' : 'bg-light me-5' ?>"
                         style="max-width:80%">
                        <div class="small fw-bold mb-1"><?= $isAdmin ? 'Suporte' : 'Cliente' ?></div>
                        <?= nl2br(esc($m['message'])) ?>
                        <div class="small opacity-75 mt-1"><?= fmt_dt($m['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach ?>
                <?php if (empty($messages)): ?>
                <p class="text-muted text-center py-3">Nenhuma mensagem ainda.</p>
                <?php endif ?>
            </div>
        </div>

        <!-- Responder -->
        <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="card">
            <div class="card-body">
                <form method="post" action="/admin/support/<?= $ticket['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="3"
                                  placeholder="Digite sua resposta..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar resposta</button>
                </form>
            </div>
        </div>
        <?php endif ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Informações</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="text-muted small">Status</div>
                    <?php
                    $statusLabel = ['open' => 'Aberto', 'attending' => 'Em atendimento', 'resolved' => 'Resolvido', 'closed' => 'Fechado'];
                    ?>
                    <?= $statusLabel[$ticket['status']] ?? $ticket['status'] ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Prioridade</div>
                    <?php $prioLabel = ['low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente'] ?>
                    <?= $prioLabel[$ticket['priority']] ?? $ticket['priority'] ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small">Aberto em</div>
                    <?= fmt_dt($ticket['created_at']) ?>
                </li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
