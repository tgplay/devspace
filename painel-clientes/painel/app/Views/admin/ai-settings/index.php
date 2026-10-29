<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Configurações de IA</h4>
    <a href="/admin/prospects/queue" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-play-circle me-1"></i> Fila de abordagem
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold">Groq</div>
            <div class="card-body">
                <form method="post" action="/admin/ai-settings/save">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">API Key do Groq</label>
                        <input type="password" name="api_key" class="form-control font-monospace" autocomplete="new-password"
                               value="" <?= $fromEnv ? 'disabled' : '' ?>
                               placeholder="<?= $apiKeyHint ? esc($apiKeyHint) . ' (deixe vazio para manter)' : 'gsk_...' ?>">
                        <?php if ($fromEnv): ?>
                        <div class="form-text text-success">Chave definida pela variável de ambiente GROQ_API_KEY.</div>
                        <?php endif ?>
                        <div class="form-text">
                            Gere uma chave gratuita em <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com/keys</a> — conta pessoal, sem custo, sem cartão.
                            <?php if (! $apiKey): ?>
                            <span class="text-warning fw-semibold">— não configurada</span>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Limite diário de abordagens por WhatsApp</label>
                        <input type="number" name="whatsapp_daily_limit" class="form-control" min="0" max="200"
                               value="<?= (int) $waLimit ?>">
                        <div class="form-text">
                            Máximo de leads marcados como "Contatado" por dia na fila de abordagem. Volume alto de mensagens
                            para quem não conhece você aumenta o risco de banimento do número. Use 0 para desativar o limite.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-floppy me-1"></i> Salvar configurações
                    </button>
                </form>
            </div>
        </div>
        <p class="text-muted small mt-3">
            Usada pelo botão "Gerar com IA" na <a href="/admin/prospects/queue">fila de abordagem</a> para personalizar a mensagem de WhatsApp de cada prospecto.
        </p>
    </div>
</div>

<?= $this->endSection() ?>
