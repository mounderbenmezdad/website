<?php
// Démarrez la session
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "memoire";

$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Récupérer les données du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['nom']; // nom d'utilisateur ou email
    $passw = $_POST['mdp'];
    $tables = array("medecins", "infirmiers", "patient");
    // Recherche du mot de passe dans chaque table
    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table WHERE ((name='$name' OR email='$name') AND password='$passw')";
        $result = $conn->query($sql);
        // Vérifier si l'utilisateur existe et que le mot de passe est correct
        if ($result->num_rows > 0) {
            // L'utilisateur est authentifié, stockez ses informations dans la session
            $row = $result->fetch_assoc();
            // Utilisez une variable de session différente pour chaque type d'utilisateur
            switch ($table) {
                case "medecins":
                    $_SESSION['medecins'] = $row;
                    header("Location: medecin.php");
                    break;
                case "infirmiers":
                    $_SESSION['infirmiers'] = $row;
                    header("Location: infirmier.php");
                    break;
                case "patient":
                    $_SESSION['patient'] = $row;
                    header("Location: patient.php");
                    break;
                default:
                    echo "Position non reconnue : " . $table;
            }
            exit(); // Terminer le script après la redirection
        }else {
            $sql = "SELECT * FROM chefservice WHERE ((name='$name' OR email='$name') AND password='$passw')";
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
        
            $row = $result->fetch_assoc();
            $_SESSION['chefservice'] = $row;
                header("Location: chefservice.php");
        }else {echo "Nom d'utilisateur ou mot de passe incorrect"; }
    }
    
    // Échec de l'authentification
    
}}
?>