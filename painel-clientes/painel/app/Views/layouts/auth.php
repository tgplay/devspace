<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Painel de Clientes' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body { background: #f0f2f5; }
        .auth-card { max-width: 420px; margin: 80px auto; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Painel de Clientes</h4>
        </div>
        <?= $this->renderSection('content') ?>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
