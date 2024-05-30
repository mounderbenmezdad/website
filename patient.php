<?php
session_start(); 
$servername = "localhost";
$username = "root";
$password = "";
$database = "memoire";

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['patient'])) {
  // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
  header("Location: log-in.html");
  exit(); // Terminer le script après la redirection
}
$patient = $_SESSION['patient'];
$user_id = $_SESSION['patient']['id']; // Utilisez l'ID du patient, pas du médecin

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}
$error_message = '';
$success_message = '';
// Rendez-vous
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rendezvousbtn'])) {
    $appointment_for = $_POST['appointment_for'];
    $appointment_description = $_POST['appointment_description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $datetime = $date . ' ' . $time;

    $sql_insert = "INSERT INTO rendezvous (patientid, doctorid, date, sujet, sender) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        $error_message = "Erreur de préparation de la requête : " . $conn->error;
    } else {
        $stmt->bind_param("sssss", $user_id, $appointment_for, $datetime, $appointment_description, $user_id);
        if ($stmt->execute() === false) {
            $error_message = "Erreur lors de l'exécution de la requête : " . $stmt->error;
        } else {
            $success_message = "Votre demande de rendez-vous a été soumise avec succès.";
        }
        $stmt->close();
    }
}

$recivername = isset($_POST['recivername']) ? $_POST['recivername'] : (isset($_GET['recivername']) ? $_GET['recivername'] : '');
$reciverid = isset($_POST['reciverid']) ? $_POST['reciverid'] : (isset($_GET['reciverid']) ? $_GET['reciverid'] : '');


function getMedecinInfo($conn, $user_id) {
    $medecinInfo = array();

    $monmedecin = "SELECT * FROM medecins m JOIN dossiermedical dm ON m.id=dm.idmedecin WHERE dm.idpatient='$user_id'";
    $resultmonmedecin = $conn->query($monmedecin);

    if ($resultmonmedecin->num_rows > 0) {
        $medecinInfo = $resultmonmedecin->fetch_assoc();
    }
    return $medecinInfo;
}
$medecinInfo = getMedecinInfo($conn, $user_id);
// Insérer un nouveau message dans la base de données
if (isset($_POST['envoyer']) && !empty($_POST['nouveau_message']) && !empty($reciverid)) {
    $nouveau_message = $_POST['nouveau_message'];
    $sql_insert = "INSERT INTO messages (sender, reciver, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param("sss", $user_id, $reciverid, $nouveau_message);
    if ($stmt->execute() === false) {
        die("Erreur lors de l'exécution de la requête : " . $stmt->error);
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?medecinid=" . $reciverid);
    exit;
}





function getFormulaireInfirmierInfo($conn, $user_id) {
    $formulaires = array();

    $sql = "SELECT * FROM formulaireInfirmier WHERE IDPatient='$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $formulaires[] = $row;
        }
    }

    return $formulaires;
}
$formulairesInfirmier = getFormulaireInfirmierInfo($conn, $user_id);
$mesrendezvous = "SELECT * FROM rendezvous WHERE patientid='$user_id' AND sender != '$user_id'";
$resultmesrendezvous = $conn->query($mesrendezvous);

if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['updateprofile']) ) {
    $name = $conn->real_escape_string($_POST['name']);
    $familyName = $conn->real_escape_string($_POST['familyName']);
    $email = $conn->real_escape_string($_POST['email']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Vérifiez si l'email est déjà pris par un autre utilisateur
    $sql_check_email = "SELECT id FROM patient WHERE email = ? AND id != ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    if ($stmt_check_email === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    $stmt_check_email->bind_param("ss", $email, $user_id);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
  
    if ($stmt_check_email->num_rows > 0) {
        // L'email est déjà utilisé par un autre utilisateur
        $error_message = "L'email est déjà pris.";
    } else {
        // Vérifiez si le numéro de téléphone est déjà pris par un autre utilisateur
        $sql_check_phone = "SELECT id FROM patient WHERE phoneNumber = ? AND id != ?";
        $stmt_check_phone = $conn->prepare($sql_check_phone);
        if ($stmt_check_phone === false) {
            die("Erreur de préparation de la requête : " . $conn->error);
        }
        $stmt_check_phone->bind_param("ss", $phoneNumber, $user_id);
        $stmt_check_phone->execute();
        $stmt_check_phone->store_result();
  
        if ($stmt_check_phone->num_rows > 0) {
            // Le numéro de téléphone est déjà utilisé par un autre utilisateur
            $error_message = "Le numéro de téléphone est déjà pris.";
        } else {
            // Mise à jour des informations dans la base de données
            $sql_update = "UPDATE patient SET 
                            name = ?, 
                            familyName = ?, 
                            email = ?, 
                            phoneNumber = ?, 
                            sex = ?, 
                            address = ? 
                            WHERE id = ?";
  
            $stmt = $conn->prepare($sql_update);
            if ($stmt === false) {
                die("Erreur de préparation de la requête : " . $conn->error);
            }
            $stmt->bind_param("sssssss", $name, $familyName, $email, $phoneNumber, $sex, $address, $user_id);
            if ($stmt->execute() === false) {
                die("Erreur lors de l'exécution de la requête : " . $stmt->error);
            }
            $stmt->close();
  
            // Mise à jour des informations de session
            $patient['name'] = $name;
            $patient['familyName'] = $familyName;
            $patient['email'] = $email;
            $patient['phoneNumber'] = $phoneNumber;
            $patient['sex'] = $sex;
            $patient['address'] = $address;
  
            // Rediriger vers la page de profil ou afficher un message de succès
            header("Location: patient.php");
            exit;
        }
        $stmt_check_phone->close();
    }
    $stmt_check_email->close();
  }


  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    unset($_SESSION['patient']);
    header("Location: log-in.html"); // Redirige vers la page de connexion après la déconnexion
    exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    body {
  font-family: "Inter", sans-serif;
  line-height: 1.5;
  min-height: 100vh;
  display: flex; 
  margin-left: 1%;
  flex-direction: row;
  align-items: center;
  padding-top: 5vh;
  padding-bottom: 5vh;
  background-image: url('hop.jpg');
  background-size: cover; /* Ajoute cette ligne pour que l'image couvre tout le body */
  background-position: center; /* Centre l'image */
  background-repeat: no-repeat; /* Évite que l'image se répète */
}

    .navbar{
    margin-right: 10%;
    margin-left: 1%;
   }
    .navbarsections { 
      list-style: none;
      margin: 0;
      padding: 0;
      margin-left: 1%; 
      margin-right: auto;
      background-color: #05043e;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: .75rem;
      border-radius: 10px;
      box-shadow: 0 10px 50px 0 rgba(5, 4, 62, 0.25);
    }

    .navbarsection:nth-child(6) {
      margin-top: 5rem; 
      padding-top: 1.25rem;
      border-top: 1px solid #363664;
    }

    .navbarsection + .navbarsection {
      margin-top: .75rem;
    }

    .nav-link {
      color: #FFF; 
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3rem;
      height: 3rem;
      border-radius: 8px;
      position: relative;
    }
    .edit-form {
  align-items: center;
  text-align: center;
  background-color: rgba(255, 255, 255, 0.5); /* Gris avec 50% de transparence */
  border: 2px solid black; /* Bordure noire de 2 pixels */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Ombre */
}


    .nav-link:hover, .nav-link:focus, .nav-link.active {
      background-color: #30305a;
      outline: 0;
    }

    .nav-link:hover span, a:focus span, a.active span {
      transform: scale(1);
      opacity: 1;
    }

    .nav-link span {
      position: absolute;
      background-color: #30305a;
      white-space: nowrap;
      padding: .5rem 1rem;
      border-radius: 6px;
      left: calc(100% + 1.5rem);
      transform-origin: center left;
      transform: scale(0);
      opacity: 0;
      transition: .15s ease;
    }

    .section {
      display: none;
      width: 100%;
      background-color: transparent;
    }

    .section.active {
      height: 50%;
      width: 100%;
      display: block;
    }
    

    .savepr {
  background-color: #4CAF50; /* Couleur de fond verte */
  border: none; /* Pas de bordure par défaut */
  color: white; /* Texte en blanc */
  padding: 15px 32px; /* Padding pour agrandir le bouton */
  text-align: center; /* Centre le texte */
  text-decoration: none; /* Pas de soulignement */
  display: inline-block; /* Affichage en ligne */
  font-size: 16px; /* Taille de police */
  margin: 4px 2px; /* Marge */
  cursor: pointer; /* Curseur en forme de pointeur */
  border-radius: 12px; /* Bordures arrondies */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); /* Ombre portée */
  transition: background-color 0.3s, box-shadow 0.3s; /* Transitions pour les effets */
}

.savepr:hover {
  background-color: #45a049; /* Couleur de fond au survol */
  box-shadow: 0 8px 10px rgba(0, 0, 0, 0.4); /* Ombre accentuée au survol */
}

.savepr:active {
  background-color: #3e8e41; /* Couleur de fond au clic */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Réduit l'ombre au clic */
  transform: translateY(2px); /* Déplace légèrement le bouton vers le bas au clic */
}





/*centrer le button request(rendezvous) */
.button-container {
        text-align: center;
        margin-top: 20px;
}
   .informations{
    display: flex;
    flex-direction: column;
    font-family: Georgia, 'Times New Roman', Times, serif;
}
i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 80px;
    transition: 0.7s;
    color: #fff;
}
.pro{
    margin-left: 120%:
}
    form {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        fieldset {
            border: none;
            margin-bottom: 20px;
        }
        legend {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input, select, textarea {
            width: 90%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
    background-color: rgba(128, 128, 128, 0.5); /* Gris avec 50% de transparence */
    border: 2px solid black; /* Bordure noire de 2 pixels */
  
}

        
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1em;
            padding: 15px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .number {
            background: #4CAF50;
            color: white;
            height: 30px;
            width: 30px;
            display: inline-block;
            font-size: 0.8em;
            margin-right: 10px;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
        }




        .table-container {
            max-width: 100%;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        th:first-child, td:first-child {
            border-left: none;
        }
        th:last-child, td:last-child {
            border-right: none;
        }
        th, td {
            border-top: none;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #333;
        }






        .notification {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
        }
        .notification .icon {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 50%;
            margin-right: 20px;
            font-size: 20px;
        }
        .notification .content {
            flex: 1;
        }
        .notification .content .date {
            font-size: 14px;
            color: #777;
        }
        .notification .content .sujet {
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        .notification .content .doctor {
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="navbar">
    <ul class="navbarsections">
      <li class="navbarsection">
        <a href="#home" class="nav-link">
          <img src="home.png" alt="" class="ai-home">
          <span>Home</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#monmedecin" class="nav-link">
          <img src="patient.png" alt="" class="ai-image">
          <span>Mon medecin</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#notifications" class="nav-link">
          <img src="exam.png" alt="" class="ai-file">
          <span>Notifications</span>
        </a>
      </li>
      
      <li class="navbarsection">
        <a href="#rendezvous" class="nav-link">
          <img src="rendezvous.png" alt="" class="ai-book-open">
          <span>Rendez-vous</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#dossiermedical" class="nav-link">
          <img src="man.png" alt="" class="ai-bell">
          <span>Dossier medical</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#profile" class="nav-link">
          <img src="profilemedecin.png" alt="" class="ai-person">
          <span>Profile</span>
        </a>
      </li>
      <a class="nav-link" href="#" onclick="document.getElementById('logout-form').submit();">
        <img src="switch.png" alt="" class="ai-game-controller">
        <span>Logout</span>
        </a>
    </ul>
  </div>
  <form id="logout-form" method="post" style="display: none;">
        <input type="hidden" name="logout" value="1">
    </form>
  <div id="home" class="section active"></div>
    <div id="monmedecin" class="section ">
        <div class="monmdc">


                <?php if ($error_message): ?>
                <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
                <?php endif; ?>
    <form class="edit-form" id="editForm">
    <div class="pro">
    <div class="profile-info">
        <img src="doctor (2).png" alt="Profile Picture" class="cercle">
        <div>
            <h3><?= htmlspecialchars($medecinInfo['name']) ?> <?= htmlspecialchars($medecinInfo['familyName']) ?></h3>
            <p>Medecin | Hope Care</p>
            <p>Email: <?= htmlspecialchars($medecinInfo['email']) ?></p>
        </div>
    </div>
    </div>
        <h3 class="textarea"><?= htmlspecialchars($medecinInfo['name']) ?></h3>
        <input type="text" name="familyNamemdc" placeholder="Family Name" id="familyName" value="<?= htmlspecialchars($medecinInfo['familyName']) ?>">
        <input type="text" name="emailmdc" placeholder="Email" id="email" value="<?= htmlspecialchars($medecinInfo['email']) ?>">
        <input type="text" name="phoneNumbermdc" placeholder="Phone" id="phone" value="<?= htmlspecialchars($medecinInfo['phoneNumber']) ?>">
        <input type="text" name="sexmdc" placeholder="Sex" id="sex" value="<?= htmlspecialchars($medecinInfo['sex']) ?>">
        <input type="text" name="address" placeholder="Address" id="address" value="<?= htmlspecialchars($medecinInfo['address']) ?>">
    </form>
  </div>

  <div id="messages" class="section">
  <div class="row clearf">
        <div class="col-lg-12">
            <div class="card chat-app">
                <div id="plist" class="people-list">
                    <ul class="contactlist">
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
                    $sqlchef = "SELECT id, name, familyName FROM chefservice";
                    $resultchef = $conn->query($sqlchef);
                    $sqlmdc = "SELECT id, name, familyName FROM medecins WHERE id!='$user_id'";
                    $resultmdc = $conn->query($sqlmdc);
                    $sqlinf = "SELECT id, name, familyName FROM infirmiers";
                    $resultinf = $conn->query($sqlinf);
                    $sqlpat = "SELECT id, name, familyName FROM patient";
                    $resultpat = $conn->query($sqlpat);
                    if ($resultmdc === false) {
                        die("Erreur lors de la récupération des médecins : " . $conn->error);
                    }
                    if ($resultinf === false) {
                      die("Erreur lors de la récupération des infirmiers : " . $conn->error);
                  }
                  if ($resultpat === false) {
                    die("Erreur lors de la récupération des patients : " . $conn->error);
                }
                if ($resultchef === false) {
                  die("Erreur lors de la récupération des médecins : " . $conn->error);
              }
echo"Chef service";
if ($resultchef->num_rows > 0) {
  while ($rowchef = $resultchef->fetch_assoc()) {
      echo "<li class='clearfix'>";
      echo "<img src='doctor.png' alt='avatar'>";
      echo "<span class='name'>" . htmlspecialchars($rowchef["name"]) . " " . htmlspecialchars($rowchef["familyName"]) . "</span>";
      echo "<form method='post'>";
      echo "<input type='hidden' name='reciverid' value='" . $rowchef["id"] . "'>";
      echo "<input type='hidden' name='recivername' value='" . $rowchef["name"] . "'>";
      echo "<button type='submit' name='discuter' class='delete-button'>Discuter</button>";
      echo "</form>";
      echo "</li>";
  }
} else {
  echo "<p>Chef service n'est pas trouvé.</p>";
}

                    if ($resultmdc->num_rows > 0) {
                        while ($rowmdc = $resultmdc->fetch_assoc()) {
                            echo "<li class='clearfix'>";
                            echo "<img src='doctor.png' alt='avatar'>";
                            echo "<span class='name'>" . htmlspecialchars($rowmdc["name"]) . " " . htmlspecialchars($rowmdc["familyName"]) . "</span>";
                            echo "<form method='post'>";
                            echo "<input type='hidden' name='reciverid' value='" . $rowmdc["id"] . "'>";
                            echo "<input type='hidden' name='recivername' value='" . $rowmdc["name"] . "'>";
                            echo "<button type='submit' name='discuter' class='delete-button'>Discuter</button>";
                            echo "</form>";
                            echo "</li>";
                        }
                    } else {
                        echo "<p>Aucun médecin trouvé.</p>";
                    }
                    echo"Infirmiers";
                    if ($resultinf->num_rows > 0) {
                      while ($rowinf = $resultinf->fetch_assoc()) {
                          echo "<li class='clearfix'>";
                          echo "<img src='doctor.png' alt='avatar'>";
                          echo "<span class='name'>" . htmlspecialchars($rowinf["name"]) . " " . htmlspecialchars($rowinf["familyName"]) . "</span>";
                          echo "<form method='post'>";
                          echo "<input type='hidden' name='reciverid' value='" . $rowinf["id"] . "'>";
                          echo "<input type='hidden' name='recivername' value='" . $rowinf["name"] . "'>";
                          echo "<button type='submit' name='discuter' class='delete-button'>Discuter</button>";
                          echo "</form>";
                          echo "</li>";
                      }
                  } else {
                      echo "<p>Aucun infirmier trouvé.</p>";
                  }
                  echo"Patients";
                    if ($resultpat->num_rows > 0) {
                      while ($rowpat = $resultpat->fetch_assoc()) {
                          echo "<li class='clearfix'>";
                          echo "<img src='doctor.png' alt='avatar'>";
                          echo "<span class='name'>" . htmlspecialchars($rowpat["name"]) . " " . htmlspecialchars($rowpat["familyName"]) . "</span>";
                          echo "<form method='post'>";
                          echo "<input type='hidden' name='reciverid' value='" . $rowpat["id"] . "'>";
                          echo "<input type='hidden' name='recivername' value='" . $rowpat["name"] . "'>";
                          echo "<button type='submit' name='discuter' class='delete-button'>Discuter</button>";
                          echo "</form>";
                          echo "</li>";
                      }
                  } else {
                      echo "<p>Aucun infirmier trouvé.</p>";
                  }
                
                    ?>
                    </ul>
                </div>
                <div class="chat">
                    <div class="chat-header clearfix">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="chat-about">
                                    <h1 class="m-b-0"><?php echo htmlspecialchars($recivername); ?></h1>
                                    <small>Last seen: 2 hours ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history">
                        <ul class="m-b-0">
                        <?php
                        if (!empty($reciverid)) {
                            $sql = "SELECT * FROM messages WHERE (sender = ? AND reciver = ?) OR (sender = ? AND reciver = ?) ORDER BY timestamp";
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                die("Erreur de préparation de la requête : " . $conn->error);
                            }
                            $stmt->bind_param("ssss", $user_id, $reciverid, $reciverid, $user_id);
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
                        <input type="hidden" name="reciverid" value="<?php echo htmlspecialchars($reciverid); ?>">
                        <textarea class="form-control" name="nouveau_message" placeholder="Entrez votre message ici..." required></textarea>
                        <button type="submit" name="envoyer" class="delete-button">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $conn->close(); ?>
    </div>
  </div>
  <div id="notifications" class="section">
  <?php
    if ($resultmesrendezvous->num_rows > 0) {
        while ($rowmesrendezvous = $resultmesrendezvous->fetch_assoc()) {
            echo "<div class='notification'>";
            echo "<div class='icon'>📅</div>";
            echo "<div class='content'>";
            echo "<div class='date'>" . htmlspecialchars($rowmesrendezvous["date"]) . "</div>";
            echo "<div class='sujet'>" . htmlspecialchars($rowmesrendezvous["sujet"]) . "</div>";
            echo "<div class='doctor'>Dr: " . htmlspecialchars($medecinInfo["name"]) . " " . htmlspecialchars($medecinInfo["familyName"]) ."</div>";
            echo "</div>"; 
            echo "</div>";
        }
    } else {
        echo "<p>Il n'y a pas de rendez-vous.</p>";
    }
    ?>
</div>
  <div id="dossiermedical" class="section">
  <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Température</th>
                    <th>Fréquence Cardiaque</th>
                    <th>Pression Artérielle</th>
                    <th>Symptômes</th>
                    <th>Médicaments Administrés</th>
                    <th>Alimentation</th>
                    <th>Activité Physique</th>
                    <th>Hydratation</th>
                    <th>Douleur</th>
                    <th>Observations Supplémentaires</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($formulairesInfirmier)): ?>
                    <?php foreach ($formulairesInfirmier as $formulaire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($formulaire['Date']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['Temperature']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['FrequenceCardiaque']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['PressionArterielle']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['Symptomes']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['MedicamentsAdministres']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['Alimentation']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['ActivitePhysique']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['Hydratation']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['Douleur']); ?></td>
                            <td><?php echo htmlspecialchars($formulaire['ObservationsSupplementaires']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13">Aucune information trouvée pour ce patient.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
  </div>
  <div id="rendezvous" class="section">
  <div id="body_header">
    <form method="post">
        <fieldset>
            <legend><span class="number">1</span>Your basic details</legend>
            <label for="name">Name :</label>
            <input type="text" id="name" name="namerdv" placeholder="Your name" required pattern="[a-zA-Z0-9]+">
            <label for="mail">Email</label>
            <input type="email" id="mail" name="emailrdv" placeholder="abc@xyz.com" required>
            <label for="tel">Contact Num:</label>
            <input type="tel" id="tel" placeholder="Include country code" name="numrdv">
        </fieldset>
        <fieldset>
            <legend><span class="number">2</span>Appointment Details</legend>
            <label for="appointment_for">Appointment for:</label>
            <select id="appointment_for" name="appointment_for" required>
                <?php
                $conn = new mysqli($servername, $username, $password, $database);
                $pat = $patient['id'];
                if ($conn->connect_error) {
                    die("La connexion à la base de données a échoué : " . $conn->connect_error);
                }
                $sql = "SELECT * FROM medecins m JOIN dossiermedical dm ON m.id=dm.idmedecin WHERE dm.idpatient='$pat'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rdvmd = $row["id"];
                        echo "<option value='$rdvmd'>" . $row["name"] . " " . $row["familyName"] . "</option>";
                    }
                } else {
                    echo "<option value=''>There's no doctors.</option>";
                }
                $conn->close();
                ?>
            </select>
            <label for="appointment_description">Appointment Description:</label>
            <textarea id="appointment_description" name="appointment_description" placeholder="write here"></textarea>
            <label for="date">Date:</label>
            <input type="date" name="date" required>
            <br>
            <label for="time">Time:</label>
            <input type="time" name="time" required>
            <br>
        </fieldset>
        <div class="button-container">
            <button type="submit" name="rendezvousbtn" class="savepr">Request</button>
        </div>
      </form>

      <div class="alert error" id="error-message">
        <?php if (!empty($error_message)) echo $error_message; ?>
      </div>
      <div class="alert success" id="success-message">
        <?php if (!empty($success_message)) echo $success_message; ?>
      </div>
    </div>
  </div>
  <div id="profile" class="section">
    
    <?php if ($error_message): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    <form class="edit-form" id="editForm" method="post">
    <div class="pro">
    <div class="profile-info">
        <img src="prppp.png" alt="Profile Picture" class="cercle">
        <div>
            <h3><?= htmlspecialchars($patient['name']) ?> <?= htmlspecialchars($patient['familyName']) ?></h3>
            <p>Patient | Hope Care</p>
            <p>Email: <?= htmlspecialchars($patient['email']) ?></p>
        </div>
    </div>
    </div>
        <input type="text" name="name" placeholder="Full Name" id="fullName" value="<?= htmlspecialchars($patient['name']) ?>">
        <input type="text" name="familyName" placeholder="Family Name" id="familyName" value="<?= htmlspecialchars($patient['familyName']) ?>">
        <input type="text" name="email" placeholder="Email" id="email" value="<?= htmlspecialchars($patient['email']) ?>">
        <input type="text" name="phoneNumber" placeholder="Phone" id="phone" value="<?= htmlspecialchars($patient['phoneNumber']) ?>">
        <input type="text" name="sex" placeholder="Sex" id="sex" value="<?= htmlspecialchars($patient['sex']) ?>">
        <input type="text" name="address" placeholder="Address" id="address" value="<?= htmlspecialchars($patient['address']) ?>">
        <button type="submit" name="updateprofile" class="savepr">Save</button>
    </form>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const navLinks = document.querySelectorAll('.nav-link');
      const sections = document.querySelectorAll('.section');

      navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
          event.preventDefault();

          const targetSection = document.querySelector(link.getAttribute('href'));

          sections.forEach(section => {
            section.classList.remove('active');
          });

          navLinks.forEach(link => {
            link.classList.remove('active');
          });

          link.classList.add('active');
          targetSection.classList.add('active');
        });
      });
 var successMessage = document.getElementById('success-message');
            var errorMessage = document.getElementById('error-message');

            if (successMessage && successMessage.textContent.trim() !== '') {
                successMessage.style.display = 'block';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 2000);
            }

            if (errorMessage && errorMessage.textContent.trim() !== '') {
                errorMessage.style.display = 'block';
                setTimeout(function() {
                    errorMessage.style.display = 'none';
                }, 2000);
            }

            var form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                setTimeout(function() {
                    form.submit();
                }, 50);
            });


    });
  </script>
</body>
</html>