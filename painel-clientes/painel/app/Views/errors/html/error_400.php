<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Requisição inválida</title>
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
        <div class="error-code">400</div>
        <h4 class="fw-semibold mt-2 mb-2">Requisição inválida</h4>
        <p class="text-muted mb-4">
            <?php if (ENVIRONMENT !== 'production'): ?>
                <?= nl2br(esc($message)) ?>
            <?php else: ?>
                Não foi possível processar esta solicitação.
            <?php endif ?>
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <a href="/" class="btn btn-primary">
                <i class="bi bi-house me-1"></i> Página inicial
            </a>
        </div>
    </div>
</body>
</html>
