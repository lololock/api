<?php
// CORS
header("Access-Control-Allow-Origin: http://localhost"); 
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Réponse immédiate au preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '../../src/TaskController.php';

// Gérer les requêtes OPTIONS (CORS préflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Récupérer l’URL demandée (ex: /tasks/1)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si ton site est dans un sous-dossier, adapte ici
// Exemple: $basePath = '/api'; puis enlever ce préfixe
$basePath = ''; 
$path = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
$path = trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];
$segments = explode('/', $path);

// On gère seulement la ressource "tasks"
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

$controller = new TaskController();

if ($resource === 'tasks') {
    switch ($method) {
        
        case 'GET':
            
            if ($id === null) {
                $controller->index();   // GET /tasks
            } else {
                $controller->show($id); // GET /tasks/{id}
            }
            break;

        case 'POST':
            $controller->store();       // POST /tasks
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required']);
                exit;
            }
            $controller->update($id);   // PUT /tasks/{id}
            break;

        case 'DELETE':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required']);
                exit;
            }
            $controller->destroy($id);  // DELETE /tasks/{id}
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
