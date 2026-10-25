<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $questions = [];
        $count = max(1, min(50, (int)($_POST['question_count'] ?? 1)));
        for ($i=1; $i<=$count; $i++) {
            if (trim((string)($_POST["q{$i}_text"] ?? '')) === '') continue;
            $questions[] = [
                'text'=>$_POST["q{$i}_text"] ?? '',
                'options'=>[
                    'A'=>$_POST["q{$i}_a"] ?? '',
                    'B'=>$_POST["q{$i}_b"] ?? '',
                    'C'=>$_POST["q{$i}_c"] ?? '',
                    'D'=>$_POST["q{$i}_d"] ?? ''
                ],
                'correct'=>$_POST["q{$i}_correct"] ?? '',
                'explanation'=>$_POST["q{$i}_explanation"] ?? ''
            ];
        }
        $id = $storage->createExam([
            'module_code'=>$_POST['module_code'] ?? '',
            'unit_code'=>$_POST['unit_code'] ?? '',
            'title'=>$_POST['title'] ?? '',
            'description'=>$_POST['description'] ?? '',
            'questions'=>$questions
        ]);
        redirect('?page=exam&id='.$id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<section class="section-title">
    <span class="eyebrow">EDITOR</span>
    <h2>Crear examen manualmente</h2>
    <p>Añade preguntas A/B/C/D y la respuesta correcta. Puedes ampliar el formulario hasta 50 preguntas.</p>
</section>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="panel form-stack" id="createExamForm">
    <div class="form-grid">
        <label>Asignatura
            <select name="module_code" required>
                <?php foreach ($modules as $m): ?><option value="<?= e($m['code']) ?>"><?= e($m['code'].' · '.$m['name']) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>Unidad
            <input name="unit_code" placeholder="UT1" required>
        </label>
    </div>
    <label>Título
        <input name="title" placeholder="Examen 02 · Nombre" required>
    </label>
    <label>Descripción
        <textarea name="description" rows="2" placeholder="Opcional"></textarea>
    </label>

    <input type="hidden" name="question_count" id="questionCount" value="1">
    <div id="questionBuilder"></div>
    <div class="action-row">
        <button class="btn secondary" id="addQuestion" type="button">＋ Añadir pregunta</button>
        <button class="btn primary" type="submit">Guardar examen</button>
    </div>
</form>
