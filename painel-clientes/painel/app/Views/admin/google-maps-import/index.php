<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Captação Google Maps</h4>
    <a href="/admin/prospects" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-list-ul me-1"></i> Ver prospectos
    </a>
</div>

<div class="row g-4">

    <!-- Configurações -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header fw-semibold">Configurações</div>
            <div class="card-body">
                <form method="post" action="/admin/google-maps-import/save">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">API Key do Google</label>
                        <input type="text" name="api_key" class="form-control font-monospace"
                               value="<?= esc($apiKey) ?>" placeholder="AIza...">
                        <div class="form-text">
                            Google Cloud Console → Credenciais → Places API
                            <?php if (! $apiKey): ?>
                            <span class="text-warning fw-semibold">— não configurada</span>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Avaliação mínima</label>
                            <input type="number" name="min_rating" class="form-control"
                                   value="<?= esc($minRating) ?>" step="0.1" min="0" max="5">
                            <div class="form-text">Escala de 0 a 5</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Reviews mínimos</label>
                            <input type="number" name="min_reviews" class="form-control"
                                   value="<?= esc($minReviews) ?>" min="0">
                            <div class="form-text">Nº de avaliações</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Buscas <span class="text-muted fw-normal">(uma por linha)</span></label>
                        <textarea name="searches" class="form-control font-monospace"
                                  rows="14" style="font-size:.8rem;resize:vertical"><?= esc($searches) ?></textarea>
                        <div class="form-text">Ex: <code>restaurante Pinheiros São Paulo</code></div>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-floppy me-1"></i> Salvar configurações
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Execução + Log -->
    <div class="col-lg-7">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Execução em tempo real</span>
                <button id="btn-run" class="btn btn-success btn-sm" <?= $apiKey ? '' : 'disabled title="Configure a API Key primeiro"' ?>>
                    <i class="bi bi-play-fill me-1"></i> Iniciar captação
                </button>
            </div>

            <div class="card-body p-0 flex-grow-1" style="min-height:400px">
                <div id="log-wrap" style="height:440px;overflow-y:auto;background:#1a1a2e;padding:1rem 1.25rem;border-radius:0">
                    <div id="log" style="font-family:'Consolas','Courier New',monospace;font-size:.8rem;line-height:1.7;color:#c9d1d9">
                        <?php if (! $apiKey): ?>
                        <span style="color:#f0a500">⚠  Configure a API Key e salve antes de iniciar.</span>
                        <?php else: ?>
                        <span style="color:#6a737d">Clique em "Iniciar captação" para começar.</span>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <div id="summary-card" class="card-footer d-none py-3">
                <div id="summary" class="row text-center g-3 align-items-center"></div>
            </div>
        </div>
    </div>

</div>

<script>
const btn      = document.getElementById('btn-run');
const logDiv   = document.getElementById('log');
const logWrap  = document.getElementById('log-wrap');
const sumCard  = document.getElementById('summary-card');
const sumDiv   = document.getElementById('summary');

function addLine(html, color) {
    const div = document.createElement('div');
    if (color) div.style.color = color;
    div.innerHTML = html;
    logDiv.appendChild(div);
    logWrap.scrollTop = logWrap.scrollHeight;
}

btn.addEventListener('click', function () {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Executando...';
    logDiv.innerHTML = '';
    sumCard.classList.add('d-none');

    const source = new EventSource('/admin/google-maps-import/run');

    source.onmessage = function (e) {
        const msg = JSON.parse(e.data);
        const d   = msg.data;

        if (msg.type === 'search') {
            addLine('', '');
            addLine('🔍 ' + d.query, '#58a6ff');

        } else if (msg.type === 'count') {
            addLine('&nbsp;&nbsp;&nbsp;' + d.count + ' resultados brutos', '#6a737d');

        } else if (msg.type === 'imported') {
            const reviews = (d.reviews || 0).toLocaleString('pt-BR');
            addLine(
                '&nbsp;&nbsp;&nbsp;✓ <strong style="color:#56d364">' + d.name + '</strong>'
                + '&nbsp;<span style="color:#6a737d">(' + d.rating + '⭐ ' + reviews + ' avaliações)</span>'
            );

        } else if (msg.type === 'error') {
            addLine('⚠ ' + d.message, '#f0a500');

        } else if (msg.type === 'done') {
            source.close();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Iniciar captação';

            addLine('', '');
            addLine('────────────────────────────────────────', '#30363d');

            const s     = d.skipped;
            const total = Object.values(s).reduce((a, b) => a + b, 0);

            sumCard.classList.remove('d-none');
            sumDiv.innerHTML = `
                <div class="col-auto">
                    <div class="fs-2 fw-bold text-success">${d.imported}</div>
                    <div class="small text-muted">importados</div>
                </div>
                <div class="col-auto">
                    <div class="fs-2 fw-bold text-secondary">${total}</div>
                    <div class="small text-muted">ignorados</div>
                </div>
                <div class="col text-start text-muted small ms-2">
                    ${s.com_site} com site &nbsp;·&nbsp;
                    ${s.sem_telefone} sem telefone &nbsp;·&nbsp;
                    ${s.duplicado} duplicados<br>
                    ${s.rating_baixo} rating baixo &nbsp;·&nbsp;
                    ${s.poucos_reviews} poucas reviews
                </div>
                <div class="col-auto">
                    <a href="/admin/prospects/queue" class="btn btn-success btn-sm">
                        <i class="bi bi-play-fill me-1"></i> Ir para a fila
                    </a>
                </div>
            `;
        }
    };

    source.onerror = function () {
        addLine('', '');
        addLine('Conexão encerrada.', '#6a737d');
        source.close();
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Iniciar captação';
    };
});
</script>

<?= $this->endSection() ?>
