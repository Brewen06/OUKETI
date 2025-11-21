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
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT id, label FROM mots_cles";
$result = $conn->query($sql);
$mots_cles = array();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $mots_cles[] = array("id" => $row["id"], "label" => $row["label"]);
    }
}
$conn->close();
echo json_encode($mots_cles, JSON_UNESCAPED_UNICODE);

?>