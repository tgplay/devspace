<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Documentos</h4>

<?php if (empty($documents)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-file-earmark fs-1 d-block mb-2"></i>
        Nenhum documento disponível ainda.
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>Tipo</th><th>Data</th><th></th></tr>
            </thead>
            <tbody>
                <?php
                $typeIcon  = ['contract' => 'bi-file-earmark-text', 'briefing' => 'bi-file-earmark-richtext', 'delivery' => 'bi-file-earmark-zip', 'other' => 'bi-file-earmark'];
                $typeLabel = ['contract' => 'Contrato', 'briefing' => 'Briefing', 'delivery' => 'Entrega', 'other' => 'Outro'];
                ?>
                <?php foreach ($documents as $d): ?>
                <tr>
                    <td>
                        <i class="bi <?= $typeIcon[$d['type']] ?? 'bi-file-earmark' ?> me-2 text-muted"></i>
                        <?= esc($d['name']) ?>
                    </td>
                    <td><?= $typeLabel[$d['type']] ?? $d['type'] ?></td>
                    <td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                    <td>
                        <a href="<?= esc($d['file_path']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Baixar
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>
