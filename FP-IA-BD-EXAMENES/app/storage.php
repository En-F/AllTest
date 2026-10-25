<?php
declare(strict_types=1);

require_once __DIR__ . '/seed.php';

final class Storage {
    private bool $sqlite = false;
    private ?PDO $pdo = null;
    private string $jsonPath;

    public function __construct(string $dataDir) {
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        $this->jsonPath = $dataDir . '/fp_examenes.json';

        if (extension_loaded('pdo_sqlite')) {
            try {
                $this->pdo = new PDO('sqlite:' . $dataDir . '/fp_examenes.sqlite');
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->sqlite = true;
                $this->initSqlite();
                return;
            } catch (Throwable $e) {
                $this->sqlite = false;
                $this->pdo = null;
            }
        }
        $this->initJson();
    }

    public function mode(): string {
        return $this->sqlite ? 'SQLite' : 'JSON (compatibilidad)';
    }

    private function initSqlite(): void {
        $sql = [
            "CREATE TABLE IF NOT EXISTS modules (
                code TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                block_name TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS exams (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                module_code TEXT NOT NULL,
                unit_code TEXT NOT NULL,
                title TEXT NOT NULL,
                description TEXT NOT NULL DEFAULT '',
                created_at TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS questions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                exam_id INTEGER NOT NULL,
                position INTEGER NOT NULL,
                text TEXT NOT NULL,
                option_a TEXT NOT NULL,
                option_b TEXT NOT NULL,
                option_c TEXT NOT NULL,
                option_d TEXT NOT NULL,
                correct_answer TEXT NOT NULL,
                explanation TEXT NOT NULL DEFAULT ''
            )",
            "CREATE TABLE IF NOT EXISTS attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                exam_id INTEGER NOT NULL,
                started_at TEXT NOT NULL,
                finished_at TEXT NOT NULL,
                duration_seconds INTEGER NOT NULL DEFAULT 0,
                score REAL NOT NULL,
                correct_count INTEGER NOT NULL,
                total_count INTEGER NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS attempt_answers (
                attempt_id INTEGER NOT NULL,
                question_id INTEGER NOT NULL,
                selected_answer TEXT,
                is_correct INTEGER NOT NULL,
                PRIMARY KEY (attempt_id, question_id)
            )"
        ];
        foreach ($sql as $statement) $this->pdo->exec($statement);

        $count = (int)$this->pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
        if ($count === 0) $this->seedSqlite();
    }

    private function seedSqlite(): void {
        $seed = seed_catalog();
        $this->pdo->beginTransaction();
        try {
            $mStmt = $this->pdo->prepare("INSERT INTO modules(code,name,block_name) VALUES(?,?,?)");
            foreach ($seed['modules'] as $m) $mStmt->execute([$m['code'],$m['name'],$m['block']]);

            $examIdMap = [];
            foreach ($seed['exams'] as $exam) {
                $eStmt = $this->pdo->prepare("INSERT INTO exams(module_code,unit_code,title,description,created_at) VALUES(?,?,?,?,?)");
                $eStmt->execute([$exam['module_code'],$exam['unit_code'],$exam['title'],$exam['description'],$exam['created_at']]);
                $newExamId = (int)$this->pdo->lastInsertId();
                $examIdMap[$exam['id']] = $newExamId;

                $qStmt = $this->pdo->prepare("INSERT INTO questions(exam_id,position,text,option_a,option_b,option_c,option_d,correct_answer,explanation) VALUES(?,?,?,?,?,?,?,?,?)");
                foreach ($exam['questions'] as $pos => $q) {
                    $qStmt->execute([
                        $newExamId,$pos+1,$q['text'],
                        $q['options']['A'],$q['options']['B'],$q['options']['C'],$q['options']['D'],
                        $q['correct'],$q['explanation']
                    ]);
                }
            }

            foreach ($seed['attempts'] as $attempt) {
                $examId = $examIdMap[$attempt['exam_id']] ?? null;
                if (!$examId) continue;
                $aStmt = $this->pdo->prepare("INSERT INTO attempts(exam_id,started_at,finished_at,duration_seconds,score,correct_count,total_count) VALUES(?,?,?,?,?,?,?)");
                $aStmt->execute([$examId,$attempt['started_at'],$attempt['finished_at'],$attempt['duration_seconds'],$attempt['score'],$attempt['correct_count'],$attempt['total_count']]);
                $attemptId = (int)$this->pdo->lastInsertId();

                $questions = $this->pdo->prepare("SELECT id,position,correct_answer FROM questions WHERE exam_id=? ORDER BY position");
                $questions->execute([$examId]);
                $ansStmt = $this->pdo->prepare("INSERT INTO attempt_answers(attempt_id,question_id,selected_answer,is_correct) VALUES(?,?,?,?)");
                foreach ($questions->fetchAll() as $q) {
                    $selected = $attempt['answers'][(int)$q['position']] ?? null;
                    $ansStmt->execute([$attemptId,$q['id'],$selected,$selected === $q['correct_answer'] ? 1 : 0]);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function initJson(): void {
        if (!file_exists($this->jsonPath)) {
            file_put_contents($this->jsonPath, json_encode(seed_catalog(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function readJson(): array {
        $raw = @file_get_contents($this->jsonPath);
        $data = json_decode($raw ?: '', true);
        return is_array($data) ? $data : seed_catalog();
    }

    private function writeJson(array $data): void {
        file_put_contents($this->jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function modules(): array {
        if ($this->sqlite) {
            return $this->pdo->query("SELECT code,name,block_name AS block FROM modules ORDER BY CASE code WHEN 'PIA' THEN 1 WHEN 'MIA' THEN 2 WHEN 'SAA' THEN 3 WHEN 'BDA' THEN 4 ELSE 5 END")->fetchAll();
        }
        return $this->readJson()['modules'] ?? [];
    }

    public function exams(): array {
        if ($this->sqlite) {
            $rows = $this->pdo->query("SELECT e.*, (SELECT COUNT(*) FROM questions q WHERE q.exam_id=e.id) AS question_count FROM exams e ORDER BY module_code, unit_code, id")->fetchAll();
            return $rows;
        }
        $data = $this->readJson();
        $out = [];
        foreach ($data['exams'] ?? [] as $exam) {
            $copy = $exam;
            unset($copy['questions']);
            $copy['question_count'] = count($exam['questions'] ?? []);
            $out[] = $copy;
        }
        return $out;
    }

    public function getExam(int $id): ?array {
        if ($this->sqlite) {
            $stmt = $this->pdo->prepare("SELECT * FROM exams WHERE id=?");
            $stmt->execute([$id]);
            $exam = $stmt->fetch();
            if (!$exam) return null;

            $q = $this->pdo->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY position");
            $q->execute([$id]);
            $questions = [];
            foreach ($q->fetchAll() as $row) {
                $questions[] = [
                    'id'=>(int)$row['id'],
                    'text'=>$row['text'],
                    'options'=>[
                        'A'=>$row['option_a'],'B'=>$row['option_b'],
                        'C'=>$row['option_c'],'D'=>$row['option_d']
                    ],
                    'correct'=>$row['correct_answer'],
                    'explanation'=>$row['explanation']
                ];
            }
            $exam['questions'] = $questions;
            return $exam;
        }
        foreach ($this->readJson()['exams'] ?? [] as $exam) {
            if ((int)$exam['id'] === $id) return $exam;
        }
        return null;
    }

    public function attempts(?int $examId = null): array {
        if ($this->sqlite) {
            $sql = "SELECT a.*, e.title AS exam_title, e.module_code, e.unit_code FROM attempts a JOIN exams e ON e.id=a.exam_id";
            $params = [];
            if ($examId !== null) {
                $sql .= " WHERE a.exam_id=?";
                $params[] = $examId;
            }
            $sql .= " ORDER BY a.id DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
        $data = $this->readJson();
        $examMap = [];
        foreach ($data['exams'] ?? [] as $e) $examMap[(int)$e['id']] = $e;
        $rows = [];
        foreach ($data['attempts'] ?? [] as $a) {
            if ($examId !== null && (int)$a['exam_id'] !== $examId) continue;
            $e = $examMap[(int)$a['exam_id']] ?? [];
            $a['exam_title'] = $e['title'] ?? 'Examen';
            $a['module_code'] = $e['module_code'] ?? '';
            $a['unit_code'] = $e['unit_code'] ?? '';
            $rows[] = $a;
        }
        usort($rows, fn($a,$b)=>(int)$b['id'] <=> (int)$a['id']);
        return $rows;
    }

    public function attemptDetail(int $attemptId): ?array {
        if ($this->sqlite) {
            $stmt = $this->pdo->prepare("SELECT a.*, e.title AS exam_title, e.module_code, e.unit_code FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.id=?");
            $stmt->execute([$attemptId]);
            $attempt = $stmt->fetch();
            if (!$attempt) return null;
            $q = $this->pdo->prepare("SELECT q.*, aa.selected_answer, aa.is_correct FROM attempt_answers aa JOIN questions q ON q.id=aa.question_id WHERE aa.attempt_id=? ORDER BY q.position");
            $q->execute([$attemptId]);
            $attempt['review'] = $q->fetchAll();
            return $attempt;
        }
        $data = $this->readJson();
        $attempt = null;
        foreach ($data['attempts'] ?? [] as $a) if ((int)$a['id'] === $attemptId) $attempt = $a;
        if (!$attempt) return null;
        $exam = $this->getExam((int)$attempt['exam_id']);
        $review = [];
        foreach ($exam['questions'] ?? [] as $idx => $q) {
            $selected = $attempt['answers'][$idx + 1] ?? null;
            $review[] = [
                'position'=>$idx+1,
                'text'=>$q['text'],
                'option_a'=>$q['options']['A'],'option_b'=>$q['options']['B'],
                'option_c'=>$q['options']['C'],'option_d'=>$q['options']['D'],
                'correct_answer'=>$q['correct'],
                'explanation'=>$q['explanation'],
                'selected_answer'=>$selected,
                'is_correct'=>$selected === $q['correct'] ? 1 : 0
            ];
        }
        $attempt['exam_title'] = $exam['title'] ?? 'Examen';
        $attempt['module_code'] = $exam['module_code'] ?? '';
        $attempt['unit_code'] = $exam['unit_code'] ?? '';
        $attempt['review'] = $review;
        return $attempt;
    }

    public function saveAttempt(int $examId, array $answers, int $durationSeconds, string $startedAt): int {
        $exam = $this->getExam($examId);
        if (!$exam) throw new RuntimeException('Examen no encontrado.');

        $correct = 0;
        foreach ($exam['questions'] as $q) {
            $selected = $answers[(int)$q['id']] ?? null;
            if ($selected === $q['correct']) $correct++;
        }
        $total = count($exam['questions']);
        $score = $total > 0 ? round(($correct / $total) * 10, 2) : 0;

        if ($this->sqlite) {
            $this->pdo->beginTransaction();
            try {
                $stmt = $this->pdo->prepare("INSERT INTO attempts(exam_id,started_at,finished_at,duration_seconds,score,correct_count,total_count) VALUES(?,?,?,?,?,?,?)");
                $stmt->execute([$examId,$startedAt,date('c'),$durationSeconds,$score,$correct,$total]);
                $attemptId = (int)$this->pdo->lastInsertId();

                $aStmt = $this->pdo->prepare("INSERT INTO attempt_answers(attempt_id,question_id,selected_answer,is_correct) VALUES(?,?,?,?)");
                foreach ($exam['questions'] as $q) {
                    $selected = $answers[(int)$q['id']] ?? null;
                    $aStmt->execute([$attemptId,$q['id'],$selected,$selected === $q['correct'] ? 1 : 0]);
                }
                $this->pdo->commit();
                return $attemptId;
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        $data = $this->readJson();
        $ids = array_map(fn($a)=>(int)$a['id'], $data['attempts'] ?? []);
        $attemptId = $ids ? max($ids)+1 : 1;

        $positionAnswers = [];
        foreach ($exam['questions'] as $idx => $q) {
            $positionAnswers[$idx+1] = $answers[(int)$q['id']] ?? null;
        }
        $data['attempts'][] = [
            'id'=>$attemptId,'exam_id'=>$examId,'started_at'=>$startedAt,
            'finished_at'=>date('c'),'duration_seconds'=>$durationSeconds,
            'score'=>$score,'correct_count'=>$correct,'total_count'=>$total,
            'answers'=>$positionAnswers
        ];
        $this->writeJson($data);
        return $attemptId;
    }

    public function stats(): array {
        $attempts = $this->attempts();
        $count = count($attempts);
        $avg = $count ? array_sum(array_map(fn($a)=>(float)$a['score'], $attempts)) / $count : 0;
        $passes = count(array_filter($attempts, fn($a)=>(float)$a['score'] >= 5));
        $best = $count ? max(array_map(fn($a)=>(float)$a['score'], $attempts)) : 0;
        $worst = $count ? min(array_map(fn($a)=>(float)$a['score'], $attempts)) : 0;

        $byModule = [];
        foreach ($attempts as $a) {
            $code = $a['module_code'] ?? '?';
            $byModule[$code][] = (float)$a['score'];
        }
        $moduleAvg = [];
        foreach ($byModule as $code => $scores) $moduleAvg[$code] = array_sum($scores)/count($scores);

        return compact('count','avg','passes','best','worst','moduleAvg');
    }

    public function weakQuestions(int $limit = 8): array {
        if ($this->sqlite) {
            $stmt = $this->pdo->prepare("
                SELECT q.id,q.text,e.module_code,e.unit_code,
                       COUNT(*) AS attempts,
                       SUM(CASE WHEN aa.is_correct=0 THEN 1 ELSE 0 END) AS failures
                FROM attempt_answers aa
                JOIN questions q ON q.id=aa.question_id
                JOIN exams e ON e.id=q.exam_id
                GROUP BY q.id
                HAVING failures > 0
                ORDER BY failures DESC, attempts DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $data = $this->readJson();
        $stats = [];
        foreach ($data['attempts'] ?? [] as $a) {
            $exam = $this->getExam((int)$a['exam_id']);
            foreach ($exam['questions'] ?? [] as $idx => $q) {
                $selected = $a['answers'][$idx+1] ?? null;
                $key = $a['exam_id'] . ':' . ($idx+1);
                $stats[$key] ??= ['id'=>$idx+1,'text'=>$q['text'],'module_code'=>$exam['module_code'],'unit_code'=>$exam['unit_code'],'attempts'=>0,'failures'=>0];
                $stats[$key]['attempts']++;
                if ($selected !== $q['correct']) $stats[$key]['failures']++;
            }
        }
        $rows = array_values(array_filter($stats, fn($r)=>$r['failures']>0));
        usort($rows, fn($a,$b)=>[$b['failures'],$b['attempts']] <=> [$a['failures'],$a['attempts']]);
        return array_slice($rows,0,$limit);
    }

    public function createExam(array $payload): int {
        $module = strtoupper(trim((string)($payload['module_code'] ?? '')));
        $unit = strtoupper(trim((string)($payload['unit_code'] ?? '')));
        $title = trim((string)($payload['title'] ?? ''));
        $description = trim((string)($payload['description'] ?? ''));
        $questions = $payload['questions'] ?? [];

        $validModules = array_column($this->modules(), 'code');
        if (!in_array($module, $validModules, true)) throw new InvalidArgumentException('Asignatura no válida.');
        if ($unit === '' || $title === '') throw new InvalidArgumentException('Unidad y título son obligatorios.');
        if (!is_array($questions) || count($questions) === 0) throw new InvalidArgumentException('El examen necesita al menos una pregunta.');

        $normalized = [];
        foreach ($questions as $q) {
            $text = trim((string)($q['text'] ?? ''));
            $correct = strtoupper(trim((string)($q['correct'] ?? '')));
            $options = $q['options'] ?? [];
            if ($text === '' || !in_array($correct,['A','B','C','D'],true)) throw new InvalidArgumentException('Pregunta o respuesta correcta no válida.');
            foreach (['A','B','C','D'] as $letter) {
                if (trim((string)($options[$letter] ?? '')) === '') throw new InvalidArgumentException("Falta la opción $letter.");
            }
            $normalized[] = [
                'text'=>$text,'options'=>$options,'correct'=>$correct,
                'explanation'=>trim((string)($q['explanation'] ?? ''))
            ];
        }

        if ($this->sqlite) {
            $this->pdo->beginTransaction();
            try {
                $stmt = $this->pdo->prepare("INSERT INTO exams(module_code,unit_code,title,description,created_at) VALUES(?,?,?,?,?)");
                $stmt->execute([$module,$unit,$title,$description,date('c')]);
                $examId = (int)$this->pdo->lastInsertId();
                $qStmt = $this->pdo->prepare("INSERT INTO questions(exam_id,position,text,option_a,option_b,option_c,option_d,correct_answer,explanation) VALUES(?,?,?,?,?,?,?,?,?)");
                foreach ($normalized as $i=>$q) {
                    $qStmt->execute([$examId,$i+1,$q['text'],$q['options']['A'],$q['options']['B'],$q['options']['C'],$q['options']['D'],$q['correct'],$q['explanation']]);
                }
                $this->pdo->commit();
                return $examId;
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        $data = $this->readJson();
        $ids = array_map(fn($e)=>(int)$e['id'], $data['exams'] ?? []);
        $examId = $ids ? max($ids)+1 : 1;
        foreach ($normalized as $i=>&$q) $q['id'] = $i+1;
        $data['exams'][] = [
            'id'=>$examId,'module_code'=>$module,'unit_code'=>$unit,
            'title'=>$title,'description'=>$description,'created_at'=>date('c'),
            'questions'=>$normalized
        ];
        $this->writeJson($data);
        return $examId;
    }

    public function importExamJson(string $json): int {
        $data = json_decode($json, true);
        if (!is_array($data)) throw new InvalidArgumentException('JSON inválido.');
        return $this->createExam($data);
    }

    
public function deleteAttempt(int $attemptId): void
{
    if ($attemptId <= 0) {
        return;
    }

    if ($this->sqlite) {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM attempt_answers WHERE attempt_id = ?"
            );
            $stmt->execute([$attemptId]);

            $stmt = $this->pdo->prepare(
                "DELETE FROM attempts WHERE id = ?"
            );
            $stmt->execute([$attemptId]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return;
    }

    $data = $this->readJson();

    $data['attempts'] = array_values(array_filter(
        $data['attempts'] ?? [],
        fn(array $a): bool =>
            (int)($a['id'] ?? 0) !== $attemptId
    ));

    $this->writeJson($data);
}


public function clearHistory(): void
{
    if ($this->sqlite) {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec("DELETE FROM attempt_answers");
            $this->pdo->exec("DELETE FROM attempts");

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return;
    }

    $data = $this->readJson();
    $data['attempts'] = [];

    $this->writeJson($data);
}

public function deleteExam(int $examId): void 
    {
        if ($this->sqlite) {
            $this->pdo->beginTransaction();
            try {
                $this->pdo->prepare("DELETE FROM attempt_answers WHERE attempt_id IN (SELECT id FROM attempts WHERE exam_id = ?)")->execute([$examId]);
                $this->pdo->prepare("DELETE FROM attempts WHERE exam_id = ?")->execute([$examId]);
                $this->pdo->prepare("DELETE FROM questions WHERE exam_id = ?")->execute([$examId]);
                $this->pdo->prepare("DELETE FROM exams WHERE id = ?")->execute([$examId]);
                $this->pdo->commit();
            } catch (Throwable $e) {
                $this->pdo->rollBack();
            }
            return;
        }

        $data = $this->readJson();
        
        if (isset($data['exams'])) {
            $data['exams'] = array_values(array_filter(
                $data['exams'], 
                fn($e) => (int)$e['id'] !== $examId
            ));
        }
        
        if (isset($data['attempts'])) {
            $data['attempts'] = array_values(array_filter(
                $data['attempts'], 
                fn($a) => (int)$a['exam_id'] !== $examId
            ));
        }
        
        $this->writeJson($data);
    }
}