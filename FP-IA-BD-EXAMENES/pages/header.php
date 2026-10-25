<?php
declare(strict_types=1);
$tree = nav_tree($modules, $exams);
$page = current_page();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FP IA & Big Data · Exam Center</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">AI</div>
            <div>
                <strong>FP IA & Big Data</strong>
                <small>Exam Center</small>
            </div>
        </div>
        <nav class="main-nav">
            <a class="<?= $page==='dashboard' ? 'active' : '' ?>" href="?page=dashboard">⌂ <span>Dashboard</span></a>
            <a class="<?= $page==='history' ? 'active' : '' ?>" href="?page=history">◷ <span>Historial</span></a>
            <a class="<?= $page==='create' ? 'active' : '' ?>" href="?page=create">＋ <span>Crear examen</span></a>
            <a class="<?= $page==='import' ? 'active' : '' ?>" href="?page=import">⇩ <span>Importar JSON</span></a>
        </nav>

        <div class="sidebar-label">EXÁMENES</div>
        <div class="exam-tree">
        <?php foreach ($tree as $code=>$m): ?>
            <details open>
                <summary><strong><?= e($code) ?></strong><span><?= e($m['block']) ?></span></summary>
                <?php if (!$m['units']): ?>
                    <div class="empty-tree">Sin exámenes</div>
                <?php else: ?>
                    <?php foreach ($m['units'] as $unit=>$unitExams): ?>
                        <details open class="unit">
                            <summary><?= e($unit) ?></summary>
                            <?php foreach ($unitExams as $exam): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <a href="?page=exam&id=<?= (int)$exam['id'] ?>" style="flex-grow: 1; text-decoration: none;">
                                        <span>📝</span>
                                        <span><?= e($exam['title']) ?></span>
                                    </a>
                                    
                                    <!-- Botón de eliminar para TODOS los exámenes -->
                                    <form method="post" action="?page=delete_exam" style="display:inline; margin-left: 8px;" onsubmit="return confirm('¿Seguro que quieres eliminar este examen y todo su historial?');">
                                        <input type="hidden" name="exam_id" value="<?= e((string)$exam['id']) ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 2px;" title="Eliminar examen">
                                            🗑️
                                        </button>
                                    </form>
                                    
                                </div>
                            <?php endforeach; ?>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
        </div>

        <div class="storage-mode">
            <span class="dot"></span>
            Datos: <?= e($storage->mode()) ?>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <button class="icon-btn mobile-only" id="menuBtn" type="button" aria-label="Abrir menú">☰</button>
            <div>
                <h1>FP IA BD Exámenes</h1>
                <p>Práctica, resultados y progreso</p>
            </div>
            <div class="top-actions">
                <a class="btn secondary" href="?page=create">＋ Nuevo examen</a>
            </div>
        </header>
