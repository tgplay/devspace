<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm auth-card">
    <div class="card-body p-4">
        <h5 class="card-title mb-4">Criar conta</h5>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form method="post" action="/register">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" required value="<?= old('name') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required value="<?= old('email') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Criar conta</button>
        </form>

        <hr>
        <p class="text-center mb-0 small">
            Já tem conta? <a href="/login">Entrar</a>
        </p>
    </div>
</div>

<?= $this->endSection() ?>
