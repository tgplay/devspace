<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="d-flex align-items-center mb-4 gap-3">
            <a href="/admin/team" class="text-muted"><i class="bi bi-arrow-left"></i></a>
            <h4 class="mb-0"><?= esc($title) ?></h4>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <?php
                $action  = $member ? "/admin/team/{$member['id']}/update" : '/admin/team';
                $isEdit  = $member !== null;
                $isSelf  = $isEdit && $member['id'] == session()->get('user_id');
                ?>

                <form method="post" action="<?= $action ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= esc(old('name', $member['name'] ?? '')) ?>"
                               required minlength="2" maxlength="120">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= esc(old('email', $member['email'] ?? '')) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nível de acesso</label>
                        <?php if ($isSelf): ?>
                        <input type="hidden" name="role" value="admin">
                        <input type="text" class="form-control" value="Administrador" disabled>
                        <div class="form-text">Você não pode alterar sua própria role.</div>
                        <?php else: ?>
                        <select name="role" class="form-select" required>
                            <option value="admin"  <?= (old('role', $member['role'] ?? '') === 'admin')  ? 'selected' : '' ?>>
                                Administrador — acesso total ao painel
                            </option>
                            <option value="agent"  <?= (old('role', $member['role'] ?? '') === 'agent')  ? 'selected' : '' ?>>
                                Agente de Vendas — acesso a prospectos, fila e captação
                            </option>
                        </select>
                        <?php endif ?>
                    </div>

                    <hr>

                    <?php if ($isEdit): ?>
                    <p class="text-muted small mb-3">Deixe em branco para manter a senha atual.</p>
                    <?php endif ?>

                    <div class="mb-3">
                        <label class="form-label">Senha<?= $isEdit ? '' : ' *' ?></label>
                        <input type="password" name="password" class="form-control"
                               minlength="6" autocomplete="new-password" placeholder="••••••"
                               <?= $isEdit ? '' : 'required' ?>>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Salvar alterações' : 'Criar membro' ?>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
