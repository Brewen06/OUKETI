<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ouketi';

try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion a la base de donnees']);
    exit;
}

function readJsonBody() {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function sanitizeLabel($label) {
    return trim((string)$label);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare('SELECT id, libelle FROM motscles ORDER BY libelle ASC');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $result = array_map(function ($row) {
            return [
                'id' => (int)$row['id'],
                'libelle' => $row['libelle'],
            ];
        }, $rows);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $data = readJsonBody();
        $libelle = sanitizeLabel($data['libelle'] ?? '');

        if ($libelle === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Le libelle est obligatoire'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO motscles (libelle) VALUES (?)');
        $stmt->execute([$libelle]);

        echo json_encode([
            'message' => 'Mot-cle ajoute avec succes',
            'id' => (int)$pdo->lastInsertId(),
            'libelle' => $libelle,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'PUT') {
        $data = readJsonBody();
        $id = (int)($data['id'] ?? 0);
        $libelle = sanitizeLabel($data['libelle'] ?? '');

        if ($id <= 0 || $libelle === '') {
            http_response_code(400);
            echo json_encode(['error' => 'ID et libelle obligatoires'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE motscles SET libelle = ? WHERE id = ?');
        $stmt->execute([$libelle, $id]);

        if ($stmt->rowCount() === 0) {
            $checkStmt = $pdo->prepare('SELECT id FROM motscles WHERE id = ?');
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Mot-cle introuvable'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        echo json_encode([
            'message' => 'Mot-cle modifie avec succes',
            'id' => $id,
            'libelle' => $libelle,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalide'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM motscles WHERE id = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Mot-cle introuvable'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['message' => 'Mot-cle supprime avec succes'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Methode non autorisee'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode([
            'error' => 'Ce mot-cle est utilise par des objets et ne peut pas etre supprime pour le moment.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
}
