<?php
// Récupérer l'ID de l'utilisateur à partir de la session ou d'un paramètre dans l'URL
$user_id = $_GET['user_id'];

// Récupérer les données de l'utilisateur depuis la base de données
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Afficher les données de l'utilisateur
    while($row = $result->fetch_assoc()) {
        echo "Name: " . $row["name"]. "<br>";
        echo "Family Name: " . $row["family_name"]. "<br>";
        // Afficher d'autres informations de l'utilisateur de la même manière
    }
} else {
    echo "0 results";
}
?>
