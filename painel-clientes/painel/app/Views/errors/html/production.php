<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Algo deu errado</title>
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
        .error-icon {
            font-size: 5rem;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="text-center px-4">
        <i class="bi bi-exclamation-circle error-icon"></i>
        <h4 class="fw-semibold mt-3 mb-2">Algo deu errado</h4>
        <p class="text-muted mb-4" style="max-width:400px;margin:0 auto">
            Ocorreu um erro inesperado. Nossa equipe já foi notificada.<br>
            Tente novamente em alguns instantes.
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
