<?php
session_start(); 
$servername = "localhost";
$username = "root";
$password = "";
$database = "memoire";

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['infirmiers'])) {
  // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
  header("Location: log-in.html");
  exit(); // Terminer le script après la redirection
}

$infirmier = $_SESSION['infirmiers']; // Accès aux données de l'utilisateur médecin
$user_id = $infirmier['id']; // ID du médecin

$error_message = '';

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enregistrerlediag'])) {
    // Récupérer les valeurs du formulaire
   // Connexion à la base de données
   $conn = new mysqli($servername, $username, $password, $database);
   // Vérification de la connexion
   if ($conn->connect_error) {
       die("La connexion à la base de données a échoué : " . $conn->connect_error);
   }

    $IDPatient = $_POST['malade'];
    $Temperature = $_POST['temperature'];
    $FrequenceCardiaque = $_POST['frequancecardiaque'];
    $PressionArterielle = $_POST['pressionarterielle'];
    $Symptomes = $_POST['symptomes'];
    $MedicamentsAdministres = $_POST['medicamentsadministres'];
    $Alimentation = $_POST['alimentation'];
    $Elimination = $_POST['elimination'];
    $ActivitePhysique = $_POST['activitephysique'];
    $Hydratation = $_POST['hydratation'];
    $Douleur = $_POST['douleur'];
    $ObservationsSupplementaires = $_POST['observationsupplementaires'];

   

    // Préparation de la requête
    $stmt = $conn->prepare("INSERT INTO FormulaireInfirmier (IDPatient, Temperature, FrequenceCardiaque, PressionArterielle, Symptomes, MedicamentsAdministres, Alimentation, Elimination, ActivitePhysique, Hydratation, Douleur, ObservationsSupplementaires) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    // Liaison des paramètres
    $stmt->bind_param("ssssssssssss", $IDPatient, $Temperature, $FrequenceCardiaque, $PressionArterielle, $Symptomes, $MedicamentsAdministres, $Alimentation, $Elimination, $ActivitePhysique, $Hydratation, $Douleur, $ObservationsSupplementaires);

    // Exécution de la requête
    if ($stmt->execute()) {
        echo "Les informations ont été insérées avec succès dans la table FormulaireInfirmier.";
    } else {
        echo "Erreur lors de l'insertion des informations dans la table FormulaireInfirmier : " . $stmt->error;
    }

    // Fermer la déclaration et la connexion
    $stmt->close();
    $conn->close();

    // Rediriger vers la même page pour éviter la réexécution des requêtes
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


    //modifierleprofile
if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['updateprofile']) ) {
    $name = $conn->real_escape_string($_POST['name']);
    $familyName = $conn->real_escape_string($_POST['familyName']);
    $email = $conn->real_escape_string($_POST['email']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Vérifiez si l'email est déjà pris par un autre utilisateur
    $sql_check_email = "SELECT id FROM infirmiers WHERE email = ? AND id != ?";
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
        $sql_check_phone = "SELECT id FROM infirmiers WHERE phoneNumber = ? AND id != ?";
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
            $sql_update = "UPDATE infirmiers SET 
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
            $infirmier['name'] = $name;
            $infirmier['familyName'] = $familyName;
            $infirmier['email'] = $email;
            $infirmier['phoneNumber'] = $phoneNumber;
            $infirmier['sex'] = $sex;
            $infirmier['address'] = $address;
  
            // Rediriger vers la page de profil ou afficher un message de succès
            header("Location: infirmier.php");
            exit;
        }
        $stmt_check_phone->close();
    }
    $stmt_check_email->close();
  }


  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['voirledossier'])) {
    // Votre code de traitement ici
    $patientformulaire = $_POST['voirledossier'];
  }
  
  
  
  function getpatientInfo($conn, $user_id) {
    $patientInfo = array();
    $monpatient = "SELECT * 
                 FROM patient p 
                 JOIN dossiermedical dm ON p.id = dm.idpatient 
                 LEFT JOIN rendezvous rv ON p.id = rv.sender 
                 WHERE dm.idmedecin = '$user_id'  AND rv.doctorid='$user_id'";
    $resultmonpatient = $conn->query($monpatient);
  
    if ($resultmonpatient->num_rows > 0) {
        $patientInfo = $resultmonpatient->fetch_assoc();
    }
    return $patientInfo;
  }
  $patientInfo = getpatientInfo($conn, $user_id);
  function getmypatient($conn, $user_id) {
    $mypatients = array();
    $mypatient = "SELECT * 
                  FROM patient p 
                  JOIN dossiermedical dm ON p.id = dm.idpatient 
                  WHERE dm.infirmierid = '$user_id' ";
    $resultmypatient = $conn->query($mypatient);
  
    if ($resultmypatient->num_rows > 0) {
        while($row = $resultmypatient->fetch_assoc()) {
            $mypatients[] = $row;
        }
    }
    return $mypatients;
  }
  
  $patientmy = getmypatient($conn, $user_id);
  $idmypatient = isset($_GET['idmypatient']) ? intval($_GET['idmypatient']) : 0;
  
  // Récupérer les informations médicales du patient
  function getPatientMedicalInfo($conn, $idmypatient) {
      $sql = "SELECT * FROM formulairemedecin WHERE idpatient = '$idmypatient'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          return $result->fetch_assoc();
      } else {
          return null;
      }
  }
  
  $mypatientInfo = getPatientMedicalInfo($conn, $idmypatient);
  
  
  









  function obtenirExamensPatient($servername, $username, $password, $database, $user_id) {
    // Connexion à la base de données
    $conn = new mysqli($servername, $username, $password, $database);
  
    // Vérification de la connexion
    if ($conn->connect_error) {
        die("La connexion à la base de données a échoué : " . $conn->connect_error);
    }
  
    // Requête SQL pour récupérer les informations des patients
    $exam = "SELECT p.id, p.name, p.familyName, p.bd, p.sex, p.address, p.phoneNumber FROM patient p JOIN patientsalle ps ON p.id = ps.id_patient JOIN dossiermedical dm ON p.id = dm.idpatient LEFT JOIN formulaireinfirmier fi ON p.id = fi.IDPatient WHERE dm.infirmierid = '$user_id'AND ps.id_salle='SOIN1' AND (fi.Date IS NULL OR fi.Date < DATE_SUB(NOW(), INTERVAL 24 HOUR))";
    $resultexam = $conn->query($exam);
    // Tableau pour stocker les informations des patients
    $patients = array();
    // Vérification du résultat de la requête
    if ($resultexam->num_rows > 0) {
        while ($rowexam = $resultexam->fetch_assoc()) {
            $patients[] = array(
                "id" => $rowexam["id"],
                "name" => $rowexam["name"],
                "familyName" => $rowexam["familyName"],
                "birthday" => $rowexam["bd"],
                "sex" => $rowexam["sex"],
                "address" => $rowexam["address"],
                "phoneNumber" => $rowexam["phoneNumber"]
            );
        }
    }
    else{
      $exam ="SELECT p.id, p.name, p.familyName, p.bd, p.sex, p.address, p.phoneNumber FROM patient p JOIN dossiermedical dm ON p.id = dm.idpatient LEFT JOIN FormulaireMedecin fm ON p.id = fm.IDPatient WHERE fm.Date IS NOT NULL AND fm.Date < DATE_SUB(NOW(), INTERVAL 3 MONTH)";
      $resultexam = $conn->query($exam);
      // Tableau pour stocker les informations des patients
      $patients = array();
      // Vérification du résultat de la requête
      if ($resultexam->num_rows > 0) {
          while ($rowexam = $resultexam->fetch_assoc()) {
              $patients[] = array(
                  "id" => $rowexam["id"],
                  "name" => $rowexam["name"],
                  "familyName" => $rowexam["familyName"],
                  "birthday" => $rowexam["bd"],
                  "sex" => $rowexam["sex"],
                  "address" => $rowexam["address"],
                  "phoneNumber" => $rowexam["phoneNumber"]
              );
          }
      }
    }
  
    // Fermeture de la connexion
    $conn->close();
    // Retourner les informations des patients
    return $patients;
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    unset($_SESSION['infirmiers']);
    header("Location: log-in.html"); // Redirige vers la page de connexion après la déconnexion
    exit();
}
 $conn->close();
?>
<!DOCTYPE html>
<html >
<head>
<style>
table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

thead {
  background-color: #f8f9fa; /* Couleur de fond de l'en-tête */
}


thead th {
  padding: 10px;
  text-align: left;
  border-bottom: 2px solid #dee2e6; /* Bordure inférieure de l'en-tête */
}
tr{
  background-color: rgba(204, 204, 204, 0.8);
}
tbody td {
  padding: 10px;
  border-bottom: 1px solid #dee2e6; /* Bordure inférieure des cellules */
}

/* Style pour les boutons */
button {
  padding: 8px 16px;
  border: none;
  background-color: #007bff; /* Couleur de fond des boutons */
  color: #fff;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #0056b3; /* Couleur de fond des boutons au survol */
}
.voird{
background-color: transparent;
border: none;
}

     textarea {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  /* Ajustez la largeur selon vos préférences */
  min-width: 200px;
}

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



    h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .form-group {
            margin-bottom: 15px;
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
        .edit-form {
  align-items: center;
  text-align: center;
  background-color: rgba(255, 255, 255, 0.5); /* Gris avec 50% de transparence */
  border: 2px solid black; /* Bordure noire de 2 pixels */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Ombre */
}









.form-container {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 800px;
    width: 100%;
}

.form-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #4CAF50;
}

.form-row {
    display: flex;
    justify-content: space-between;
}

.form-column {
    width: 48%;
}

.form-column label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-column input[type="text"],
.form-column input[type="number"],
.form-column textarea,
.form-column select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    background-color: rgba(255, 255, 255, 0.8);
}

.form-column textarea {
    height: 100px;
    resize: none;
}

.form-container .submit-button {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}

.form-container .submit-button:hover {
    background-color: #45a049;
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
        <a href="#mespatients" class="nav-link">
          <img src="patient.png" alt="" class="ai-image">
          <span>Mes patients</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#diagnostic" class="nav-link">
          <img src="messages.png" alt="" class="ai-game-controller">
          <span>exam</span>
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
  <div id="home" class="section active">home</div>
  <div id="mespatients" class="section">
    <?php if (!empty($patientmy)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patientmy as $patient): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($patient['name']); ?></td>
                        <td><?php echo htmlspecialchars($patient['familyName']); ?></td>
                        <td>
                            <form method="post" class="voird">
                                <button type="submit" name="idmypatient" value="<?php echo htmlspecialchars($patient['id']); ?>">Voir le dossier</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun patient trouvé.</p>
    <?php endif; ?>

    <?php if ($mypatientInfo): ?>
        <table>
            <tbody>
                <tr>
                    <td>ID Patient:</td>
                    <td><?php echo htmlspecialchars($mypatientInfo['IDPatient']); ?></td>
                </tr>
                <tr>
                    <td>Date Examen:</td>
                    <td><?php echo htmlspecialchars($mypatientInfo['DateExamen']); ?></td>
                </tr>
                <tr>
                    <td>Nom Médecin Traitant:</td>
                    <td><?php echo htmlspecialchars($mypatientInfo['NomMedecinTraitant']); ?></td>
                </tr>
                <!-- Ajoutez d'autres lignes pour les champs supplémentaires -->
                <tr>
                    <td>Poids:</td>
                    <td><?php echo htmlspecialchars($mypatientInfo['poids']); ?></td>
                </tr>
                <tr>
                    <td>Taille:</td>
                    <td><?php echo htmlspecialchars($mypatientInfo['taille']); ?></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun dossier médical trouvé pour ce patient.</p>
    <?php endif; ?>

  </div>

  <div id="diagnostic" class="section">
        <form method="POST" class="form-container">
            <h2>Formulaire de Diagnostic</h2>
            <div class="form-row">
                <div class="form-column">
                    <label for="malade">Patient :</label>
                    <select id="malade" name="malade">
                        <?php
                            session_start();   
                            $servername = "localhost";
                            $username = "root";
                            $password = "";
                            $database = "memoire";
                            $conn = new mysqli($servername, $username, $password, $database);
                            if ($conn->connect_error) {
                                die("La connexion à la base de données a échoué : " . $conn->connect_error);
                            }
                            $myid= $infirmier['id'];
                            $currentDateTime = date('Y-m-d H:i:s');
                            $twentyHoursAgo = date('Y-m-d H:i:s', strtotime('-20 hours', strtotime($currentDateTime)));
                            
                            $sql = "SELECT * FROM patient p 
                                    JOIN dossiermedical dm ON p.id=dm.idpatient 
                                    LEFT JOIN FormulaireInfirmier fi ON p.id = fi.IDPatient 
                                    WHERE dm.infirmierid='$myid' 
                                    AND (fi.date IS NULL OR fi.date < '$twentyHoursAgo')";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {  
                                    $rdvmd = $row["id"];
                                    echo "<option value='$rdvmd'>" . $row["name"] . " " . $row["familyName"] . "</option>";     
                                }
                            } else {
                                echo "<option value=''>Aucun patient disponible</option>";
                            }
                            $conn->close();
                        ?>
                    </select>

                    <label for="temperature">Température :</label>
                    <input type="text" id="temperature" name="temperature" required>

                   
                    <label for="elimination">Élimination :</label>
                    <textarea id="elimination" name="elimination" required></textarea>

                    <label for="activitephysique">Activité Physique :</label>
                    <textarea id="activitephysique" name="activitephysique" required></textarea>
                    <label for="symptomes">Symptômes :</label>
                    <textarea id="symptomes" name="symptomes" required></textarea>
                    <label for="medicamentsadministres">Médicaments Administrés :</label>
                    <textarea id="medicamentsadministres" name="medicamentsadministres" required></textarea>

                   
                  </div>


                <div class="form-column">


                <label for="frequancecardiaque">Fréquence Cardiaque :</label>
                    <input type="number" id="frequancecardiaque" name="frequancecardiaque" required>

                    <label for="pressionarterielle">Pression Artérielle :</label>
                    <input type="text" id="pressionarterielle" name="pressionarterielle" required>
                   

                    

                    <label for="hydratation">Hydratation :</label>
                    <textarea id="hydratation" name="hydratation" required></textarea>

                    <label for="douleur">Douleur :</label>
                    <textarea id="douleur" name="douleur" required></textarea>

                    <label for="observationsupplementaires">Observations Supplémentaires :</label>
                    <textarea id="observationsupplementaires" name="observationsupplementaires" required></textarea>
                    <label for="alimentation">Alimentation :</label>
                    <textarea id="alimentation" name="alimentation" required></textarea>
                  </div>
            </div>

            <input type="submit" name="enregistrerlediag" value="Enregistrer le diagnostic" class="submit-button">
        </form>
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
            <h3><?= htmlspecialchars($infirmier['name']) ?> <?= htmlspecialchars($infirmier['familyName']) ?></h3>
            <p>Infirmier | Hope Care</p>
            <p>Email: <?= htmlspecialchars($infirmier['email']) ?></p>
        </div>
    </div>
    </div>
        <input type="text" name="name" placeholder="Full Name" id="fullName" value="<?= htmlspecialchars($infirmier['name']) ?>">
        <input type="text" name="familyName" placeholder="Family Name" id="familyName" value="<?= htmlspecialchars($infirmier['familyName']) ?>">
        <input type="text" name="email" placeholder="Email" id="email" value="<?= htmlspecialchars($infirmier['email']) ?>">
        <input type="text" name="phoneNumber" placeholder="Phone" id="phone" value="<?= htmlspecialchars($infirmier['phoneNumber']) ?>">
        <input type="text" name="sex" placeholder="Sex" id="sex" value="<?= htmlspecialchars($infirmier['sex']) ?>">
        <input type="text" name="address" placeholder="Address" id="address" value="<?= htmlspecialchars($infirmier['address']) ?>">
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
    });
</script>
</body>
</html>
