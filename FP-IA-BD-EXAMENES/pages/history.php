<?php $attempts = $storage->attempts(); ?>
<section class="section-title" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <span class="eyebrow">HISTORIAL</span>
        <h2>Todos los intentos</h2>
        <p>Cada intento queda guardado para medir tu evolución real.</p>
    </div>
    <?php if ($attempts): ?>
    <form method="post" action="?page=clear_history" onsubmit="return confirm('¿Seguro que quieres borrar TODOS los intentos del historial? Esta acción no se puede deshacer.');">
        <button type="submit" class="btn danger" style="background: #ef4444; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-weight: 600;">
            Vaciar historial
        </button>
    </form>
    <?php endif; ?>
</section>

<section class="panel">
<?php if (!$attempts): ?>
    <p class="muted">No hay intentos todavía.</p>
<?php else: ?>
<div class="table-wrap">
<table>
<thead>
    <tr>
        <th>#</th>
        <th>Examen</th>
        <th>Asignatura</th>
        <th>Nota</th>
        <th>Aciertos</th>
        <th>Tiempo</th>
        <th>Fecha</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
<?php foreach ($attempts as $a): ?>
<tr>
    <td><?= (int)$a['id'] ?></td>
    <td><?= e($a['exam_title']) ?></td>
    <td><span class="tag"><?= e($a['module_code'].' · '.$a['unit_code']) ?></span></td>
    <td><strong class="<?= e(score_class((float)$a['score'])) ?>"><?= number_format((float)$a['score'],1,',','') ?></strong></td>
    <td><?= (int)$a['correct_count'] ?>/<?= (int)$a['total_count'] ?></td>
    <td><?= e(format_duration((int)$a['duration_seconds'])) ?></td>
    <td><?= e(date('d/m/Y H:i', strtotime($a['finished_at']))) ?></td>
    <td style="white-space: nowrap;">
        <a href="?page=result&id=<?= (int)$a['id'] ?>" style="margin-right: 10px;">Revisar</a>
        <form method="post" action="?page=delete_attempt" style="display:inline;" onsubmit="return confirm('¿Seguro que quieres borrar el intento #<?= (int)$a['id'] ?>?');">
            <input type="hidden" name="attempt_id" value="<?= (int)$a['id'] ?>">
            <button type="submit" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                Borrar
            </button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</section>