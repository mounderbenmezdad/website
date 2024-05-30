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
    <title>Document</title>
    <style>
        :root {
    --font-family-sans-serif: "Open Sans", -apple-system, BlinkMacSystemFont,
    "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji",
    "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

*, *::before, *::after {
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}

html {
    font-family: sans-serif;
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
}

nav {
    display: block;
}

body {
    margin: 0;
    font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI",
    Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji",
    "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #515151;
    text-align: left;
    background-color: #e9edf4;
}

h1, h2, h3, h4, h5, h6 {
    margin-top: 0;
    margin-bottom: 0.5rem;
}

p {
    margin-top: 0;
    margin-bottom: 1rem;
}

a {
    color: #3f84fc;
    text-decoration: none;
    background-color: transparent;
}

a:hover {
    color: #0458eb;
    text-decoration: underline;
}

h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    font-family: "Nunito", sans-serif;
    margin-bottom: 0.5rem;
    font-weight: 500;
    line-height: 1.2;
}

h1, .h1 {
    font-size: 2.5rem;
    font-weight: normal;
}

.card {
    position: relative;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, 0.125);
    border-radius: 0;
}

.card-body {
    -webkit-box-flex: 1;
    -webkit-flex: 1 1 auto;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    padding: 1.25rem;
}

.card-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    text-align: center;
}

.dashboard {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    min-height: 100vh;
}

.section {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-flex: 2;
    -webkit-flex-grow: 2;
    -ms-flex-positive: 2;
    flex-grow: 2;
    margin-top: 84px;
}

.dashboard-content {
    -webkit-box-flex: 2;
    -webkit-flex-grow: 2;
    -ms-flex-positive: 2;
    flex-grow: 2;
    padding: 25px;
}

.dashboard-nav {
    min-width: 238px;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow: auto;
    background-color: #373193;
}

.dashboard-compact .dashboard-nav {
    display: none;
}

.dashboard-nav header {
    min-height: 84px;
    padding: 8px 27px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
}

.dashboard-nav header .menu-toggle {
    display: none;
    margin-right: auto;
}

.dashboard-nav a {
    color: #515151;
}

.dashboard-nav a:hover {
    text-decoration: none;
}

.dashboard-nav {
    background-color: #443ea2;
}

.dashboard-nav a {
    color: #fff;
}

.brand-logo {
    font-family: "Nunito", sans-serif;
    font-weight: bold;
    font-size: 20px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    color: #515151;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
}

.brand-logo:focus, .brand-logo:active, .brand-logo:hover {
    color: #dbdbdb;
    text-decoration: none;
}

.brand-logo i {
    color: #d2d1d1;
    font-size: 27px;
    margin-right: 10px;
}

.navigationbar {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
}

.dashboard-nav-item {
    min-height: 56px;
    padding: 8px 20px 8px 70px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    letter-spacing: 0.02em;
    transition: ease-out 0.5s;
}

.dashboard-nav-item i {
    width: 36px;
    font-size: 19px;
    margin-left: -40px;
}

.dashboard-nav-item:hover {
    background: rgba(255, 255, 255, 0.04);
}

.active {
    background: rgba(0, 0, 0, 0.1);
}

.dashboard-nav-dropdown {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
}

.dashboard-nav-dropdown.show {
    background: rgba(255, 255, 255, 0.04);
}

.dashboard-nav-dropdown.show > .dashboard-nav-dropdown-toggle {
    font-weight: bold;
}

.dashboard-nav-dropdown.show > .dashboard-nav-dropdown-toggle:after {
    -webkit-transform: none;
    -o-transform: none;
    transform: none;
}

.dashboard-nav-dropdown.show > .dashboard-nav-dropdown-menu {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
}

.dashboard-nav-dropdown-toggle:after {
    content: "";
    margin-left: auto;
    display: inline-block;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid rgba(81, 81, 81, 0.8);
    -webkit-transform: rotate(90deg);
    -o-transform: rotate(90deg);
    transform: rotate(90deg);
}

.dashboard-nav .dashboard-nav-dropdown-toggle:after {
    border-top-color: rgba(255, 255, 255, 0.72);
}

.dashboard-nav-dropdown-menu {
    display: none;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
}

.dashboard-nav-dropdown-item {
    min-height: 40px;
    padding: 8px 20px 8px 70px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    transition: ease-out 0.5s;
}

.dashboard-nav-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.04);
}






.menu-toggle {
    position: relative;
    width: 42px;
    height: 42px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
    color: #443ea2;
}

.menu-toggle:hover, .menu-toggle:active, .menu-toggle:focus {
    text-decoration: none;
    color: #875de5;
}

.menu-toggle i {
    font-size: 20px;
}

.dashboard-toolbar {
    min-height: 84px;
    background-color: #dfdfdf;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 8px 27px;
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1000;
}

.nav-item-divider {
    height: 1px;
    margin: 1rem 0;
    overflow: hidden;
    background-color: rgba(236, 238, 239, 0.3);
}

@media (min-width: 992px) {
    .section {
        margin-left: 238px;
    }

    .dashboard-compact .section {
        margin-left: 0;
    }
}


@media (max-width: 768px) {
    .dashboard-content {
        padding: 15px 0px;
    }
}

@media (max-width: 992px) {
    .dashboard-nav {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        z-index: 1070;
    }

    .dashboard-nav.mobile-show {
        display: block;
    }
}

@media (max-width: 992px) {
    .dashboard-nav header .menu-toggle {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
    }
}

@media (min-width: 992px) {
    .dashboard-toolbar {
        left: 238px;
    }

    .dashboard-compact .dashboard-toolbar {
        left: 0;
    }
}
    </style>
</head>
<body>
<div class='dashboard'>
    <div class="dashboard-nav">
        <header><a href="#!" class="menu-toggle"><i class="fas fa-bars"></i></a><a href="#"
                                                                                   class="brand-logo"><i
                class="fas fa-anchor"></i> <span>Hope Care</span></a></header>

        <nav class="navigationbar">
            <a href="#home" class="dashboard-nav-item"><i class="fas fa-home"></i>
            Home </a><a
                href="#notifications" class="dashboard-nav-item"><i class="fas fa-tachometer-alt"></i> Notifications
            </a>
            <li class="dashboard-nav-dropdown">
                <a href="javascript:void(0)" class="dashboard-nav-item dashboard-nav-dropdown-toggle">Our</a>
                <div class="dashboard-nav-dropdown-menu" >
                    <a href="#doctors" class="dashboard-nav-dropdown-item">Doctors</a>
                    <a href="#patients" class="dashboard-nav-dropdown-item">Patients</a>
                <a href="#nurses" class="dashboard-nav-dropdown-item">Nurses</a>
                </div>
            </li>            
            <a href="#messages" class="dashboard-nav-item"><i class="fas fa-cogs"></i> Messages </a><a
                    href="#profile" class="dashboard-nav-item"><i class="fas fa-user"></i> Profile </a>
            <div class="nav-item-divider"></div>
            <a
                href="#logout" class="dashboard-nav-item"><i class="fas fa-sign-out-alt"></i> Logout 
            </a>
        </nav>
    </div>
   </div>
    





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
        <!-- Important Messages or Announcements -->
        <div class="dashboard-section">
            <h2>Important Messages / Announcements</h2>
            <p>Check out our latest policy updates in the announcements section.</p>
        </div>

        <!-- Quick Links to Main Sections -->
        <div class="dashboard-section">
            <h2>Quick Links</h2>
            <ul>
                <li><a href="#doctors">View Doctors</a></li>
                <li><a href="#patients">View Patients</a></li>
                <li><a href="#nurses">View Nurses</a></li>
            </ul>
        </div>

        <!-- Recent Features or Updates -->
        <div class="dashboard-section">
            <h2>Recent Features / Updates</h2>
            <p>Check out our latest features and updates.</p>
        </div>

        <!-- Interface Customization Options -->
        <div class="dashboard-section">
            <h2>Interface Customization</h2>
            <p>Personalize your dashboard with customizable widgets.</p>
        </div>

        <!-- Medical / Health News -->
        <div class="dashboard-section">
            <h2>Medical / Health News</h2>
            <p>Stay informed with the latest medical and health news.</p>
        </div>

        <!-- Search Section -->
        <div class="dashboard-section">
            <h2>Search</h2>
            <p>Quickly find specific information using our search feature.</p>
        </div>

        <!-- Personalized Welcome Messages -->
        <div class="dashboard-section">
            <h2>Welcome Messages</h2>
            <p>Receive personalized welcome messages based on your role or status.</p>
        </div>
    </div>
</div></div>
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
    var navLinks = document.querySelectorAll('.dashboard-nav-item');

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

    // Ajouter un gestionnaire d'événements au bouton dropdown
    document.getElementById('dropdown-toggle').addEventListener('click', function () {
        document.querySelector('.dashboard-nav-dropdown-menu').classList.toggle('show');
    });



    var navLinks = document.querySelectorAll('.');
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


</script>
</body>
</html>