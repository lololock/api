<?php

require_once __DIR__ . '/database.php';

class TaskController
{
    public function index()
    {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT * FROM tasks");
        $tasks = $stmt->fetchAll();
        $this->json($tasks);
    }

    public function show($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) {
            $this->json(['error' => 'Task not found'], 404);
            return;
        }

        $this->json($task);
    }

    public function store()
    {
        $data = $this->getJsonInput();

        if (empty($data['title'])) {
            $this->json(['error' => 'Title is required'], 400);
            return;
        }

        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO tasks (title, done) VALUES (?, ?)");
        $stmt->execute([
            $data['title'],
            isset($data['done']) ? (int)$data['done'] : 0
        ]);

        $id = $pdo->lastInsertId();
        $this->show($id);
    }

    public function update($id)
    {
        $data = $this->getJsonInput();

        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) {
            $this->json(['error' => 'Task not found'], 404);
            return;
        }

        $title = $data['title'] ?? $task['title'];
        $done  = isset($data['done']) ? (int)$data['done'] : (int)$task['done'];

        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, done = ? WHERE id = ?");
        $stmt->execute([$title, $done, $id]);

        $this->show($id);
    }

    public function destroy($id)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            $this->json(['error' => 'Task not found'], 404);
            return;
        }

        $this->json(['message' => 'Task deleted']);
    }

    private function 
            json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
