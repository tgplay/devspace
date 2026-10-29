<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Captação CNPJ <span class="badge text-bg-success fs-6 align-middle">gratuito</span></h4>
    <a href="/admin/prospects" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-list-ul me-1"></i> Ver prospectos
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-5">

        <!-- Arquivos da Receita -->
        <div class="card mb-3">
            <div class="card-header fw-semibold">Arquivos da Receita</div>
            <div class="card-body small">
                <?php if ($filesOk): ?>
                    <div class="text-success mb-2"><i class="bi bi-check-circle me-1"></i>
                        <?= (int) $estabCount ?> arquivo(s) de estabelecimentos + Municipios.zip encontrados.
                        <?php if ($estabCount < 10): ?><span class="text-warning">(o ideal são 10 arquivos de estabelecimentos)</span><?php endif ?>
                    </div>
                    <details>
                        <summary class="text-muted">Ver arquivos</summary>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($files as $f): ?>
                            <li><?= esc($f['name']) ?> — <?= esc(number_format($f['size'] / 1048576, 0, ',', '.')) ?> MB
                                <span class="text-muted">(<?= esc(date('d/m/Y', $f['mtime'])) ?>)</span></li>
                            <?php endforeach ?>
                        </ul>
                    </details>
                <?php else: ?>
                    <div class="text-warning fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Arquivos não encontrados.</div>
                    <ol class="ps-3 mb-0">
                        <li>Acesse <a href="https://arquivos.receitafederal.gov.br/" target="_blank" rel="noopener">arquivos.receitafederal.gov.br</a> → Dados → Cadastros → CNPJ → pasta do mês mais recente.</li>
                        <li>Baixe <code>Municipios.zip</code> e <code>Estabelecimentos0.zip</code> a <code>Estabelecimentos9.zip</code> (vários GB, sem descompactar).</li>
                        <li>Coloque na pasta <code>painel\writable\cnpj\</code> e recarregue esta página.</li>
                    </ol>
                <?php endif ?>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="card-header fw-semibold">Filtros</div>
            <div class="card-body">
                <form id="cnpj-form" method="post" action="/admin/cnpj-import/save" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Município</label>
                        <input type="text" name="municipio" class="form-control" value="<?= esc($cfg['municipio']) ?>" placeholder="SAO PAULO" required>
                        <div class="form-text">Sem acento. Vários: separe por vírgula.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Atividades (CNAE)</label>
                        <input type="text" name="cnae" class="form-control font-monospace" value="<?= esc($cfg['cnae']) ?>" placeholder="5611,1091" required>
                        <div class="form-text">Prefixos separados por vírgula. Ex.: <code>5611</code> restaurantes/lanchonetes/bares, <code>1091</code> padarias. Confira na tabela do IBGE (CONCLA).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bairros <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" name="bairro" class="form-control" value="<?= esc($cfg['bairro']) ?>" placeholder="PINHEIROS,VILA MADALENA,MOEMA">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Máx. de leads</label>
                            <input type="number" name="limit" class="form-control" min="1" max="5000" value="<?= (int) $cfg['limit'] ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Aberta há (meses)</label>
                            <input type="number" name="min_meses" class="form-control" min="0" value="<?= (int) $cfg['min_meses'] ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mês dos dados <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" name="competencia" class="form-control" value="<?= esc($cfg['competencia']) ?>" placeholder="<?= esc(date('Y-m')) ?>" pattern="\d{4}-\d{2}">
                        <div class="form-text">Só para registro na origem do contato (AAAA-MM).</div>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="so_celular" value="1" id="so_celular" <?= $cfg['so_celular'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="so_celular">Só quem tem celular (provável WhatsApp)</label>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-floppy me-1"></i> Salvar filtros
                    </button>
                </form>
            </div>
        </div>
        <p class="text-muted small mt-3">
            Descarta empresas com e-mail de domínio próprio (provável site) e duplicados (mesmo CNPJ ou telefone, inclusive de quem pediu para não ser contatado).
            Telefone/e-mail cadastrais podem ser da contabilidade. Mais detalhes em <code>scripts/cnpj/README.md</code>.
        </p>
    </div>

    <!-- Execução -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Execução em tempo real</span>
                <span class="d-flex gap-2">
                    <button id="btn-dry" class="btn btn-outline-primary btn-sm" <?= $filesOk ? '' : 'disabled' ?>>
                        <i class="bi bi-eye me-1"></i> Simular
                    </button>
                    <button id="btn-real" class="btn btn-success btn-sm" <?= $filesOk ? '' : 'disabled' ?>>
                        <i class="bi bi-play-fill me-1"></i> Importar
                    </button>
                </span>
            </div>
            <div id="running-bar" class="alert alert-warning rounded-0 border-0 border-bottom mb-0 py-2 px-3 d-none">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span id="running-text" class="small flex-grow-1"></span>
                    <button type="button" id="btn-stop" class="btn btn-danger btn-sm">
                        <i class="bi bi-stop-fill me-1"></i> Parar importação
                    </button>
                    <button type="button" id="btn-kill" class="btn btn-outline-danger btn-sm d-none"
                            title="Encerra o processo PHP no servidor. Use se a parada normal não funcionar.">
                        <i class="bi bi-x-octagon me-1"></i> Forçar parada
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="log-wrap" style="height:440px;overflow-y:auto;background:#1a1a2e;padding:1rem 1.25rem">
                    <div id="log" style="font-family:'Consolas','Courier New',monospace;font-size:.8rem;line-height:1.7;color:#c9d1d9">
                        <span style="color:#6a737d">
                            <?= $filesOk ? 'Use "Simular" para ver uma amostra sem gravar nada. A leitura dos 10 arquivos leva alguns minutos.' : 'Coloque os arquivos da Receita na pasta indicada para começar.' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div id="summary-card" class="card-footer d-none">
                <div id="summary" class="row g-3 align-items-center"></div>
            </div>
        </div>
    </div>
</div>

<script>
const form    = document.getElementById('cnpj-form');
const btnDry  = document.getElementById('btn-dry');
const btnReal = document.getElementById('btn-real');
const logDiv  = document.getElementById('log');
const logWrap = document.getElementById('log-wrap');
const sumCard = document.getElementById('summary-card');
const sumDiv  = document.getElementById('summary');

function addLine(text, color) {
    const div = document.createElement('div');
    if (color) div.style.color = color;
    div.textContent = text;
    logDiv.appendChild(div);
    logWrap.scrollTop = logWrap.scrollHeight;
    return div;
}

let progressLine = null;
let running = false;
const btnStop = document.getElementById('btn-stop');
const btnKill = document.getElementById('btn-kill');
const bar     = document.getElementById('running-bar');
const barText = document.getElementById('running-text');
let stopAskedAt = 0;

function fmtAge(sec) {
    if (sec < 60) return sec + ' s';
    return Math.floor(sec / 60) + ' min ' + (sec % 60) + ' s';
}

async function refreshState() {
    try {
        const st = await (await fetch('/admin/cnpj-import/status', { headers: { 'Accept': 'application/json' } })).json();
        if (st.running) {
            const age = st.started ? Math.max(0, Math.floor(Date.now() / 1000) - st.started) : 0;
            barText.textContent = 'Há uma ' + (st.mode === 'real' ? 'importação' : 'simulação') + ' em andamento no servidor'
                + (st.started ? ' (há ' + fmtAge(age) + ')' : '') + '.'
                + (stopAskedAt ? ' Parada solicitada, aguardando encerrar...' : '');
            bar.classList.remove('d-none');
            btnDry.disabled = btnReal.disabled = true;
            // oferece "forçar" se a parada normal foi pedida há mais de 20 s e continua rodando
            btnKill.classList.toggle('d-none', !(stopAskedAt && Date.now() - stopAskedAt > 20000));
        } else {
            bar.classList.add('d-none');
            stopAskedAt = 0;
            if (!running) btnDry.disabled = btnReal.disabled = <?= $filesOk ? 'false' : 'true' ?>;
        }
    } catch (e) { /* ignora falha momentânea */ }
}

async function stopImport(force) {
    if (force && !confirm('Encerrar o processo PHP à força? O que já foi gravado permanece, mas a execução é cortada na hora.')) return;
    const body = new URLSearchParams();
    if (force) body.set('force', '1');
    try {
        const r = await (await fetch('/admin/cnpj-import/stop', { method: 'POST', body })).json();
        addLine((r.ok ? '■ ' : '⚠ ') + r.message, r.ok ? '#f0a500' : '#f85149');
        if (!force && r.ok) stopAskedAt = Date.now();
    } catch (e) {
        addLine('⚠ Falha ao pedir a parada: ' + e.message, '#f85149');
    }
    refreshState();
}
btnStop.addEventListener('click', () => stopImport(false));
btnKill.addEventListener('click', () => stopImport(true));

function setBusy(busy, label) {
    running = busy;
    btnDry.disabled = btnReal.disabled = busy;
    if (busy) {
        (label === 'real' ? btnReal : btnDry).innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Executando...';
    } else {
        btnDry.innerHTML  = '<i class="bi bi-eye me-1"></i> Simular';
        btnReal.innerHTML = '<i class="bi bi-play-fill me-1"></i> Importar';
    }
}

function handle(msg, mode) {
    const d = msg.data || {};
    if (msg.type === 'info') {
        addLine(d.message, '#58a6ff');
        progressLine = null;
    } else if (msg.type === 'progress') {
        const txt = '… ' + Number(d.lines).toLocaleString('pt-BR') + ' linhas lidas, ' + d.imported + (mode === 'real' ? ' importados' : ' encontrados');
        if (!progressLine) progressLine = addLine(txt, '#6a737d'); else progressLine.textContent = txt;
    } else if (msg.type === 'lead') {
        addLine('   ✓ ' + d.text, '#56d364');
    } else if (msg.type === 'error') {
        addLine('⚠ ' + d.message, '#f0a500');
    } else if (msg.type === 'done') {
        const s = d.skipped, total = Object.values(s).reduce((a, b) => a + b, 0);
        addLine('────────────────────────────────────────', '#30363d');
        addLine((d.stopped ? (d.dry_run ? 'Simulação interrompida: ' : 'Importação interrompida: ') : (d.dry_run ? 'Simulação concluída: ' : 'Importação concluída: ')) + d.imported + ' lead(s).', d.stopped ? '#f0a500' : '#56d364');
        sumCard.classList.remove('d-none');
        sumDiv.innerHTML = `
            <div class="col-auto"><div class="fs-2 fw-bold text-success">${d.imported}</div>
                <div class="small text-muted">${d.dry_run ? 'seriam importados' : 'importados'}</div></div>
            <div class="col-auto"><div class="fs-2 fw-bold text-secondary">${total}</div>
                <div class="small text-muted">ignorados</div></div>
            <div class="col text-start text-muted small ms-2">
                ${s.dominio_proprio} com e-mail de domínio próprio &nbsp;·&nbsp; ${s.sem_telefone} sem telefone &nbsp;·&nbsp; ${s.duplicado} duplicados<br>
                ${s.inativa} inativas &nbsp;·&nbsp; ${s.recente} abertas há pouco &nbsp;·&nbsp; ${s.sem_fantasia} sem nome fantasia
                &nbsp;·&nbsp; ${s.so_celular} sem celular &nbsp;·&nbsp; ${s.bairro} fora dos bairros
            </div>
            ${d.dry_run ? '' : '<div class="col-auto"><a href="/admin/prospects/queue" class="btn btn-success btn-sm"><i class="bi bi-play-fill me-1"></i> Ir para a fila</a></div>'}`;
    }
}

async function run(mode) {
    if (!form.reportValidity()) return;
    if (mode === 'real' && !confirm('Importar os leads encontrados para o painel?')) return;

    setBusy(true, mode);
    stopAskedAt = 0;
    logDiv.innerHTML = '';
    sumCard.classList.add('d-none');
    progressLine = null;

    const body = new URLSearchParams(new FormData(form));
    body.set('mode', mode);

    try {
        const res = await fetch('/admin/cnpj-import/run', { method: 'POST', body });
        if (!res.ok || !res.body) throw new Error('HTTP ' + res.status);

        const reader = res.body.getReader();
        const dec = new TextDecoder();
        let buf = '';
        for (;;) {
            const { value, done } = await reader.read();
            if (done) break;
            buf += dec.decode(value, { stream: true });
            let i;
            while ((i = buf.indexOf('\n\n')) >= 0) {
                const chunk = buf.slice(0, i); buf = buf.slice(i + 2);
                if (chunk.startsWith('data: ')) {
                    try { handle(JSON.parse(chunk.slice(6)), mode); } catch (e) { /* ignora linha inválida */ }
                }
            }
        }
    } catch (err) {
        addLine('⚠ Conexão interrompida (' + err.message + '). Se era uma importação real, ela continua no servidor; recarregue a lista de prospectos para conferir.', '#f0a500');
    } finally {
        setBusy(false);
        refreshState();
    }
}

refreshState();
setInterval(refreshState, 4000);
btnDry.addEventListener('click',  () => run('dry'));
btnReal.addEventListener('click', () => run('real'));
</script>

<?= $this->endSection() ?>
