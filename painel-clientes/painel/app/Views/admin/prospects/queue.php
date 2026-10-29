<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$pending = $total - $skipped;
$waLimitReached = ($waLimit ?? 0) > 0 && ($waSent ?? 0) >= $waLimit;
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

<?php
$adminName = \App\Controllers\Admin\Prospects::senderName();
?>

<?php if (\App\Controllers\Admin\Prospects::senderNameIsWeak()): ?>
<div class="alert alert-warning py-2 small d-flex align-items-center gap-2">
    <i class="bi bi-person-badge"></i>
    <span>As mensagens saem assinadas como <strong><?= esc($adminName) ?></strong>. Para assinar com nome e sobrenome,
        ajuste seu nome em <a href="/admin/profile">Administrador (perfil)</a>.</span>
</div>
<?php endif ?>

<?php if (($waLimit ?? 0) > 0): ?>
<div class="alert alert-<?= $waLimitReached ? 'danger' : 'light border' ?> py-2 small d-flex align-items-center gap-2">
    <i class="bi bi-whatsapp"></i>
    <span>
        Abordagens por WhatsApp hoje: <strong><?= (int) $waSent ?> / <?= (int) $waLimit ?></strong>
        <?php if ($waLimitReached): ?>
        — limite diário atingido. Continue amanhã (limitar o volume protege seu número contra banimento).
        <?php endif ?>
    </span>
</div>
<?php endif ?>

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

if ($pReviews && $pRating) {
    $googleLine = "tem nota {$pRating} e {$pReviews} avaliações no Google — parabéns!";
} elseif ($pRating) {
    $googleLine = "tem nota {$pRating} no Google — parabéns!";
} elseif ($pReviews) {
    $googleLine = "tem {$pReviews} avaliações no Google — parabéns!";
} elseif (($prospect['source'] ?? '') === 'google_maps') {
    $googleLine = "tem uma ótima presença no Google!";
} else {
    // Lead de outra origem (ex.: base de CNPJ): não afirma nada sobre o Google
    $googleLine = null;
}

// Placeholder substituído pelo JS: {{SAUDACAO}}
// Sem emojis de propósito: o link wa.me (?text=...) corrompe alguns emojis
// no pré-preenchimento do WhatsApp Web — copiar/colar funciona, o link não.
$waMsg  = "Oi, {{SAUDACAO}}! Sou {$adminName}.\n\n";
if ($googleLine !== null) {
    $waMsg .= "Vi que o {$pName} {$googleLine}\n\n";
} else {
    $where  = ! empty($prospect['company']) ? " ({$prospect['company']})" : '';
    $waMsg .= "Encontrei {$pName}{$where} pesquisando negócios da região.\n\n";
}
$waMsg .= "Percebi que vocês ainda não têm site próprio. Isso pode estar fazendo vocês perderem clientes que pesquisam no Google antes de decidir onde ir.\n\n";
$waMsg .= "Posso te mostrar quantas pessoas buscam por vocês e não te encontram? Diagnóstico rápido, sem compromisso.\n\n";
$waMsg .= \App\Controllers\Admin\Prospects::OPT_OUT_FOOTER;

// Link gerado sem o placeholder — JS vai sobrescrever o href depois de montar a mensagem final
$waLink = $waPhone ? '#wa-open' : null;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Card principal -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-4">

                <?php if (! empty($liveError)): ?>
                <div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= esc($liveError) ?></div>
                <?php endif ?>

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
                            <?php if (! empty($prospect['_live'])): ?>
                            <span class="text-muted small" title="Nome, telefone e nota são buscados ao vivo e não ficam salvos no painel">Dados: Google Maps</span>
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
                                  rows="5" readonly style="font-size:.9rem; padding-right:5rem; font-family: 'Segoe UI Emoji', 'Apple Color Emoji', sans-serif;"><?= esc($waMsg) ?></textarea>
                        <button type="button" onclick="copyMsg()"
                                class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2"
                                id="copy-btn">
                            <i class="bi bi-clipboard me-1"></i><span id="copy-lbl">Copiar</span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <button type="button" onclick="generateAiMsg()" class="btn btn-sm btn-outline-primary" id="ai-btn">
                            <i class="bi bi-stars me-1"></i>Gerar com IA
                        </button>
                        <span id="ai-error" class="text-danger small d-none"></span>
                        <span id="ai-hint" class="text-muted small d-none">
                            <i class="bi bi-info-circle me-1"></i>Prefira "Copiar" — o botão "Abrir WhatsApp" pode corromper emojis
                        </span>
                    </div>
                </div>

                <!-- Ações -->
                <div class="row g-2">

                    <!-- WhatsApp -->
                    <?php if ($waPhone): ?>
                    <div class="col-12 col-sm-auto">
                        <a href="#" id="wa-btn" target="_blank" rel="noopener"
                           data-phone="55<?= esc($waPhone) ?>"
                           class="btn btn-success w-100 <?= $waLimitReached ? 'disabled' : '' ?>" style="min-width:180px"
                           <?= $waLimitReached ? 'aria-disabled="true" onclick="return false"' : '' ?>>
                            <i class="bi bi-whatsapp me-2"></i>Abrir WhatsApp
                        </a>
                    </div>
                    <!-- Ligar -->
                    <div class="col-12 col-sm-auto">
                        <a href="tel:+55<?= esc($waPhone) ?>"
                           class="btn btn-outline-success w-100">
                            <i class="bi bi-telephone me-1"></i>Ligar
                        </a>
                    </div>
                    <!-- Sem WhatsApp -->
                    <div class="col-12 col-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="no_whatsapp">
                            <button type="submit" class="btn btn-outline-warning w-100"
                                    title="Registra que o número não tem WhatsApp e avança a fila">
                                <i class="bi bi-whatsapp me-1"></i>Sem WhatsApp
                            </button>
                        </form>
                    </div>
                    <?php endif ?>

                    <!-- Contatado -->
                    <div class="col-12 col-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="contacted">
                            <button type="submit" class="btn btn-primary w-100" style="min-width:150px" <?= $waLimitReached ? 'disabled' : '' ?>>
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

                    <!-- Não contatar (opt-out) -->
                    <div class="col-12 col-sm-auto">
                        <form method="post" action="/admin/prospects/<?= $prospect['id'] ?>/queue-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="opt_out">
                            <button type="submit" class="btn btn-outline-dark w-100"
                                    title="Use quando a pessoa pedir para não receber mais mensagens"
                                    onclick="return confirm('Marcar \'' + <?= json_encode($pName) ?> + '\' como NÃO CONTATAR? Ele sai de todas as filas.')">
                                <i class="bi bi-slash-circle me-1"></i>Não contatar
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
const prospectId = <?= (int) $prospect['id'] ?>;

const hour = new Date().getHours();
const saudacao = hour < 12 ? 'bom dia' : hour < 18 ? 'boa tarde' : 'boa noite';

function updateWaLink() {
    const waBtn = document.getElementById('wa-btn');
    if (! waBtn) return;
    const phone = waBtn.dataset.phone;
    const text  = document.getElementById('wa-message').value;
    waBtn.href = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(text);
}

(function () {
    const textarea = document.getElementById('wa-message');
    textarea.value = textarea.value.replace('{{SAUDACAO}}', saudacao);
    updateWaLink();
})();

function generateAiMsg() {
    const btn      = document.getElementById('ai-btn');
    const errSpan  = document.getElementById('ai-error');
    const textarea = document.getElementById('wa-message');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Gerando...';
    errSpan.classList.add('d-none');

    fetch('/admin/prospects/' + prospectId + '/generate-message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'saudacao=' + encodeURIComponent(saudacao),
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                textarea.value = data.message;
                updateWaLink();
                document.getElementById('ai-hint').classList.remove('d-none');
            } else {
                errSpan.textContent = data.error || 'Não foi possível gerar a mensagem.';
                errSpan.classList.remove('d-none');
            }
        })
        .catch(function () {
            errSpan.textContent = 'Erro de conexão. Tente novamente.';
            errSpan.classList.remove('d-none');
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-stars me-1"></i>Gerar com IA';
        });
}

function copyMsg() {
    navigator.clipboard.writeText(document.getElementById('wa-message').value).then(function () {
        const lbl = document.getElementById('copy-lbl');
        lbl.textContent = 'Copiado!';
        setTimeout(function () { lbl.textContent = 'Copiar'; }, 2000);
    });
}

document.addEventListener('keydown', function (e) {
    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;
    const key = e.key.toLowerCase();
    if (key === 'w') {
        const waBtn = document.getElementById('wa-btn');
        if (waBtn) window.open(waBtn.href, '_blank');
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
