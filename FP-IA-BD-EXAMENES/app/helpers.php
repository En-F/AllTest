<?php
declare(strict_types=1);
ob_start();

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function now_iso(): string {
    return date('c');
}

function module_block(string $code): string {
    return in_array($code, ['PIA', 'MIA', 'SAA'], true) ? 'IA' : 'Datos';
}

function score_class(float $score): string {
    if ($score >= 9) return 'score-excellent';
    if ($score >= 5) return 'score-pass';
    return 'score-fail';
}

function format_duration(int $seconds): string {
    $seconds = max(0, $seconds);
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    return sprintf('%02d:%02d', $m, $s);
}
