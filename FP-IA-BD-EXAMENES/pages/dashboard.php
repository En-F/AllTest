<?php
$stats = $storage->stats();
$attempts = array_slice($storage->attempts(), 0, 6);
$weak = $storage->weakQuestions(6);
?>
<?php
// ---------- Datos para anillos, tendencia y frecuencia ----------
$all = array_reverse($storage->attempts());          // cronológico (antiguo → reciente)
$totalAttempts = count($all);
$series = array_slice($all, -20);                    // últimos 20 intentos
$m = count($series);

// Regresión lineal simple (línea de tendencia)
$slope = 0.0; $intercept = 0.0;
if ($m >= 2) {
    $ys = array_map(fn($a) => (float)$a['score'], $series);
    $mx = ($m - 1) / 2; $my = array_sum($ys) / $m; $num = 0.0; $den = 0.0;
    foreach ($ys as $i => $y) { $num += ($i - $mx) * ($y - $my); $den += ($i - $mx) ** 2; }
    $slope = $den > 0 ? $num / $den : 0.0;
    $intercept = $my - $slope * $mx;
}

// Anillos de avance
$distinctDone = count(array_unique(array_map(fn($a) => (int)$a['exam_id'], $all)));
$coveragePct  = count($exams) ? $distinctDone / count($exams) * 100 : 0;
$avgPct       = (float)$stats['avg'] * 10;
$passPct      = $stats['count'] ? $stats['passes'] / $stats['count'] * 100 : 0;

$ring = function (float $pct, string $value, string $label, string $rgb): string {
    $pct = max(0, min(100, $pct)); $r = 52; $c = 2 * M_PI * $r;
    $off = round($c * (1 - $pct / 100), 2); $c = round($c, 2);
    return '<div class="ring" style="--c:' . $rgb . '"><div class="ring-box">'
        . '<svg viewBox="0 0 130 130" aria-hidden="true"><circle class="ring-bg" cx="65" cy="65" r="52"/>'
        . '<circle class="ring-fg" cx="65" cy="65" r="52" stroke-dasharray="' . $c . '" stroke-dashoffset="' . $off . '" style="--full:' . $c . '"/></svg>'
        . '<div class="ring-text"><strong>' . $value . '</strong></div></div>'
        . '<span class="ring-label">' . $label . '</span></div>';
};

// Gráfica de tendencia (SVG)
$W = 640; $H = 230; $pl = 36; $pr = 16; $pt = 16; $pb = 28;
$iw = $W - $pl - $pr; $ih = $H - $pt - $pb;
$px = fn(int $i): float => $m > 1 ? $pl + $i * $iw / ($m - 1) : $pl + $iw / 2;
$py = fn(float $v): float => $pt + $ih * (1 - max(0, min(10, $v)) / 10);
$linePts = []; foreach ($series as $i => $a) $linePts[] = round($px($i), 1) . ',' . round($py((float)$a['score']), 1);

// Frecuencia: intentos por día (últimos 14 días)
$days = [];
for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = 0;
foreach ($all as $a) { $d = date('Y-m-d', strtotime((string)$a['finished_at'])); if (isset($days[$d])) $days[$d]++; }
$maxDay = max(1, max($days)); $days14 = array_sum($days);
$bW = 640; $bH = 150; $bpl = 12; $bpr = 12; $bpt = 20; $bpb = 24;
$biw = $bW - $bpl - $bpr; $bih = $bH - $bpt - $bpb; $slot = $biw / 14; $barW = $slot * 0.55;

// Veces que se ha hecho cada examen
$perExam = [];
foreach ($all as $a) { $k = $a['module_code'] . ' · ' . $a['unit_code'] . ' · ' . $a['exam_title']; $perExam[$k] = ($perExam[$k] ?? 0) + 1; }
arsort($perExam); $perExam = array_slice($perExam, 0, 5, true);
$maxPer = $perExam ? max($perExam) : 1;

$trendTxt = $m < 2 ? 'Necesitas 2+ intentos' : (abs($slope) < 0.05 ? '→ Estable' : ($slope > 0 ? '↗ Mejorando' : '↘ Bajando'));
$trendCls = $m < 2 ? '' : (abs($slope) < 0.05 ? 'flat' : ($slope > 0 ? 'up' : 'down'));
?>
<section class="hero">
    <div>
        <span class="eyebrow">PANEL DE ESTUDIO</span>
        <h2>Tu progreso de exámenes</h2>
        <p>Revisa resultados, detecta puntos débiles y vuelve a practicar por asignatura y unidad.</p>
    </div>
</section>

<section class="metric-grid">
    <article class="metric"><span>Exámenes realizados</span><strong><?= (int)$stats['count'] ?></strong><small>Intentos guardados</small></article>
    <article class="metric"><span>Nota media</span><strong><?= number_format((float)$stats['avg'],1,',','') ?></strong><small>Sobre 10</small></article>
    <article class="metric"><span>Mejor resultado</span><strong><?= number_format((float)$stats['best'],1,',','') ?></strong><small>Máxima nota</small></article>
    <article class="metric"><span>Aprobados</span><strong><?= (int)$stats['passes'] ?></strong><small><?= $stats['count'] ? round($stats['passes']/$stats['count']*100) : 0 ?> % de intentos</small></article>
</section>

<section class="progress-grid">
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">AVANCE</span><h3>Progreso global</h3></div></div>
        <div class="rings">
            <?= $ring($avgPct, number_format((float)$stats['avg'],1,',',''), 'Nota media', '91,140,255') ?>
            <?= $ring($passPct, round($passPct) . '%', 'Aprobados', '61,220,151') ?>
            <?= $ring($coveragePct, $distinctDone . '/' . count($exams), 'Exámenes probados', '168,120,255') ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div><span class="eyebrow">TENDENCIA</span><h3>Evolución de tus notas</h3></div>
            <span class="trend-badge <?= $trendCls ?>"><?= e($trendTxt) ?></span>
        </div>
        <?php if ($m === 0): ?>
            <p class="muted">Haz tu primer examen para ver la tendencia.</p>
        <?php else: ?>
        <div class="chart-wrap">
        <svg viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Gráfica de evolución de notas">
            <defs>
                <linearGradient id="areaG" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5b8cff" stop-opacity=".35"/><stop offset="1" stop-color="#5b8cff" stop-opacity="0"/></linearGradient>
                <linearGradient id="lineG" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#5b8cff"/><stop offset="1" stop-color="#a878ff"/></linearGradient>
            </defs>
            <?php foreach ([0,5,10] as $g): ?>
                <line class="grid <?= $g===5 ? 'pass-line' : '' ?>" x1="<?= $pl ?>" x2="<?= $W-$pr ?>" y1="<?= round($py((float)$g),1) ?>" y2="<?= round($py((float)$g),1) ?>"/>
                <text class="axis" x="<?= $pl-8 ?>" y="<?= round($py((float)$g)+4,1) ?>" text-anchor="end"><?= $g ?></text>
            <?php endforeach; ?>
            <?php if ($m > 1): ?>
                <polygon fill="url(#areaG)" points="<?= implode(' ', $linePts) ?> <?= round($px($m-1),1) ?>,<?= round($py(0),1) ?> <?= round($px(0),1) ?>,<?= round($py(0),1) ?>"/>
                <polyline class="score-line" fill="none" stroke="url(#lineG)" points="<?= implode(' ', $linePts) ?>"/>
                <line class="trend-line" x1="<?= round($px(0),1) ?>" y1="<?= round($py($intercept),1) ?>" x2="<?= round($px($m-1),1) ?>" y2="<?= round($py($intercept + $slope*($m-1)),1) ?>"/>
            <?php endif; ?>
            <?php foreach ($series as $i => $a): ?>
                <circle class="pt" cx="<?= round($px($i),1) ?>" cy="<?= round($py((float)$a['score']),1) ?>" r="4.5">
                    <title><?= e($a['exam_title'] . ' · ' . number_format((float)$a['score'],1,',','') . ' · ' . date('d/m/Y', strtotime((string)$a['finished_at']))) ?></title>
                </circle>
            <?php endforeach; ?>
            <text class="axis" x="<?= $pl ?>" y="<?= $H-8 ?>">#<?= $totalAttempts - $m + 1 ?></text>
            <text class="axis" x="<?= $W-$pr ?>" y="<?= $H-8 ?>" text-anchor="end">#<?= $totalAttempts ?></text>
        </svg>
        </div>
        <div class="chart-legend"><span><i class="l-score"></i>Nota por intento</span><span><i class="l-trend"></i>Línea de tendencia</span><span><i class="l-pass"></i>Aprobado (5)</span></div>
        <?php endif; ?>
    </section>
</section>

<section class="panel">
    <div class="panel-head">
        <div><span class="eyebrow">CONSTANCIA</span><h3>Veces que has hecho exámenes</h3></div>
        <span class="trend-badge"><?= (int)$days14 ?> en 14 días · <?= (int)$totalAttempts ?> en total</span>
    </div>
    <div class="freq-grid">
        <div class="chart-wrap">
        <svg viewBox="0 0 <?= $bW ?> <?= $bH ?>" role="img" aria-label="Intentos por día">
            <defs><linearGradient id="barG" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7aa2ff"/><stop offset="1" stop-color="#8a63ff"/></linearGradient></defs>
            <line class="grid" x1="<?= $bpl ?>" x2="<?= $bW-$bpr ?>" y1="<?= $bpt+$bih ?>" y2="<?= $bpt+$bih ?>"/>
            <?php $k = 0; foreach ($days as $d => $cnt):
                $h = $cnt ? max(6, $bih * $cnt / $maxDay) : 2;
                $x = $bpl + $k * $slot + ($slot - $barW) / 2; $y = $bpt + $bih - $h; ?>
                <rect class="bar-rect <?= $cnt ? '' : 'empty' ?>" x="<?= round($x,1) ?>" y="<?= round($y,1) ?>" width="<?= round($barW,1) ?>" height="<?= round($h,1) ?>" rx="4"><title><?= e(date('d/m', strtotime($d)) . ': ' . $cnt . ' intento(s)') ?></title></rect>
                <?php if ($cnt): ?><text class="axis val" x="<?= round($x+$barW/2,1) ?>" y="<?= round($y-6,1) ?>" text-anchor="middle"><?= (int)$cnt ?></text><?php endif; ?>
                <text class="axis" x="<?= round($x+$barW/2,1) ?>" y="<?= $bH-6 ?>" text-anchor="middle"><?= e(date('d', strtotime($d))) ?></text>
            <?php $k++; endforeach; ?>
        </svg>
        </div>
        <div class="per-exam">
            <strong class="per-title">Más repetidos</strong>
            <?php if (!$perExam): ?>
                <p class="muted">Sin intentos todavía.</p>
            <?php else: foreach ($perExam as $name => $cnt): ?>
                <div class="per-row">
                    <div class="per-head"><span><?= e(function_exists('mb_strimwidth') ? mb_strimwidth($name,0,38,'…') : substr($name,0,38)) ?></span><b><?= (int)$cnt ?>×</b></div>
                    <div class="bar"><span style="width:<?= round($cnt/$maxPer*100) ?>%"></span></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<div class="two-col">
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">RENDIMIENTO</span><h3>Promedio por asignatura</h3></div></div>
        <div class="module-bars">
        <?php foreach ($modules as $m):
            $hasAttempts = isset($stats['moduleAvg'][$m['code']]);
            $avg = $hasAttempts ? (float)$stats['moduleAvg'][$m['code']] : 0;
        ?>
        <div class="module-row">
            <div class="module-row-head">
                <strong><?= e($m['code']) ?></strong>
                <span><?= $hasAttempts ? number_format($avg, 1, ',', '') . '/10' : 'Sin intentos' ?></span>
            </div>
            <div class="bar"><span style="width:<?= max(0, min(100, $avg * 10)) ?>%"></span></div>
        </div>
        <?php endforeach; ?>
    </div>
    </section>

    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">REPASO</span><h3>Preguntas que más fallas</h3></div></div>
        <?php if (!$weak): ?>
            <p class="muted">Todavía no hay errores registrados.</p>
        <?php else: ?>
            <div class="weak-list">
                <?php foreach ($weak as $w): ?>
                <div class="weak-item">
                    <span class="tag"><?= e($w['module_code'].' · '.$w['unit_code']) ?></span>
                    <p><?= e(mb_strimwidth($w['text'],0,125,'…')) ?></p>
                    <small><?= (int)$w['failures'] ?> fallo(s) en <?= (int)$w['attempts'] ?> intento(s)</small>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<section class="panel">
    <div class="panel-head">
        <div><span class="eyebrow">ACTIVIDAD</span><h3>Últimos intentos</h3></div>
        <a href="?page=history">Ver historial →</a>
    </div>
    <?php if (!$attempts): ?>
        <p class="muted">Aún no has realizado ningún examen.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Examen</th><th>Asignatura</th><th>Nota</th><th>Aciertos</th><th>Tiempo</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($attempts as $a): ?>
                <tr>
                    <td><?= e($a['exam_title']) ?></td>
                    <td><span class="tag"><?= e($a['module_code'].' · '.$a['unit_code']) ?></span></td>
                    <td><strong class="<?= e(score_class((float)$a['score'])) ?>"><?= number_format((float)$a['score'],1,',','') ?></strong></td>
                    <td><?= (int)$a['correct_count'] ?>/<?= (int)$a['total_count'] ?></td>
                    <td><?= e(format_duration((int)$a['duration_seconds'])) ?></td>
                    <td><a href="?page=result&id=<?= (int)$a['id'] ?>">Revisar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
