<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$pending = $total - $skipped;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/prospects" class="text-muted small">&larr; Prospectos</a>
        <h4 class="mb-0 mt-1">Fila de Abordagem</h4>
    </div>
    <div class="d-flex align-items-center gap-3">
        <?php if ($skipped > 0): ?>
        <form method="post" action="/admin/prospects/queue/clear">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="if(confirm('Reiniciar fila e mostrar os pulados novamente?')) this.closest('form').submit()">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reiniciar fila
            </button>
        </form>
        <?php endif ?>
        <span class="badge text-bg-primary fs-6 px-3">
            <?= $pending ?> pendente<?= $pending !== 1 ? 's' : '' ?>
            <?php if ($skipped > 0): ?>
            <span class="opacity-75 fw-normal"> · <?= $skipped ?> pulado<?= $skipped !== 1 ? 's' : '' ?></span>
            <?php endif ?>
        </span>
    </div>
</div>

<?php if (! $prospect): ?>

<div class="card">
    <div class="card-body text-center py-5">
        <?php if ($skipped > 0): ?>
        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
        <h5>Todos os leads foram processados por agora.</h5>
        <p class="text-muted mb-4">Você tem <?= $skipped ?> lead<?= $skipped !== 1 ? 's' : '' ?> pulado<?= $skipped !== 1 ? 's' : '' ?>. Quer revisitá-los?</p>
        <form method="post" action="/admin/prospects/queue/clear">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reiniciar fila com pulados
            </button>
        </form>
        <?php else: ?>
        <i class="bi bi-inbox fs-1 text-muted d-block mb-3 opacity-25"></i>
        <h5>Nenhum lead novo na fila.</h5>
        <p class="text-muted mb-4">Importe leads do Google Maps para começar a prospectar.</p>
        <a href="/admin/prospects/import" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i>Importar CSV
        </a>
        <?php endif ?>
    </div>
</div>

<?php else:
$pName    = $prospect['name'];
$pRating  = $prospect['rating']        ? number_format((float)$prospect['rating'], 1, ',', '') : null;
$pReviews = $prospect['reviews_count'] ? number_format((int)$prospect['reviews_count'])         : null;
$waPhone  = preg_replace('/\D/', '', $prospect['phone'] ?? '');

$msgParts = [];
if ($pReviews && $pRating) {
    $msgParts[] = "tem {$pReviews} avaliações no Google com {$pRating} estrelas — parabéns!";
} elseif ($pRating) {
    $msgParts[] = "tem {$pRating} estrelas no Google — parabéns!";
} elseif ($pReviews) {
    $msgParts[] = "tem {$pReviews} avaliações no Google — parabéns!";
} else {
    $msgParts[] = "tem uma ótima presença no Google!";
}

$waMsg  = "Olá! Vi que o {$pName} {$msgParts[0]} ";
$waMsg .= "Percebi que vocês ainda não têm um site. Isso pode estar fazendo vocês perderem clientes que pesquisam no Google antes de decidir onde ir. ";
$waMsg .= "Posso mostrar exatamente quantas pessoas pesquisam por vocês e não te encontram? Faço um diagnóstico rápido, sem compromisso.";

$waLink = $waPhone ? 'https://wa.me/55' . $waPhone . '?text=' . rawurlencode($waMsg) : null;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Card principal -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-4">

                <!-- Cabeçalho do lead -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h3 class="mb-1"><?= esc($pName) ?></h3>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <?php if ($pRating): ?>
                            <span class="badge text-bg-warning text-dark fs-6">
                                ⭐ <?= $pRating ?>
                            </span>
                            <?php endif ?>
                            <?php if ($pReviews): ?>
                            <span class="text-muted">
                                <i class="bi bi-chat-square-text me-1"></i><?= $pReviews ?> avaliações
                            </span>
                            <?php endif ?>
                            <?php if ($prospect['maps_url']): ?>
                            <a href="<?= esc($prospect['maps_url']) ?>" target="_blank" rel="noopener"
                               class="text-muted small text-decoration-none">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Ver no Maps
                            </a>
                            <?php endif ?>
                        </div>
                    </div>
                    <a href="/admin/prospects/<?= $prospect['id'] ?>" target="_blank"
                       class="btn btn-sm btn-outline-secondary" title="Abrir ficha completa">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>

                <!-- Telefone -->
                <?php if ($prospect['phone']): ?>
                <div class="mb-4 p-3 bg-light rounded d-flex align-items-center gap-2">
                    <i class="bi bi-telephone-fill text-success fs-5"></i>
                    <span class="fw-semibold fs-5"><?= esc($prospect['phone']) ?></span>
                </div>
                <?php endif ?>

                <!-- Mensagem -->
                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted small text-uppercase letter-spacing-1">
                        Mensagem de abordagem
                    </label>
                    <div class="position-relative">
                        <textarea id="wa-message" class="form-control"
                                  rows="5" readonly style="font-size:.9rem; padding-right:5rem;"><?= esc($waMsg) ?></textarea>
                        <button type="button" onclick="copyMsg()"
                                class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2"
                                id="copy-btn">
                            <i class="bi bi-clipboard me-1"></i><span id="copy-lbl">Copiar</span>
                        </button>
                    </div>
                </div>

                <!-- Ações -->
                <div class="row g-2">

                    <!-- WhatsApp -->
                    <?php if ($waLink): ?>
                    <div class="col-12 col-sm-auto">
                        <a href="<?= $waLink ?>" target="_blank" rel="noopener"
                           class="btn btn-success w-100" style="min-width:180px">
                            <i class="bi bi-whatsapp me-2"></i>Abrir WhatsApp
                        </a>
                    </div>
                    <?php endif ?>

                    <!-- Contatado -->
                    <div class="col-12 col-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="contacted">
                            <button type="submit" class="btn btn-primary w-100" style="min-width:150px">
                                <i class="bi bi-check-lg me-1"></i>Contatado
                            </button>
                        </form>
                    </div>

                    <!-- Pular -->
                    <div class="col-12 col-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="skip">
                            <button type="submit" class="btn btn-outline-secondary w-100" style="min-width:120px">
                                <i class="bi bi-skip-forward me-1"></i>Pular
                            </button>
                        </form>
                    </div>

                    <!-- Desqualificar -->
                    <div class="col-12 col-sm-auto ms-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="disqualify">
                            <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Marcar \'' + <?= json_encode($pName) ?> + '\' como perdido?')">
                                <i class="bi bi-x-lg me-1"></i>Desqualificar
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- Dica de atalho de teclado -->
        <div class="text-center text-muted small">
            <kbd>W</kbd> WhatsApp &nbsp;·&nbsp;
            <kbd>C</kbd> Contatado &nbsp;·&nbsp;
            <kbd>P</kbd> Pular &nbsp;·&nbsp;
            <kbd>D</kbd> Desqualificar
        </div>

    </div>
</div>

<script>
function copyMsg() {
    navigator.clipboard.writeText(document.getElementById('wa-message').value).then(function () {
        const lbl = document.getElementById('copy-lbl');
        lbl.textContent = 'Copiado!';
        setTimeout(function () { lbl.textContent = 'Copiar'; }, 2000);
    });
}

// Atalhos de teclado
document.addEventListener('keydown', function (e) {
    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;
    const key = e.key.toLowerCase();
    if (key === 'w') {
        <?php if ($waLink): ?>
        window.open(<?= json_encode($waLink) ?>, '_blank');
        <?php endif ?>
    } else if (key === 'c') {
        document.querySelector('form [value="contacted"]').closest('form').submit();
    } else if (key === 'p') {
        document.querySelector('form [value="skip"]').closest('form').submit();
    } else if (key === 'd') {
        if (confirm('Desqualificar <?= esc(addslashes($pName)) ?>?')) {
            document.querySelector('form [value="disqualify"]').closest('form').submit();
        }
    }
});
</script>

<?php endif ?>

<?= $this->endSection() ?>
