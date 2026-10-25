<?php
declare(strict_types=1);

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $str, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string {
        $sub = substr($str, $start);
        if (strlen($sub) <= $width) {
            return $sub;
        }
        $cut = max(0, $width - strlen($trim_marker));
        return substr($sub, 0, $cut) . $trim_marker;
    }
}

require_once __DIR__ . '/app/bootstrap.php';

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $str, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string {
        $sub = substr($str, $start);
        if (strlen($sub) <= $width) {
            return $sub;
        }
        $cut = max(0, $width - strlen($trim_marker));
        return substr($sub, 0, $cut) . $trim_marker;
    }
}

require_once __DIR__ . '/app/bootstrap.php';

if (current_page() === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $examId = (int)($_POST['exam_id'] ?? 0);
    $answers = $_POST['answers'] ?? [];
    $duration = max(0, min(86400, (int)($_POST['duration_seconds'] ?? 0)));
    $startedAt = (string)($_POST['started_at'] ?? date('c'));
    try {
        $attemptId = $storage->saveAttempt($examId, $answers, $duration, $startedAt);
        redirect('?page=result&id='.$attemptId);
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        redirect('?page=dashboard');
    }
}
if (current_page() === 'delete_attempt' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $attemptId = (int)($_POST['attempt_id'] ?? 0);
    if ($attemptId > 0) {
        $storage->deleteAttempt($attemptId);
    }
    redirect('?page=history');
}

$currentPage = $_GET['page'] ?? current_page();

if ($currentPage === 'delete_exam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $examId = (int)($_POST['exam_id'] ?? 0);
    if ($examId > 0) {
        $storage->deleteExam($examId);
    }
    redirect('?page=dashboard');
}

if (current_page() === 'clear_history' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage->clearHistory();
    redirect('?page=history');
}



require __DIR__ . '/pages/header.php';

if (!empty($_SESSION['flash_error'])) {
    echo '<div class="alert danger">'.e((string)$_SESSION['flash_error']).'</div>';
    unset($_SESSION['flash_error']);
}

switch (current_page()) {
    case 'exam': require __DIR__ . '/pages/exam.php'; break;
    case 'result': require __DIR__ . '/pages/result.php'; break;
    case 'history': require __DIR__ . '/pages/history.php'; break;
    case 'create': require __DIR__ . '/pages/create.php'; break;
    case 'import': require __DIR__ . '/pages/import.php'; break;
    default: require __DIR__ . '/pages/dashboard.php'; break;
}

require __DIR__ . '/pages/footer.php';