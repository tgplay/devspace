<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm auth-card">
    <div class="card-body p-4">
        <h5 class="card-title mb-4">Entrar</h5>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>

        <form method="post" action="/login">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required autofocus
                       value="<?= old('email') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>

        <hr>
        <p class="text-center mb-0 small">
            Não tem conta? <a href="/register">Cadastre-se</a>
        </p>
    </div>
</div>

<?= $this->endSection() ?>
