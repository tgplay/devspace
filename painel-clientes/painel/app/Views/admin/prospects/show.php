<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isNew = $prospect === null;

$statusLabel = [
    'new'           => 'Novo',
    'contacted'     => 'Contatado',
    'qualified'     => 'Qualificado',
    'proposal_sent' => 'Proposta enviada',
    'won'           => 'Ganho',
    'lost'          => 'Perdido',
];
$statusBadge = [
    'new'           => 'primary',
    'contacted'     => 'info',
    'qualified'     => 'warning',
    'proposal_sent' => 'secondary',
    'won'           => 'success',
    'lost'          => 'danger',
];
$interestOpts = ['site' => 'Site', 'app' => 'Aplicativo', 'system' => 'Sistema', 'other' => 'Outro'];
$sourceOpts   = ['website' => 'Site', 'referral' => 'Indicação', 'social' => 'Redes sociais', 'email' => 'E-mail', 'other' => 'Outro', 'google_maps' => 'Google Maps'];

$action = $isNew ? '/admin/prospects' : "/admin/prospects/{$prospect['id']}";
$title  = $isNew ? 'Novo Prospecto' : esc($prospect['name']);
?>

<div class="mb-4">
    <a href="/admin/prospects" class="text-muted small">&larr; Prospectos</a>
    <div class="d-flex align-items-center gap-3 mt-1">
        <h4 class="mb-0"><?= $title ?></h4>
        <?php if (! $isNew): ?>
        <span class="badge text-bg-<?= $statusBadge[$prospect['status']] ?? 'secondary' ?> fs-6">
            <?= $statusLabel[$prospect['status']] ?? esc($prospect['status']) ?>
        </span>
        <?php endif ?>
    </div>
</div>

<div class="row g-4">

    <!-- Formulário principal -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">Dados do prospecto</div>
            <div class="card-body">
                <form method="post" action="<?= $action ?>">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nome *</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= esc(old('name', $prospect['name'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">E-mail</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= esc(old('email', $prospect['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telefone / WhatsApp</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= esc(old('phone', $prospect['phone'] ?? '')) ?>"
                                   placeholder="(11) 99999-9999">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Empresa</label>
                            <input type="text" name="company" class="form-control"
                                   value="<?= esc(old('company', $prospect['company'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Interesse</label>
                            <select name="interest" class="form-select">
                                <?php foreach ($interestOpts as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= old('interest', $prospect['interest'] ?? 'other') === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Origem</label>
                            <select name="source" class="form-select">
                                <?php foreach ($sourceOpts as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= old('source', $prospect['source'] ?? 'other') === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php if (! $isNew): ?>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statusLabel as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= $prospect['status'] === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php endif ?>

                        <!-- Dados do Google Maps -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Avaliação Google ⭐</label>
                            <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5"
                                   placeholder="ex: 4.5"
                                   value="<?= esc(old('rating', $prospect['rating'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nº de avaliações</label>
                            <input type="number" name="reviews_count" class="form-control" min="0"
                                   placeholder="ex: 128"
                                   value="<?= esc(old('reviews_count', $prospect['reviews_count'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Link Google Maps</label>
                            <input type="url" name="maps_url" class="form-control"
                                   placeholder="https://maps.google.com/..."
                                   value="<?= esc(old('maps_url', $prospect['maps_url'] ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas internas</label>
                            <textarea name="notes" class="form-control" rows="5"
                                      placeholder="Anotações sobre o prospecto, histórico de contatos..."><?= esc(old('notes', $prospect['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $isNew ? 'Criar prospecto' : 'Salvar alterações' ?>
                        </button>
                        <a href="/admin/prospects" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Painel lateral -->
    <?php if (! $isNew): ?>
    <div class="col-lg-4 d-flex flex-column gap-3">

        <!-- Informações rápidas -->
        <div class="card">
            <div class="card-header fw-semibold">Informações</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Entrada</div>
                    <?= fmt_dt($prospect['created_at']) ?>
                </li>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Última atualização</div>
                    <?= fmt_dt($prospect['updated_at']) ?>
                </li>
                <?php if ($prospect['phone']): ?>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Telefone</div>
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $prospect['phone']) ?>"
                       target="_blank" rel="noopener" class="text-decoration-none">
                        <i class="bi bi-whatsapp text-success me-1"></i><?= esc($prospect['phone']) ?>
                    </a>
                </li>
                <?php endif ?>
                <?php if ($prospect['email']): ?>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">E-mail</div>
                    <a href="mailto:<?= esc($prospect['email']) ?>"><?= esc($prospect['email']) ?></a>
                </li>
                <?php endif ?>
                <?php if ($prospect['maps_url']): ?>
                <li class="list-group-item">
                    <div class="text-muted small mb-1">Google Maps</div>
                    <a href="<?= esc($prospect['maps_url']) ?>" target="_blank" rel="noopener"
                       class="text-decoration-none small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>Ver perfil
                    </a>
                    <?php if ($prospect['rating'] || $prospect['reviews_count']): ?>
                    <div class="mt-1">
                        <?php if ($prospect['rating']): ?>
                        <span class="badge text-bg-warning text-dark me-1">⭐ <?= number_format((float)$prospect['rating'], 1) ?></span>
                        <?php endif ?>
                        <?php if ($prospect['reviews_count']): ?>
                        <span class="text-muted small"><?= number_format((int)$prospect['reviews_count']) ?> avaliações</span>
                        <?php endif ?>
                    </div>
                    <?php endif ?>
                </li>
                <?php endif ?>
            </ul>
        </div>

        <!-- Gerador de mensagem WhatsApp -->
        <?php if ($prospect['phone']): ?>
        <?php
        $pName    = $prospect['name'];
        $pRating  = $prospect['rating']        ? number_format((float)$prospect['rating'], 1, ',', '') : '??';
        $pReviews = $prospect['reviews_count'] ? number_format((int)$prospect['reviews_count']) : '??';
        $waMsg    = "Olá! Vi que o {$pName} tem {$pReviews} avaliações no Google com {$pRating} estrelas — parabéns! Percebi que vocês ainda não têm um site. Isso pode estar fazendo vocês perderem clientes que pesquisam no Google antes de decidir onde ir. Posso mostrar exatamente quantas pessoas pesquisam por vocês e não te encontram? Faço um diagnóstico rápido, sem compromisso.";
        $waPhone  = preg_replace('/\D/', '', $prospect['phone']);
        $waLink   = 'https://wa.me/55' . $waPhone . '?text=' . rawurlencode($waMsg);
        ?>
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-whatsapp text-success me-1"></i> Mensagem de abordagem
            </div>
            <div class="card-body">
                <textarea id="wa-message" class="form-control form-control-sm mb-2" rows="6"
                          readonly style="font-size:.82rem; resize:none;"><?= esc($waMsg) ?></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyWaMessage()">
                        <i class="bi bi-clipboard me-1"></i><span id="copy-label">Copiar</span>
                    </button>
                    <a href="<?= $waLink ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                        <i class="bi bi-whatsapp me-1"></i>Abrir WhatsApp
                    </a>
                </div>
            </div>
        </div>
        <script>
        function copyWaMessage() {
            const text = document.getElementById('wa-message').value;
            navigator.clipboard.writeText(text).then(function() {
                const lbl = document.getElementById('copy-label');
                lbl.textContent = 'Copiado!';
                setTimeout(function() { lbl.textContent = 'Copiar'; }, 2000);
            });
        }
        </script>
        <?php endif ?>

        <!-- Ações -->
        <div class="card">
            <div class="card-header fw-semibold">Ações</div>
            <div class="card-body d-flex flex-column gap-2">

                <?php if ($prospect['status'] !== 'won' && $prospect['status'] !== 'lost'): ?>
                <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/convert">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Converter este prospecto em cliente?\nUm acesso será criado com senha temporária.')">
                        <i class="bi bi-person-check me-1"></i> Converter em cliente
                    </button>
                </form>
                <?php elseif ($prospect['status'] === 'won'): ?>
                <div class="alert alert-success py-2 mb-0 small">
                    <i class="bi bi-check-circle me-1"></i> Prospecto convertido em cliente.
                </div>
                <?php endif ?>

                <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Remover este prospecto permanentemente?')">
                        <i class="bi bi-trash me-1"></i> Excluir prospecto
                    </button>
                </form>
            </div>
        </div>

    </div>
    <?php endif ?>

</div>

<?= $this->endSection() ?>
