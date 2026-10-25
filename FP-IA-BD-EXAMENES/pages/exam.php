<?php
$examId = (int)($_GET['id'] ?? 0);
$exam = $storage->getExam($examId);
if (!$exam) {
    echo '<div class="alert danger">Examen no encontrado.</div>';
    return;
}
$onlyMissed = isset($_GET['missed_from']) ? (int)$_GET['missed_from'] : 0;
$questions = $exam['questions'] ?? [];
$missedReviewMap = []; 

if ($onlyMissed) {
    $previous = $storage->attemptDetail($onlyMissed);
    if ($previous && (int)($previous['exam_id'] ?? 0) === $examId) {
        $missedIds = [];
        foreach (($previous['review'] ?? []) as $idx => $r) {
            $rId = (int)($r['id'] ?? $r['question_id'] ?? $idx);
            if (!(int)($r['is_correct'] ?? 0)) {
                $missedIds[] = $rId;
                $missedReviewMap[$rId] = $r;
            }
        }
        if ($missedIds) {
            $questions = array_values(array_filter(
                $questions,
                fn($q, $idx) => in_array((int)($q['id'] ?? $q['question_id'] ?? $idx), $missedIds, true),
                ARRAY_FILTER_USE_BOTH
            ));
        }
    }
    }
shuffle($questions);
?>
<section class="exam-header">
    <div>
        <span class="tag"><?= e(($exam['module_code'] ?? '').' · '.($exam['unit_code'] ?? '')) ?></span>
        <h2><?= e($exam['title'] ?? '') ?> <?= $onlyMissed ? '(Repaso de fallos)' : '' ?></h2>
        <p><?= e($exam['description'] ?? '') ?></p>
    </div>
    <div class="timer-card">
        <span>Tiempo</span>
        <strong id="timer">00:00</strong>
    </div>
</section>

<form method="post" action="?page=submit" id="examForm" class="exam-layout">
    <input type="hidden" name="exam_id" value="<?= (int)$examId ?>">
    <input type="hidden" name="started_at" id="startedAt" value="<?= e(date('c')) ?>">
    <input type="hidden" name="duration_seconds" id="durationSeconds" value="0">

    <div class="question-stack">
        <?php foreach ($questions as $index => $q): 
            $qId = (int)($q['id'] ?? $q['question_id'] ?? $index);
            $rev = $missedReviewMap[$qId] ?? null;

            $userAns = $rev['user_answer'] ?? $rev['selected'] ?? $rev['chosen'] ?? $rev['answer'] ?? null;
            $correctAns = $rev['correct_answer'] ?? $rev['correct'] ?? $q['correct'] ?? $q['answer'] ?? null;

            $userAnsText = ($userAns !== null && isset($q['options'][$userAns])) 
                ? "$userAns) " . $q['options'][$userAns] 
                : ($userAns ?: 'Sin responder');

            $correctAnsText = ($correctAns !== null && isset($q['options'][$correctAns])) 
                ? "$correctAns) " . $q['options'][$correctAns] 
                : ($correctAns ?: 'No definida');
        ?>
        <article class="question-card" data-question="<?= $index+1 ?>" id="q<?= $qId ?>">
            <div class="question-kicker">Pregunta <?= $index+1 ?> de <?= count($questions) ?></div>
            <h3><?= e($q['text'] ?? '') ?></h3>

            <?php if ($onlyMissed && $rev): ?>
                <div class="missed-feedback" style="margin: 12px 0; padding: 12px 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.08); border-left: 4px solid #ef4444; font-size: 0.95rem;">
                    <div style="color: #ef4444; margin-bottom: 6px;">
                        <strong>✘ Tu respuesta anterior:</strong> <?= e($userAnsText) ?>
                    </div>
                    <div style="color: #10b981;">
                        <strong>✔ Respuesta correcta:</strong> <?= e($correctAnsText) ?>
                    </div>
                    <?php if (!empty($rev['explanation'] ?? $q['explanation'] ?? '')): ?>
                        <div style="margin-top: 8px; opacity: 0.85; font-size: 0.9rem;">
                            <strong>Explicación:</strong> <?= e($rev['explanation'] ?? $q['explanation']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="options">
            <?php foreach (($q['options'] ?? []) as $letter => $text): 
                $isUserWrong = ($onlyMissed && $userAns !== null && (string)$letter === (string)$userAns);
                $isCorrectOpt = ($onlyMissed && $correctAns !== null && (string)$letter === (string)$correctAns);
                
                $optStyle = '';
                if ($isCorrectOpt) {
                    $optStyle = 'border: 2px solid #10b981; background: rgba(16, 185, 129, 0.1);';
                } elseif ($isUserWrong) {
                    $optStyle = 'border: 2px solid #ef4444; background: rgba(239, 68, 68, 0.1);';
                }
            ?>
                <label class="option" style="<?= $optStyle ?>">
                    <input type="radio" name="answers[<?= $qId ?>]" value="<?= e($letter) ?>">
                    <span class="option-letter"><?= e($letter) ?></span>
                    <span>
                        <?= e($text) ?>
                        <?php if ($isCorrectOpt): ?>
                            <strong style="color: #10b981; margin-left: 6px;">(Correcta)</strong>
                        <?php elseif ($isUserWrong): ?>
                            <strong style="color: #ef4444; margin-left: 6px;">(Tu elección)</strong>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
            </div>
        </article>
        <?php endforeach; ?>

        <div class="exam-actions">
            <button class="btn primary" type="submit">Finalizar y corregir</button>
            <a class="btn secondary" href="?page=dashboard">Salir sin guardar</a>
        </div>
    </div>

    <aside class="question-nav">
        <strong>Navegación</strong>
        <div class="question-grid">
            <?php foreach ($questions as $index => $q): 
                $qId = (int)($q['id'] ?? $q['question_id'] ?? $index);
            ?>
                <a href="#q<?= $qId ?>" data-nav-for="<?= $qId ?>"><?= $index+1 ?></a>
            <?php endforeach; ?>
        </div>
        <div class="legend"><span><i class="answered"></i> Respondida</span><span><i></i> Pendiente</span></div>
    </aside>
</form>