<?php
header('Content-Type: text/html; charset=UTF-8');
//Renvoyer tous les mots-clés existants dans la base de données

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ouketi";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);
// Vérifier la connexion

if (isset($_GET['search_ids'])) {
    $ids = array_filter(array_map('intval', explode(',', $_GET['search_ids'])));

    if (empty($ids)) {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $count = count($ids);

    $sql = "
        SELECT o.id, o.nom, o.quantite, o.estConteneur, o.estContenuDans
        FROM objet o
        JOIN correspond c ON o.id = c.idObjet
        WHERE c.idMotCle IN ($placeholders)
        GROUP BY o.id
        HAVING COUNT(DISTINCT c.idMotCle) = ?
    ";

    $stmt = $pdo->prepare($sql);
    $params = $ids;
    $params[] = $count;
    $stmt->execute($params);
    $objets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Typage correct des données
    foreach ($objets as &$row) {
        $row['id'] = (int)$row['id'];
        $row['quantite'] = (int)$row['quantite'];
        $row['estConteneur'] = (bool)$row['estConteneur'];
        $row['estContenuDans'] = $row['estContenuDans'] !== null ? (int)$row['estContenuDans'] : null;
    }

    echo json_encode($objets, JSON_UNESCAPED_UNICODE);
    exit;
}

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

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $motCleId = (int) $row['mot_cle_id'];

    if (!isset($mots_cles[$motCleId])) {
        $mots_cles[$motCleId] = [
            'id' => $motCleId,
            'libelle' => $row['libelle'],
            'objets' => []
        ];
    }

    if (!empty($row['objet_id'])) {
        $mots_cles[$motCleId]['objets'][] = [
            'id' => (int) $row['objet_id'],
            'nom' => $row['nom'],
            'quantite' => isset($row['quantite']) ? (int) $row['quantite'] : null,
            'estConteneur' => (bool) $row['estConteneur'],
            'estContenuDans' => $row['estContenuDans'] !== null ? (int) $row['estContenuDans'] : null
        ];
    }
}

echo json_encode(array_values($mots_cles), JSON_UNESCAPED_UNICODE);