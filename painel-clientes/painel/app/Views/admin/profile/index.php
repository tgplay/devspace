<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <h4 class="mb-4">Meu Perfil</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="post" action="/admin/profile/update">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= esc($user['name']) ?>" required minlength="2" maxlength="120">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= esc($user['email']) ?>" required>
                    </div>

                    <hr>

                    <p class="text-muted small mb-3">Deixe em branco para manter a senha atual.</p>

                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="password" class="form-control"
                               minlength="6" autocomplete="new-password" placeholder="••••••">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <span class="badge text-bg-secondary"><?= $user['role'] === 'admin' ? 'Administrador' : 'Agente de Vendas' ?></span>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Salvar alterações
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
