<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="/admin/prospects" class="text-muted small">&larr; Prospectos</a>
    <h4 class="mb-0 mt-1">Importar leads do Google Maps</h4>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-semibold">Upload do CSV</div>
            <div class="card-body">
                <form method="post" action="/admin/prospects/import" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Arquivo CSV (exportado do Outscraper)</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text">Colunas esperadas: <code>name, phone, email, site, rating, reviews, place_link</code></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Filtros de qualificação</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Avaliação mínima (estrelas)</label>
                                <input type="number" name="min_rating" class="form-control"
                                       value="4.0" step="0.1" min="0" max="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Mínimo de avaliações</label>
                                <input type="number" name="min_reviews" class="form-control"
                                       value="50" min="0">
                            </div>
                        </div>
                        <div class="form-text mt-2">
                            Leads <strong>com site preenchido</strong> são sempre ignorados, independente dos filtros.
                            Duplicatas (mesmo e-mail já cadastrado) também são puladas.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Importar leads qualificados
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-1"></i> Como exportar do Outscraper</h6>
                <ol class="small mb-3 ps-3">
                    <li class="mb-2">Acesse <strong>outscraper.com → Services → Google Maps Scraper</strong></li>
                    <li class="mb-2">Configure: categoria (ex.: <em>restaurante Pinheiros São Paulo</em>), limite 500</li>
                    <li class="mb-2">Ative <strong>Email &amp; Contacts Enricher</strong> para capturar e-mails</li>
                    <li class="mb-2">Execute e aguarde o resultado</li>
                    <li>Clique em <strong>Export → CSV</strong> e faça upload aqui</li>
                </ol>
                <hr class="my-3">
                <h6 class="fw-semibold mb-2">O que é importado</h6>
                <ul class="small mb-0 ps-3">
                    <li>Leads <strong>sem site</strong> cadastrado no Google</li>
                    <li>Avaliação <strong>≥ 4.0 ⭐</strong> e mínimo de reviews configurado</li>
                    <li>Status inicial: <span class="badge text-bg-primary">Novo</span></li>
                    <li>Origem marcada como <span class="badge text-bg-light border text-dark"><i class="bi bi-geo-alt me-1"></i>Google Maps</span></li>
                    <li>Interesse padrão: <strong>Site</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
