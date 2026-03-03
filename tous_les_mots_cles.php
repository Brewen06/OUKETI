<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ouketi";

try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Recherche d'objets par mots-clés
if (isset($_GET['search_ids']) && !empty($_GET['search_ids'])) {
    $rawIds = $_GET['search_ids'];
    
    // Validation et nettoyage des IDs
    $ids = array_filter(
        array_map('intval', explode(',', $rawIds)),
        function($id) { return $id > 0; }
    );

    if (empty($ids)) {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $count = count($ids);

    // Requête pour trouver les objets qui ont TOUS les mots-clés sélectionnés
    $sql = "
        SELECT o.id, o.nom, o.quantite, o.estConteneur, o.estContenuDans
        FROM objet o
        JOIN correspond c ON o.id = c.idObjet
        WHERE c.idMotCle IN ($placeholders)
        GROUP BY o.id, o.nom, o.quantite, o.estConteneur, o.estContenuDans
        HAVING COUNT(DISTINCT c.idMotCle) = ?
        ORDER BY o.nom ASC
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $params = array_merge($ids, [$count]);
        $stmt->execute($params);
        $objets = $stmt->fetchAll();

        // Typage correct des données
        foreach ($objets as &$row) {
            $row['id'] = (int)$row['id'];
            $row['quantite'] = (int)$row['quantite'];
            $row['estConteneur'] = (bool)$row['estConteneur'];
            $row['estContenuDans'] = $row['estContenuDans'] !== null ? (int)$row['estContenuDans'] : null;
        }

        echo json_encode($objets, JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la recherche'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Liste de tous les mots-clés avec leurs objets associés
try {
    $sql = "
        SELECT
            m.id AS mot_cle_id,
            m.libelle,
            o.id AS objet_id,
            o.nom,
            o.quantite,
            o.estConteneur,
            o.estContenuDans
        FROM motscles m
        LEFT JOIN correspond c ON c.idMotCle = m.id
        LEFT JOIN objet o ON o.id = c.idObjet
        ORDER BY m.libelle ASC, o.nom ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $mots_cles = [];

    while ($row = $stmt->fetch()) {
        $motCleId = (int)$row['mot_cle_id'];

        if (!isset($mots_cles[$motCleId])) {
            $mots_cles[$motCleId] = [
                'id' => $motCleId,
                'libelle' => $row['libelle'],
                'objets' => []
            ];
        }

        if (!empty($row['objet_id'])) {
            $mots_cles[$motCleId]['objets'][] = [
                'id' => (int)$row['objet_id'],
                'nom' => $row['nom'],
                'quantite' => (int)$row['quantite'],
                'estConteneur' => (bool)$row['estConteneur'],
                'estContenuDans' => $row['estContenuDans'] !== null ? (int)$row['estContenuDans'] : null
            ];
        }
    }

    echo json_encode(array_values($mots_cles), JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des mots-clés'], JSON_UNESCAPED_UNICODE);
}