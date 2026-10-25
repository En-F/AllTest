<?php
$attemptId = (int)($_GET['id'] ?? 0);
$a = $storage->attemptDetail($attemptId);
if (!$a) {
    echo '<div class="alert danger">Resultado no encontrado.</div>';
    return;
}
$passed = (float)$a['score'] >= 5;
?>
<section class="result-hero <?= $passed ? 'pass' : 'fail' ?>">
    <div>
        <span class="tag"><?= e($a['module_code'].' · '.$a['unit_code']) ?></span>
        <h2><?= e($a['exam_title']) ?></h2>
        <p>Resultado guardado en el historial.</p>
    </div>
    <div class="score-orb">
        <strong><?= number_format((float)$a['score'],1,',','') ?></strong>
        <span>/ 10</span>
    </div>
</section>

<section class="metric-grid compact">
    <article class="metric"><span>Estado</span><strong><?= $passed ? 'APROBADO' : 'SUSPENSO' ?></strong><small><?= $passed ? '✅' : '❌' ?></small></article>
    <article class="metric"><span>Aciertos</span><strong><?= (int)$a['correct_count'] ?>/<?= (int)$a['total_count'] ?></strong><small><?= round(((int)$a['correct_count']/max(1,(int)$a['total_count']))*100) ?> %</small></article>
    <article class="metric"><span>Errores</span><strong><?= (int)$a['total_count']-(int)$a['correct_count'] ?></strong><small>Preguntas a repasar</small></article>
    <article class="metric"><span>Tiempo</span><strong><?= e(format_duration((int)$a['duration_seconds'])) ?></strong><small>Duración</small></article>
</section>

<div class="action-row">
    <a class="btn primary" href="?page=exam&id=<?= (int)$a['exam_id'] ?>">Repetir examen</a>
    <?php if ((int)$a['correct_count'] < (int)$a['total_count']): ?>
        <a class="btn secondary" href="?page=exam&id=<?= (int)$a['exam_id'] ?>&missed_from=<?= (int)$a['id'] ?>">Solo preguntas falladas</a>
    <?php endif; ?>
    <a class="btn ghost" href="?page=dashboard">Dashboard</a>
</div>

<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">CORRECCIÓN</span><h3>Revisión pregunta por pregunta</h3></div></div>
    <div class="review-list">
        <?php foreach ($a['review'] as $idx=>$r):
            $ok = (int)$r['is_correct'] === 1;
            $letter = $r['selected_answer'] ?: '—';
            $optKey = strtolower($r['correct_answer']);
            $correctText = $r['option_'.$optKey] ?? '';
        ?>
        <details class="review-item <?= $ok ? 'ok' : 'bad' ?>">
            <summary>
                <span><?= $ok ? '✅' : '❌' ?> Pregunta <?= $idx+1 ?></span>
                <span>Tu respuesta: <?= e($letter) ?></span>
            </summary>
            <div class="review-body">
                <p><strong><?= e($r['text']) ?></strong></p>
                <?php if (!$ok): ?>
                    <p class="answer-line"><span>Correcta:</span> <strong><?= e($r['correct_answer'].'. '.$correctText) ?></strong></p>
                <?php endif; ?>
                <?php if (trim((string)$r['explanation']) !== ''): ?>
                    <p class="explanation"><?= e($r['explanation']) ?></p>
                <?php endif; ?>
            </div>
        </details>
        <?php endforeach; ?>
    </div>
</section>
