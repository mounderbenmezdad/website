<?php
// Vérifier si le formulaire de sign-up a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name']) && isset($_POST['familyName']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['address']) && isset($_POST['bd']) && isset($_POST['phoneNumber']) && isset($_POST['email']) && isset($_POST['position'])) {

    // Récupérer les données du formulaire
    
    $name = $_POST['name'];
    $familyName = $_POST['familyName'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $address = $_POST['address'];
    $bd = $_POST['bd'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $sex = $_POST['sex'];
    function generer_id($position) {
        switch ($position) {
            case 'medecin':
                $prefixe = 'M';
                break;
            case 'patient':
                $prefixe = 'P';
                break;
            case 'infirmier':
                $prefixe = 'I';
                break;
            default:
                throw new Exception('Position invalide');
        }
        // Générez un nombre aléatoire entre 100000 et 999999
        $numero = mt_rand(100000, 999999);
        // Construisez l'ID complet
        $id_complet = $prefixe . $numero;
        return $id_complet;
    }
    $id=generer_id($_POST['position']);
    // Connexion à la base de données MySQL
    $servername = "localhost"; // Adresse du serveur MySQL
    $username = "root"; // Nom d'utilisateur MySQL
    $password_db = ""; // Mot de passe MySQL
    $database = "memoire"; // Nom de la base de données

    // Créer une connexion
    $conn = new mysqli($servername, $username, $password_db, $database);
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Préparer et exécuter la requête d'insertion
    $sql = "INSERT INTO users (sex,id,name, familyname, password, address, bd, phoneNumber, email, position) 
            VALUES ('$sex','$id','$name', '$familyName', '$password', '$address', '$bd', '$phoneNumber', '$email', '$position')";

    if ($conn->query($sql) === TRUE) {
        echo "login succefully";
        header("Location: log-in.html"); 
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Fermer la connexion à la base de données
    $conn->close();
} else {
    echo "Error: Form not submitted properly.";
}
?>
