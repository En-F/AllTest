<?php
try {
        $json = trim((string)($_POST['json'] ?? ''));
        if (!empty($_FILES['json_file']['tmp_name'])) {
            $json = file_get_contents($_FILES['json_file']['tmp_name']) ?: '';
        }
        if ($json === '') throw new InvalidArgumentException('Pega un JSON o selecciona un archivo.');
        
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $decoded['imported'] = true;
            $json = json_encode($decoded, JSON_UNESCAPED_UNICODE);
        }

        $id = $storage->importExamJson($json);
        redirect('?page=exam&id='.$id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json = trim((string)($_POST['json'] ?? ''));
        if (!empty($_FILES['json_file']['tmp_name'])) {
            $json = file_get_contents($_FILES['json_file']['tmp_name']) ?: '';
        }
        if ($json === '') throw new InvalidArgumentException('Pega un JSON o selecciona un archivo.');
        $id = $storage->importExamJson($json);
        redirect('?page=exam&id='.$id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$example = [
    'module_code'=>'PIA',
    'unit_code'=>'UT2',
    'title'=>'Examen 01 · UT2',
    'description'=>'Ejemplo de importación',
    'questions'=>[
        [
            'text'=>'Texto de la pregunta',
            'options'=>['A'=>'Opción A','B'=>'Opción B','C'=>'Opción C','D'=>'Opción D'],
            'correct'=>'B',
            'explanation'=>'Explicación opcional.'
        ]
    ]
];
?>
<section class="section-title">
    <span class="eyebrow">IMPORTADOR</span>
    <h2>Importar examen desde JSON</h2>
    <p>Útil para cargar directamente los exámenes que preparemos a partir de capturas de Moodle.</p>
</section>
<?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<div class="two-col">
<form method="post" enctype="multipart/form-data" class="panel form-stack">
    <label>Archivo JSON
        <input type="file" name="json_file" accept=".json,application/json">
    </label>
    <div class="divider-text">o pega el contenido</div>
    <label>JSON
        <textarea name="json" rows="16" placeholder='{"module_code":"PIA", ...}'></textarea>
    </label>
    <button class="btn primary" type="submit">Importar examen</button>
</form>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">FORMATO</span><h3>Ejemplo válido</h3></div></div>
    <pre class="code-box"><?= e(json_encode($example, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
</section>
</div>
