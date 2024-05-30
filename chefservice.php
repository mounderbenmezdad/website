<?php
session_start(); 
$servername = "localhost";
$username = "root";
$password = "";
$database = "memoire";
if (!isset($_SESSION['chefservice'])) {
    // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
    header("Location: login.php");
    exit(); // Terminer le script après la redirection
  }
  $chefservice = $_SESSION['chefservice'];
  $user_id = $_SESSION['chefservice']['id']; // Utilisez l'ID du patient, pas du médecin
// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}
$notificationid = isset($_POST['notificationid']) ? $_POST['notificationid'] : (isset($_GET['notificationid']) ? $_GET['notificationid'] : '');
$midid = isset($_POST['midid']) ? $_POST['midid'] : (isset($_GET['midid']) ? $_GET['midid'] : '');
$infid = isset($_POST['infid']) ? $_POST['infid'] : (isset($_GET['infid']) ? $_GET['infid'] : '');
// Si le bouton Accepter est cliqué
if (isset($_POST["accepter"])) {
    // Récupération des données de l'utilisateur à partir de la table des utilisateurs
    $sql = "SELECT * FROM users WHERE id='$notificationid'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row["name"];
        $familyName = $row["familyName"];
        $email = $row["email"];
        $phoneNumber = $row["phoneNumber"];
        $address = $row["address"];
        $position = $row["position"];
        $password = $row["password"];
        $bd = $row["bd"];
        $sex = $row["sex"];
        $notificationid = $row["id"];
        $deleteuser = "DELETE FROM users WHERE id='$notificationid'";
        if ($conn->query($deleteuser) === TRUE) {
        switch ($position) {
            case "medecin":
                $sql = "INSERT INTO medecins (name, email, phoneNumber, address, password, id, familyName, position, bd, sex) 
                        VALUES ('$name', '$email', '$phoneNumber', '$address', '$password', '$notificationid', '$familyName', '$position', '$bd', '$sex')";
                
                break;
            case "infirmier":
                $sql = "INSERT INTO infirmiers (name, email, phoneNumber, address, password, id, familyName, position, bd, sex) 
                        VALUES ('$name', '$email', '$phoneNumber', '$address', '$password', '$notificationid', '$familyName', '$position', '$bd', '$sex')";
                break;
            case "patient":{
                $midid = $_POST['midid'];
                // Supprimer l'utilisateur de la table users
                    $sql = "INSERT INTO patient (name, email, phoneNumber, address, password, id, familyName, position, bd, sex) 
                            VALUES ('$name', '$email', '$phoneNumber', '$address', '$password', '$notificationid', '$familyName', '$position', '$bd', '$sex')";
                    if ($conn->query($sql) === TRUE) {
                        $dossiermedical = "INSERT INTO dossiermedical (idpatient, idmedecin, infirmierid) VALUES ('$notificationid', '$midid', '$infid')";
                        if ($conn->query($dossiermedical) === TRUE) {
                            $sqlexamen = "SELECT * FROM salles WHERE id='EXAM'";
                            $resultatexamen = $conn->query($sqlexamen);
                            if ($resultatexamen->num_rows > 0) {
                                $salle = $resultatexamen->fetch_assoc();
                                if ($salle['nbrpatients'] == $salle['capacite']) {
                                    $sqlInsertPatientSalle = "INSERT INTO patientsalle (id_patient, id_salle,id_medecin) VALUES ('$notificationid', 'ATTEX1','$midid')";
                                    if ($conn->query($sqlInsertPatientSalle) === TRUE) {
                                        echo "La salle EXAM est pleine. Le patient a été ajouté à la salle d'attente examen.";
                                    } else {
                                        echo "Erreur lors de l'insertion du patient dans la salle d'attente examen.";
                                    }
                                } else {
                                    $sqlUpdate = "UPDATE salles SET nbrpatients = nbrpatients + 1 WHERE id = 'EXAM'";
                                    if ($conn->query($sqlUpdate) === TRUE) {
                                        $sqlInsertPatientExamen = "INSERT INTO patientsalle (id_patient, id_salle,id_medecin) VALUES ('$notificationid', 'EXAM','$midid')";
                                        if ($conn->query($sqlInsertPatientExamen) === TRUE) {
                                            echo "Le patient a été ajouté à la salle d'examen.";
                                        } else {
                                            echo "Erreur lors de l'insertion du patient dans la salle examen.";
                                        }
                                    } else {
                                        echo "Erreur lors de la mise à jour du nombre de patients.";
                                    }
                                }
                            } else {
                                echo "Erreur lors de la récupération des informations de la salle.";
                            }
                        } else {
                            echo "Erreur lors de l'insertion dans le dossier médical.";
                        }
                    } else {
                        echo "Erreur lors de l'insertion du patient.";
                    }
                 }
                break;
            default:
                die("Erreur : Position non valide.");
        }
} 
        if ($position != 'patient') {
            if ($conn->query($sql) === TRUE) {
                echo "Utilisateur accepté et inséré dans la table appropriée.";
            } else {
                echo "Erreur lors de l'insertion de l'utilisateur : " . $conn->error;
            }
        }
    } else {
        echo "Utilisateur non trouvé.";
    }}

    // Si le bouton Refuser est cliqué
    if (isset($_POST["refuser"])) {
        // Suppression de l'utilisateur de la table des utilisateurs
        $deleteUserSql = "DELETE FROM users WHERE id='$notificationid'";
        if ($conn->query($deleteUserSql) === TRUE) {
            echo "L'utilisateur a été refusé et supprimé de la table des utilisateurs.";
        } else {
            echo "Erreur lors de la suppression de l'utilisateur : " . $conn->error;
        }
    }

    // Si le bouton Supprimer est cliqué
    if (isset($_POST["supprimer"])&&!empty($_POST['listeid'])) {
        $listeid = $_POST['listeid'];
        $table = $_POST["table"];
        // Suppression de l'utilisateur de la table appropriée
        $deleteUserSql = "DELETE FROM $table WHERE id='$listeid'";
        if ($conn->query($deleteUserSql) === TRUE) {
            echo "L'utilisateur a été supprimé.";
        } else {
            echo "Erreur lors de la suppression de l'utilisateur : " . $conn->error;
        }
    }




$sqlDoctors = "SELECT COUNT(*) AS totalDoctors FROM medecins";
$resultDoctors = $conn->query($sqlDoctors);
$rowDoctors = $resultDoctors->fetch_assoc();
$totalDoctors = $rowDoctors['totalDoctors'];
//medecins hommes
$sqlhommem = "SELECT COUNT(*) AS totalhommem FROM medecins where sex='homme'";
$resulthommem = $conn->query($sqlhommem);
$rowhommem = $resulthommem->fetch_assoc();
$totalhommem = $rowhommem['totalhommem'];
//medecins femmes
$sqlfemmem = "SELECT COUNT(*) AS totalfemmem FROM medecins where sex='femme'";
$resultfemmem = $conn->query($sqlfemmem);
$rowfemmem = $resultfemmem->fetch_assoc();
$totalfemmem = $rowfemmem['totalfemmem'];


// Récupération du nombre total de patients
$sqlPatients = "SELECT COUNT(*) AS totalPatients FROM patient";
$resultPatients = $conn->query($sqlPatients);
$rowPatients = $resultPatients->fetch_assoc();
$totalPatients = $rowPatients['totalPatients'];
//patients hommes
$sqlhommep = "SELECT COUNT(*) AS totalhommep FROM patient where sex='homme'";
$resulthommep = $conn->query($sqlhommep);
$rowhommep = $resulthommep->fetch_assoc();
$totalhommep = $rowhommep['totalhommep'];
//femme patients
$sqlfemmep = "SELECT COUNT(*) AS totalfemmep FROM patient where sex='femme'";
$resultfemmep = $conn->query($sqlfemmep);
$rowfemmep = $resultfemmep->fetch_assoc();
$totalfemmep = $rowfemmep['totalfemmep'];

// Récupération du nombre total d'infirmiers
$sqlNurses = "SELECT COUNT(*) AS totalNurses FROM infirmiers";
$resultNurses = $conn->query($sqlNurses);
$rowNurses = $resultNurses->fetch_assoc();
$totalNurses = $rowNurses['totalNurses'];
//homme infirmiers
$sqlhommei = "SELECT COUNT(*) AS totalhommei FROM infirmiers where sex='homme'";
$resulthommei = $conn->query($sqlhommei);
$rowhommei = $resulthommei->fetch_assoc();
$totalhommei = $rowhommei['totalhommei'];
//femme infirmiers
$sqlfemmei = "SELECT COUNT(*) AS totalfemmei FROM infirmiers where sex='femme'";
$resultfemmei = $conn->query($sqlfemmei);
$rowfemmei = $resultfemmei->fetch_assoc();
$totalfemmei = $rowfemmei['totalfemmei'];
    

$medecinid = isset($_POST['medecinid']) ? $_POST['medecinid'] : (isset($_GET['medecinid']) ? $_GET['medecinid'] : '');
$medecinname = isset($_POST['medecinname']) ? $_POST['medecinname'] : '';

// Insérer un nouveau message dans la base de données
if (isset($_POST['envoyer']) && !empty($_POST['nouveau_message']) && !empty($medecinid)) {
    $nouveau_message = $_POST['nouveau_message'];
    $sql_insert = "INSERT INTO messages (sender, reciver, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param("sss", $user_id, $medecinid, $nouveau_message);
    if ($stmt->execute() === false) {
        die("Erreur lors de l'exécution de la requête : " . $stmt->error);
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?medecinid=" . $medecinid);
    exit;
}

if (isset($_POST['logout'])) {
    // Supprime toutes les variables de session
    session_unset();

    // Détruit la session
    session_destroy();

    // Redirige l'utilisateur vers la page de connexion
    header("Location: log-in.html");
    exit();
}
// Fermeture de la connexion à la base de données
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Navbar Example</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
body { /* Définissez la largeur souhaitée */
    height: 100vh; /* Définissez la hauteur souhaitée */
    margin: auto; /* Centrez le body horizontalement */
    overflow: auto; /* Ajoutez une barre de défilement si le contenu dépasse */
    
    font-family: Arial, sans-serif; /* Police de caractères */
    min-height: 100vh;
    font-family: Arial, sans-serif;
    background-image: url('memoire/hosp.png')
}

.navbar {
    background-color: #333;
    color: #fff;
    padding: 10px;
    display: flex;
}
.navbar ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
}
.navbar li {
    display: inline-block;
    margin-right: 20px;
}
.navbar li a {
    color: #fff;
    text-decoration: none;
    padding: 10px;
}
.dropdown {
    position: relative;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #333;
    min-width: 120px;
    z-index: 1;
}
.dropdown:hover .dropdown-content {
    display: block;
}
.section {
    padding: 20px;
    border: 1px solid #ccc;
    margin-top: 20px;
    background-color: #f9f9f9;
    border-radius: 5px;
}
.section ul {
    list-style-type: none;
    padding: 0;
}
.section li {
    margin-bottom: 10px;
}
.user-info {
    font-weight: bold;
}
.accept-button, .reject-button, .delete-button {
    background-color: #007bff;
    color: white;
    padding: 10px;
    border: none;
    cursor: pointer;
    margin-right: 5px;
    border-radius: 5px;
}
.accept-button:hover, .reject-button:hover, .delete-button:hover {
    background-color: #0056b3;
}
.content {
    padding: 20px;
    background-image: url('cmemoire/hosp.png');
}
.logout-button {
    background-color: #f44336;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    margin-top: 20px;
    border-radius: 5px;
}
.logout-button:hover {
    background-color: #da190b;
}
.card {
    flex: 0 1 calc(33.33% - 20px);
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-height: 90vh; /* Hauteur maximale de la div */
}
.container{
    max-height: 100vh; /* Hauteur maximale de la div */
    background-image: url('memoire/hosp.png'); /* Couleur de fond */
}
.card-content {
    text-align: center;
}
.card-content p {
    margin: 10px 0;
}
.send-button{
    background-color: red;
    height: 10%;
    width: 10%;
}
.chat-app {
    display: flex;
    max-height: 90vh; /* Hauteur maximale de la div */
}
.chat-app .people-list {
    width: 280px;
    padding: 20px;
    background: #fff;
    overflow-y: auto;
    z-index: 7;


}
.chat{
    max-height: 90vh; /* Hauteur maximale de la div */
    overflow-y: auto; /* Ajouter une barre de défilement si nécessaire */
    padding: 20px; /* Ajouter un espace intérieur pour le contenu */

}
.chat-app .chat {
    margin-left: 280px;
    flex: 1;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #eaeaea;
    max-height: 90vh; /* Hauteur maximale de la div */
}
.people-list {
    -moz-transition: .5s;
    -o-transition: .5s;
    -webkit-transition: .5s;
    transition: .5s
}
.people-list .chat-list li {
    padding: 10px 15px;
    list-style: none;
    border-radius: 3px
}

.people-list .chat-list li:hover {
    background: #efefef;
    cursor: pointer
}

.people-list .chat-list li.active {
    background: #efefef
}

.people-list .chat-list li .name {
    font-size: 15px
}

.people-list .chat-list img {
    width: 45px;
    border-radius: 50%
}

.people-list img {
    float: left;
    border-radius: 50%
}

.people-list .about {
    float: left;
    padding-left: 8px
}

.people-list .status {
    color: #999;
    font-size: 13px
}

.chat .chat-header {
    padding: 15px 20px;
    border-bottom: 2px solid #f4f7f6
}

.chat .chat-header img {
    float: left;
    border-radius: 40px;
    width: 40px
}

.chat .chat-header .chat-about {
    float: left;
    padding-left: 10px
}

.chat .chat-history {
    padding: 20px;
    border-bottom: 2px solid;
    flex: 1 ; 
    overflow-y:auto;
}

.chat .chat-history ul {
    padding: 0
}

.chat .chat-history ul li {
    list-style: none;
    margin-bottom: 30px
}

.chat .chat-history ul li:last-child {
    margin-bottom: 0px
}

.chat .chat-history .message-data {
    margin-bottom: 15px
}

.chat .chat-history .message {
    color: #444;
    padding: 18px 20px;
    line-height: 26px;
    font-size: 16px;
    border-radius: 7px;
    display: inline-block;
    position: relative;
    background-color: red;
}

.chat .chat-history .message:after {
    bottom: 100%;
    left: 7%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-bottom-color: #fff;
    border-width: 10px;
    margin-left: -10px
}
/* Style des messages envoyés par vous (moi) */
.chat .chat-history .my-message {
    background: #efefef;
    display: flex;
    flex-direction: column;
    width: 30%;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    position: relative;
}

.chat .chat-history .my-message:after {
    bottom: 100%;
    left: 30px;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-bottom-color: #efefef;
    border-width: 10px;
    margin-left: -10px;
}

/* Style des messages envoyés par les autres utilisateurs (leur) */
.chat .chat-history .other-message {
    background: #e8f1f3;
    display: flex;
    flex-direction: column;
    width: 30%;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    position: relative;
}

.chat .chat-history .other-message:after {
    bottom: 100%;
    left: calc(100% - 20px);
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-bottom-color: #e8f1f3;
    border-width: 10px;
    margin-left: -10px;
}


.chat .chat-message {
    padding: 20px;
    border-top: 1px solid #f4f7f6;
    display: flex;
}

.online,
.offline,
.me {
    margin-right: 2px;
    font-size: 8px;
    vertical-align: middle
}

.online {
    color: #86c541
}

.offline {
    color: #e47297
}

.me {
    color: #1d8ecd
}

.float-right {
    float: right
}

.clearfix:after {
    visibility: hidden;
    display: block;
    font-size: 0;
    content: " ";
    clear: both;
    height: 0
}
.msgtime{
    width: 100%;
}
.form-control {
    width: 70%;
    margin-right: 2%;
    max-width: 100%;
    height: 60px; /* Fixed height */
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    box-sizing: border-box;
    overflow-y: auto;
    resize: none; /* Prevent resizing */
}
.envoyer {
    background-color: #007bff; /* Blue background color */
    color: white; /* White text color */
    padding: 10px 20px; /* Padding around the button */
    border: none; /* Remove default border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    font-size: 16px; /* Font size */
    transition: background-color 0.3s ease; /* Smooth transition for background color */
}

.envoyer:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

</style>
</head>
<body>
    <div class="container">
<nav class="navbar">
    <ul>
        <li><a href="#home" class="nav-link">Home</a></li>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Our</a>
            <div class="dropdown-content" >
                <a href="#doctors" class="nav-link">Doctors</a>
                <a href="#patients" class="nav-link">Patients</a>
                <a href="#nurses" class="nav-link">Nurses</a>
            </div>
        </li>
        <li><a href="#messages" class="nav-link">Messages</a></li>
        <li><a href="#notifications" class="nav-link">Notifications</a></li>
        <li> <form method="post">
        <input type="submit" name="logout" class="logout-button"value="Déconnexion"></form></li>
    </ul>
</nav>
<div class="content">
    <!-- Content of different sections will go here -->
    <div id="home" class="section"><div class="content">
        <div class="statistics">
            <h1>Welcome!</h1>
            <table>
        <tr>
            <th>Groupe</th>
        <th>Total</th>
        <th>Hommes</th>
        <th>Femmes</th>
        </tr>
        <tr>
        <td>Médecins</td>
        <td><?php echo $totalDoctors; ?></td>
        <td><?php echo $totalhommem; ?></td>
        <td><?php echo $totalfemmem; ?></td>
    </tr>
    <tr>
        <td>Patients</td>
        <td><?php echo $totalPatients; ?></td>
        <td><?php echo $totalhommep; ?></td>
        <td><?php echo $totalfemmep; ?></td>
    </tr>
    <tr>
        <td>Infirmiers</td>
        <td><?php echo $totalNurses; ?></td>
        <td><?php echo $totalhommei; ?></td>
        <td><?php echo $totalfemmei; ?></td>
    </tr>
    <tr>
        <td>Total </td>
        <td><?php echo $totalDoctors + $totalPatients + $totalNurses; ?></td>
        <td><?php echo $totalhommem + $totalhommep + $totalhommei; ?></td>
        <td><?php echo $totalfemmem + $totalfemmep + $totalfemmei; ?></td>
    </tr>
</table>
    </div>
    </div>
</div>
</div>
    <div id="doctors" class="section">
        <ul>
            <?php
            // Connexion à la base de données (à remplacer par vos propres informations de connexion)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "memoire";

            $conn = new mysqli($servername, $username, $password, $database);

            // Vérification de la connexion
            if ($conn->connect_error) {
                die("La connexion à la base de données a échoué : " . $conn->connect_error);
            }

            // Récupération des nouvelles inscriptions depuis la table des utilisateurs
            $sql = "SELECT * FROM medecins";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // Affichage des nouvelles inscriptions
                while ($row = $result->fetch_assoc()) {                    
                    echo "<div class='card'>";
                    echo "<div class='card-content'>";
                    echo "<p>" . $row["name"] . " " . $row["familyName"] . "</p>";
                    echo "<p>Email: " . $row["email"] . "</p>";
                    // Autres informations à ajouter si nécessaire
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='listeid' value='" . $row["id"] . "'>";
                    echo "<input type='hidden' name='table' value='medecins'>";
                    echo "<button type='submit' name='supprimer' class='delete-button'>Supprimer</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<li>There's no doctors.</li>";
            }

            // Fermeture de la connexion à la base de données
            $conn->close();
            ?>
        </ul>
    </div>
    <div id="patients" class="section">
        <ul>
            <?php
            // Connexion à la base de données (à remplacer par vos propres informations de connexion)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "memoire";

            $conn = new mysqli($servername, $username, $password, $database);

            // Vérification de la connexion
            if ($conn->connect_error) {
                die("La connexion à la base de données a échoué : " . $conn->connect_error);
            }

            // Récupération des nouvelles inscriptions depuis la table des utilisateurs
            $sql = "SELECT * FROM patient";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Affichage des nouvelles inscriptions
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card'>";
                    echo "<div class='card-content'>";
                    echo "<p>" . $row["name"] . " " . $row["familyName"] . "</p>";
                    echo "<p>Email: " . $row["email"] . "</p>";
                    // Autres informations à ajouter si nécessaire
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='listeid' value='" . $row["id"] . "'>";
                    echo "<input type='hidden' name='table' value='patient'>";
                    echo "<button type='submit' name='supprimer' class='delete-button'>Supprimer</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<li>There's no patient</li>";
            }

            // Fermeture de la connexion à la base de données
            $conn->close();
            ?>
        </ul>
    </div>
    <div id="nurses" class="section">
        <ul>
            <?php
            // Connexion à la base de données (à remplacer par vos propres informations de connexion)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "memoire";

            $conn = new mysqli($servername, $username, $password, $database);

            // Vérification de la connexion
            if ($conn->connect_error) {
                die("La connexion à la base de données a échoué : " . $conn->connect_error);
            }

            // Récupération des nouvelles inscriptions depuis la table des utilisateurs
            $sql = "SELECT * FROM infirmiers";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Affichage des nouvelles inscriptions
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card'>";
                    echo "<div class='card-content'>";
                    echo "<p>" . $row["name"] . " " . $row["familyName"] . "</p>";
                    echo "<p>Email: " . $row["email"] . "</p>";
                    // Autres informations à ajouter si nécessaire
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='listeid' value='" . $row["id"] . "'>";
                    echo "<input type='hidden' name='table' value='infirmiers'>";
                    echo "<button type='submit' name='supprimer' class='delete-button'>Supprimer</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<li>There's no nurse</li>";
            }

            // Fermeture de la connexion à la base de données
            $conn->close();
            ?>
        </ul>
    </div>
    <div id="messages" class="section">
    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card chat-app">
                <div id="plist" class="people-list">
                    <ul class="list-unstyled chat-list mt-2 mb-0">
                    <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $database = "memoire";
        
                    $conn = new mysqli($servername, $username, $password, $database);
        
                    // Vérification de la connexion
                    if ($conn->connect_error) {
                        die("La connexion à la base de données a échoué : " . $conn->connect_error);
                    }
                    $sql = "SELECT id, name, familyName FROM medecins";
                    $result = $conn->query($sql);

                    if ($result === false) {
                        die("Erreur lors de la récupération des médecins : " . $conn->error);
                    }

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<li class='clearfix'>";
                            echo "<img src='doctor.png' alt='avatar'>";
                            echo "<span class='name'>" . htmlspecialchars($row["name"]) . " " . htmlspecialchars($row["familyName"]) . "</span>";
                            echo "<form method='post'>";
                            echo "<input type='hidden' name='medecinid' value='" . $row["id"] . "'>";
                            echo "<input type='hidden' name='medecinname' value='" . $row["name"] . "'>";
                            echo "<button type='submit' name='discuter' class='delete-button'>Discuter</button>";
                            echo "</form>";
                            echo "</li>";
                        }
                    } else {
                        echo "<p>Aucun médecin trouvé.</p>";
                    }
                    ?>
                    </ul>
                </div>
                <div class="chat">
                    <div class="chat-header clearfix">
                        <div class="row">
                            <div class="col-lg-6">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#view_info">
                                    <img src="https://bootdey.com/img/Content/avatar/avatar2.png" alt="avatar">
                                </a>
                                <div class="chat-about">
                                    <h1 class="m-b-0"><?php echo htmlspecialchars($medecinname); ?></h1>
                                    <small>Last seen: 2 hours ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history">
                        <ul class="m-b-0">
                        <?php
                        if (!empty($medecinid)) {
                            $sql = "SELECT * FROM messages WHERE (sender = ? AND reciver = ?) OR (sender = ? AND reciver = ?) ORDER BY timestamp";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                die("Erreur de préparation de la requête : " . $conn->error);
                            }
                            $stmt->bind_param("ssss", $user_id, $medecinid, $medecinid, $user_id);
                            if ($stmt->execute() === false) {
                                die("Erreur lors de l'exécution de la requête : " . $stmt->error);
                            }
                            $result = $stmt->get_result();
                            if ($result === false) {
                                die("Erreur lors de la récupération des messages : " . $stmt->error);
                            }
                            while ($row = $result->fetch_assoc()) {
                                $message_sender = $row["sender"];
                                $message_content = htmlspecialchars($row["message"]);
                                $messageclass = ($message_sender == $user_id) ? "message my-message" : "message other-message float-right";
                                echo "<li class='clearfix'>";
                                echo "<div class='$messageclass'>";
                                echo "<span class='msgtime'>" . htmlspecialchars($row["timestamp"]) . "</span>";
                                echo "<span class='message-content'>$message_content</span>";
                                echo "</div>";
                                echo "</li>";
                            }
                            $stmt->close();
                        }
                        ?>
                        </ul>
                    </div>
                    <form class="chat-message clearfix" method="post">
                        <input type="hidden" name="medecinid" value="<?php echo htmlspecialchars($medecinid); ?>">
                        <textarea class="form-control" name="nouveau_message" placeholder="Entrez votre message ici..." required></textarea>
                        <button type="submit" name="envoyer" class="envoyer">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $conn->close(); ?>
</div>
    <div id="notifications" class="section">
    <ul>
        <?php
        // Connexion à la base de données
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "memoire";
        $conn = new mysqli($servername, $username, $password, $database);

        // Vérification de la connexion
        if ($conn->connect_error) {
            die("La connexion à la base de données a échoué : " . $conn->connect_error);
        }

        // Récupération des nouvelles inscriptions depuis la table des utilisateurs
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // Affichage des nouvelles inscriptions
            while ($row = $result->fetch_assoc()) {
                echo "<li>";
                echo "<span class='user-info'>" . htmlspecialchars($row["name"]) . " " . htmlspecialchars($row["familyName"]) . " - " . htmlspecialchars($row["position"]) . "</span>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='notificationid' value='" . htmlspecialchars($row["id"]) . "'>";
                $pi = $row["id"];   
                if ($row["position"] == 'patient') {
                    echo "<div id='form-$pi' style='display:none;' methode='post' >";
                    echo "<select name='midid'>";
                    $choisirunmdc = "SELECT * FROM medecins";
                    $resultchoisirmdc = $conn->query($choisirunmdc);
                    if ($resultchoisirmdc->num_rows > 0) {
                        while ($colmdc = $resultchoisirmdc->fetch_assoc()) {
                            $mid = $colmdc["id"];
                            echo "<option value='$mid'>" . htmlspecialchars($colmdc["name"]) . " " . htmlspecialchars($colmdc["familyName"]) . "</option>";
                        }
                    }
                    echo "</select>";

                    echo "<select name='infid'>";
                    $choisiruninf = "SELECT * FROM infirmiers";
                    $resultchoisirinf = $conn->query($choisiruninf);
                    if ($resultchoisirinf->num_rows > 0) {
                        while ($colinf = $resultchoisirinf->fetch_assoc()) {
                            $i = $colinf["id"];
                            echo "<option value='$i'>" . htmlspecialchars($colinf["name"]) . " " . htmlspecialchars($colinf["familyName"]) . "</option>";
                        }
                    }
                    echo "</select>";
                    echo "</div>";
                    echo "<button type='button' name='choisirid' class='choisirbtn' data-id='form-$pi'>$pi</button>";
                }
                echo "<button type='submit' name='accepter' id='acceptbtn' class='accept-button'>Accepter</button>";
                echo "<button type='submit' name='refuser' class='reject-button'>Refuser</button>";
                echo "</form>";
                echo "</li>";
            }
        } else {
            echo "<li>Aucune nouvelle inscription.</li>";
        }

        // Fermeture de la connexion à la base de données
        $conn->close();
        ?>
    </ul>
    </div>
</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Sélection de tous les liens de navigation
    var navLinks = document.querySelectorAll('.nav-link');

    // Sélection de toutes les sections
    var sections = document.querySelectorAll('.section');

    // Masquer toutes les sections sauf la première au chargement de la page
    sections.forEach(function(section) {
        section.style.display = 'none';
    });
    sections[0].style.display = 'block'; // Afficher la première section

    // Ajouter un écouteur d'événements à chaque lien de navigation
    navLinks.forEach(function(navLink) {
        navLink.addEventListener('click', function(event) {
            event.preventDefault(); // Empêcher le comportement de lien par défaut

            // Masquer toutes les sections
            sections.forEach(function(section) {
                section.style.display = 'none';
            });

            // Afficher la section correspondante
            var targetId = navLink.getAttribute('href').substring(1); // Récupérer l'identifiant de la section cible
            var targetSection = document.getElementById(targetId);
            targetSection.style.display = 'block';
            localStorage.setItem('activeSection', targetId);
        });
    });

    
    
    // Fonctionnalité des boutons choisirbtn
    var choisirButtons = document.querySelectorAll('.choisirbtn');
    choisirButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var formId = this.getAttribute('data-id');
            var formElement = document.getElementById(formId);

            if (formElement.style.display === 'none' || formElement.style.display === '') {
                formElement.style.display = 'block';
            } else {
                formElement.style.display = 'none';
            }
        });
    });


    // Récupérer la section active depuis le stockage local
    var activeSection = localStorage.getItem('activeSection');
    if (activeSection) {
        sections.forEach(function(section) {
            section.style.display = 'none';
        });
        var targetSection = document.getElementById(activeSection);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
    }
});
</script>
</body>
</html>
