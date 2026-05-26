<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página não encontrada</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            color: #dee2e6;
            letter-spacing: -4px;
        }
    </style>
</head>
<body>
    <div class="text-center px-4">
        <div class="error-code">404</div>
        <h4 class="fw-semibold mt-2 mb-2">Página não encontrada</h4>
        <p class="text-muted mb-4">
            O endereço que você tentou acessar não existe ou foi removido.
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <a href="/" class="btn btn-primary">
                <i class="bi bi-house me-1"></i> Página inicial
            </a>
        </div>
        <?php if (ENVIRONMENT !== 'production' && isset($message)): ?>
        <div class="mt-4 text-start mx-auto" style="max-width:560px">
            <details>
                <summary class="text-muted small" style="cursor:pointer">Detalhes técnicos</summary>
                <code class="d-block mt-2 p-3 bg-white border rounded small text-danger text-start">
                    <?= esc($message) ?>
                </code>
            </details>
        </div>
        <?php endif ?>
    </div>
</body>
</html>
