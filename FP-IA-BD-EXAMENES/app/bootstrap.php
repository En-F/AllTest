<?php
declare(strict_types=1);

session_start();
date_default_timezone_set('Europe/Madrid');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/storage.php';

$storage = new Storage(dirname(__DIR__) . '/data');
$modules = $storage->modules();
$exams = $storage->exams();

function current_page(): string {
    return $_GET['page'] ?? 'dashboard';
}

function nav_tree(array $modules, array $exams): array {
    $tree = [];
    foreach ($modules as $m) {
        $tree[$m['code']] = ['name'=>$m['name'],'block'=>$m['block'],'units'=>[]];
    }
    foreach ($exams as $exam) {
        $code = $exam['module_code'];
        $unit = $exam['unit_code'];
        if (!isset($tree[$code])) continue;
        $tree[$code]['units'][$unit] ??= [];
        $tree[$code]['units'][$unit][] = $exam;
    }
    return $tree;
}
