
<?php
session_start(); 
$servername = "localhost";
$username = "root";
$password = "";
$database = "memoire";
// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);
$medecin = $_SESSION['medecins']; // Accès aux données de l'utilisateur médecin
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

if (!isset($_SESSION['medecins'])) {
  // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
  header("Location: log-in.html");
  exit(); // Terminer le script après la redirection
}

$user_id = $medecin['id']; // ID du médecin

$error_message = '';



function obtenirExamensPatient($servername, $username, $password, $database, $user_id) {
  // Connexion à la base de données
  $conn = new mysqli($servername, $username, $password, $database);

  // Vérification de la connexion
  if ($conn->connect_error) {
      die("La connexion à la base de données a échoué : " . $conn->connect_error);
  }

  // Requête SQL pour récupérer les informations des patients
  $exam = "SELECT p.id, p.name, p.familyName, p.bd, p.sex, p.address, p.phoneNumber FROM patient p JOIN patientsalle ps ON p.id = ps.id_patient WHERE ps.id_medecin = '$user_id' && ps.id_salle='EXAM'";
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
  else{$exam = "SELECT p.id, p.name, p.familyName, p.bd, p.sex, p.address, p.phoneNumber FROM patient p JOIN dossiermedical dm ON p.id = dm.idpatient LEFT JOIN FormulaireMedecin fm ON p.id = fm.IDPatient WHERE fm.DateExamen IS NOT NULL AND fm.DateExamen < DATE_SUB(NOW(), INTERVAL 3 MONTH)";
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
                WHERE dm.idmedecin = '$user_id' ";
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






$patients = obtenirExamensPatient($servername, $username, $password, $database, $user_id);
// Gestion de l'enregistrement d'un examen
$patientidexam=isset($_POST['$malade']) ? $_POST['malade'] : (isset($_GET['malade']) ? $_GET['malade'] : '');
$recivername = isset($_POST['recivername']) ? $_POST['recivername'] : (isset($_GET['recivername']) ? $_GET['recivername'] : '');
$reciverid = isset($_POST['reciverid']) ? $_POST['reciverid'] : (isset($_GET['reciverid']) ? $_GET['reciverid'] : '');
/// Insérer un nouveau message dans la base de données
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
  // Redirigez vers la même page avec l'ancre #messages
  header("Location: " . $_SERVER['PHP_SELF'] . "?medecinid=" . $user_id . "#messages");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rendezvousbtn'])) {
  $appointment_for = $_POST['appointment_for'];
  $appointment_description = $_POST['appointment_description'];
  $date = $_POST['date'];
  $time = $_POST['time'];
  $datetime = $date . ' ' . $time;

  $sql_insert = "INSERT INTO rendezvous (doctorid,patientid, date, sujet, sender) VALUES (?, ?, ?, ?, ?)";
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











$mesrendezvous = "SELECT * FROM rendezvous WHERE doctorid='$user_id' AND sender != '$user_id'";
$resultmesrendezvous = $conn->query($mesrendezvous);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enregistrerlexamen'])) {
  // Récupérer les valeurs du formulaire
  $idPatient = $_POST['malade'];
  $nomMedecinTraitant = $medecin['name'];
  $Poids = $_POST['poids'];
  $Taille = $_POST['taille'];
  $typeCancer = $_POST['typecancer'];
  $stadeCancer = $_POST['stadecancer'];
  $biomarqueursTumoraux = $_POST['biomarqueurstumoraux'];
  $biopsie = $_POST['biopsie'];
  $traitementsAnterieurs = $_POST['traitementsanterieurs'];
  $traitementActuel = $_POST['traitementactuel'];
  $effetsSecondaires = $_POST['effetssecondaires'];
  $symptomes = $_POST['symptomes'];
  $resultatsExamensMedicaux = $_POST['resultatsexamensmedicaux'];
  $evaluationEtatGeneral = $_POST['evaluationetatgeneral'];
  $planTraitementFutur = $_POST['plantraitementfutur'];
  $recommandationsProchainRdv = $_POST['recommandationsprochainrdv'];

  // Récupérer les données des médicaments
  $medicamentNames = isset($_POST['medicamentName']) ? $_POST['medicamentName'] : array();
  $dosages = isset($_POST['dosage']) ? $_POST['dosage'] : array();
  $frequences = isset($_POST['frequence']) ? $_POST['frequence'] : array();
  $durees = isset($_POST['duree']) ? $_POST['duree'] : array();
  $effetSecondaire = isset($_POST['effetSecondaire']) ? $_POST['effetSecondaire'] : array();

  // Assurer que les données des médicaments ont la même longueur pour obtenir le nombre de médicaments
  $nombreMedicaments = count($medicamentNames);

  // Connexion à la base de données
  $conn = new mysqli($servername, $username, $password, $database);
  // Vérification de la connexion
  if ($conn->connect_error) {
      die("La connexion à la base de données a échoué : " . $conn->connect_error);
  }

  // Préparation et exécution de l'insertion dans FormulaireMedecin
  $stmt = $conn->prepare("INSERT INTO FormulaireMedecin (IDPatient, NomMedecinTraitant, TypeCancer, StadeCancer, BiomarqueursTumoraux, Biopsie, TraitementsAnterieurs, TraitementActuel, EffetsSecondaires, Symptomes, ResultatsExamensMedicaux, EvaluationEtatGeneral, PlanTraitementFutur, RecommandationsProchainRdv, poids, taille) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  if ($stmt === false) {
      die("Erreur de préparation de la requête : " . $conn->error);
  }

  $stmt->bind_param("ssssssssssssssss", $idPatient, $nomMedecinTraitant, $typeCancer, $stadeCancer, $biomarqueursTumoraux, $biopsie, $traitementsAnterieurs, $traitementActuel, $effetsSecondaires, $symptomes, $resultatsExamensMedicaux, $evaluationEtatGeneral, $planTraitementFutur, $recommandationsProchainRdv, $Poids, $Taille);

  if ($stmt->execute()) {
      $examen_id = $conn->insert_id;
      echo "Les informations ont été insérées avec succès dans la table FormulaireMedecin.";
  } else {
      echo "Erreur lors de l'insertion des informations dans la table FormulaireMedecin : " . $stmt->error;
  }
  $stmt->close();

  // Mise à jour des valeurs des attributs antecedents et stade dans DossierMedical
  $sql_update = "UPDATE dossiermedical SET antecedents = ?, stade = ? WHERE IDPatient = ?";
  $stmt_update = $conn->prepare($sql_update);
  if ($stmt_update === false) {
      die("Erreur de préparation de la requête de mise à jour : " . $conn->error);
  }
  $stmt_update->bind_param("sss", $traitementsAnterieurs, $stadeCancer, $idPatient);
  if ($stmt_update->execute()) {
      echo "Les valeurs des attributs antecedents et stade ont été mises à jour avec succès dans la table DossierMedical.";
  } else {
      echo "Erreur lors de la mise à jour des valeurs des attributs antecedents et stade dans la table DossierMedical : " . $stmt_update->error;
  }
  $stmt_update->close();

  // Insertion des médicaments
  $stmtMedicament = $conn->prepare("INSERT INTO medicament (examen_id, medicamentName, dosage, frequence, duree, effetSecondaire) VALUES (?, ?, ?, ?, ?, ?)");
  if ($stmtMedicament === false) {
      die("Erreur de préparation de la requête pour les médicaments : " . $conn->error);
  }

  for ($i = 0; $i < $nombreMedicaments; $i++) {
      // Liaison des paramètres pour les médicaments
      $stmtMedicament->bind_param("isssss", $examen_id, $medicamentNames[$i], $dosages[$i], $frequences[$i], $durees[$i], $effetSecondaire[$i]);
      if ($stmtMedicament->execute()) {
          echo "Médicament " . ($i + 1) . " enregistré avec succès.<br>";
      } else {
          echo "Erreur lors de l'enregistrement du médicament " . ($i + 1) . " : " . $stmtMedicament->error . "<br>";
      }
  }
  $stmtMedicament->close();

  // Gestion de la mise à jour des salles
  $selectQuery = "SELECT * FROM patientsalle WHERE id_salle='ATTEX1' ORDER BY id LIMIT 1";
  $resulttour = $conn->query($selectQuery);
  if ($resulttour->num_rows > 0) {
      $rowtour = $resulttour->fetch_assoc();
      $firstPatientId = $rowtour['id'];
      $updateQuery = "UPDATE patientsalle SET id_salle = 'EXAM' WHERE id = ?";
      $stmtUpdatePatient = $conn->prepare($updateQuery);
      if ($stmtUpdatePatient === false) {
          die("Erreur de préparation de la requête de mise à jour de la salle : " . $conn->error);
      }
      $stmtUpdatePatient->bind_param("i", $firstPatientId);
      if ($stmtUpdatePatient->execute()) {
          echo "Le premier patient a été mis à jour avec succès.";
      } else {
          echo "Erreur lors de la mise à jour du premier patient : " . $stmtUpdatePatient->error;
      }
      $stmtUpdatePatient->close();
  } else {
      $updateQuery = "UPDATE salles SET nbrpatients = 0 WHERE id = 'EXAM'";
      if ($conn->query($updateQuery) === TRUE) {
          echo "La salle est disponible";
      } else {
          echo "Erreur lors de la mise à jour de la salle : " . $conn->error;
      }
  }

  $updateQuerysoin = "UPDATE patientsalle SET id_salle = 'S1' WHERE id_patient = ?";
  $stmtUpdateSoin = $conn->prepare($updateQuerysoin);
  if ($stmtUpdateSoin === false) {
      die("Erreur de préparation de la requête de mise à jour de la salle de soins : " . $conn->error);
  }
  $stmtUpdateSoin->bind_param("s", $idPatient);
  if ($stmtUpdateSoin->execute()) {
      echo "Le patient est inséré à la salle des soins.";
  } else {
      echo "Erreur lors de l'insertion du patient à la salle des soins : " . $stmtUpdateSoin->error;
  }
  $stmtUpdateSoin->close();

  $conn->close();

  // Rediriger vers la même page pour éviter la réexécution des requêtes
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
  unset($_SESSION['medecins']);
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
/* Style pour les tableaux */
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




 

 
    .row {
      margin-right: 10%;
    }
    .sendmessageform{
      display: flex;
    justify-content: center; /* Alignement horizontal */
    align-items: center; /* Alignement vertical */
      height: 50px;
      align-items: center;
      text-align: center
    }
.voird{
background-color: transparent;
border: none;
}





/*rendezvous*/
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
        .rdvbtn{
          transition: transform 0.2s ease;
          border-radius: 50%;
          background-color: transparent;
          margin-left: 45%;
          margin-bottom: 10%;
        }
        .rdvbtn:hover {
  transform: scale(1.1); /* Grossit le bouton de 10% au survol */
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


    .card {
    background: #fff;
    transition: .5s;
    border: 0;
    margin-bottom: 30px;
    border-radius: .55rem;
    position: relative;
    width: 100%;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
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


.cercle{
border-radius: 50%;
border-color: transparent;
background-color: transparent;
}

#body_header
{

	width:auto;
	margin:0px auto;
	text-align:center;
	font-size:25px;
}

.envoyer{

  height: 100%;
}
h1 {
  margin: 0 0 30px 0;
  text-align: center;
}

input[type="text"],
input[type="password"],
input[type="date"],
input[type="datetime"],
input[type="email"],
input[type="number"],
input[type="search"],
input[type="tel"],
input[type="time"],
input[type="url"],
textarea,
select {
  background: rgba(255,255,255,0.1);
  border: none;
  font-size: 16px;
  height: auto;
  margin: auto;
  outline: 0;
  padding: 8px;
  width: 100%;
  background-color: #e8eeef;
  color: black;
  box-shadow: 0 1px 0 rgba(0,0,0,0.03) inset;
  margin-bottom: 30px;
}

input[type="radio"],
input[type="checkbox"]

{
  margin: 0 4px 8px 0;
}

select {
  padding: 6px;
  height: 32px;
  border-radius: 2px;
}



fieldset {
  margin-bottom: 30px;
  border: none;
}

legend {
  font-size: 1.4em;
  margin-bottom: 10px;
}

label {
  display: block;
  margin-bottom: 8px;
}

label.light {
  font-weight: 300;
  display: inline;
}

.number {
  background-color: #5fcf80;
  color: #fff;
  height: 30px;
  width: 30px;
  display: inline-block;
  font-size: 0.8em;
  margin-right: 4px;
  line-height: 30px;
  text-align: center;
  text-shadow: 0 1px 0 rgba(255,255,255,0.2);
  border-radius: 100%;
}
.form-control{
          width: 90%;
          height: 60%;
        background-color: transparent;
        }
        .sendbutton{
          width: 60%;
          height: 100%;
        }

  form { 
  width: 80%;  
  margin: 10px auto;
  padding: 10px 20px;
  background: #f4f7f8;
  border-radius: 8px;
  border: 1px solid #ccc; }
        label { display: block; margin-top: 10px; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        textarea { resize: vertical; }
        .form-section { margin-bottom: 20px; }
        .form-section h2 { margin-bottom: 10px; }
        .edit-form {
  align-items: center;
  text-align: center;
  background-color: rgba(255, 255, 255, 0.5); /* Gris avec 50% de transparence */
  border: 2px solid black; /* Bordure noire de 2 pixels */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Ombre */
}
input, select, textarea {
            width: 80%;
            margin-bottom: 10px;
            border-radius: 4px;
            height: 4%;
    background-color: rgba(128, 128, 128, 0.5); /* Gris avec 50% de transparence */
    border: 2px solid black; /* Bordure noire de 2 pixels */
  
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






  
        


/* Style amélioré pour la section de discussion */
.chat-app {
  display: flex;
  flex-direction: row;
  max-width: 1200px;
  margin: 0 auto;
}

.chat-app .people-list {
  background-color: #f9f9f9; /* Couleur de fond plus claire */
  border-right: 1px solid #ddd;
  overflow-y: auto;
  max-height: calc(100vh - 100px);
  flex: 1;
  padding: 15px; /* Marges légèrement réduites */

}

.chat-app .people-list ul {
  list-style-type: none;
  padding: 0;
}

.chat-app .people-list li {
  padding: 10px 0;
  border-bottom: 1px solid #eee; /* Bordures plus légères */
  transition: background-color 0.3s ease; /* Effet de transition */
}

.chat-app .people-list li:hover {
  background-color: #f0f0f0; /* Couleur de fond au survol */
}

.chat-app .people-list li img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.chat-app .people-list li .name {
  margin-left: 10px;
  font-size: 16px; /* Taille de police légèrement augmentée */
}

.chat-app .chat {
  flex: 3;
  display: flex;
  flex-direction: column;
  padding: 20px;
  background-color: #fff; /* Couleur de fond plus claire */
}

.chat-app .chat .chat-history {
  flex: 1;
  overflow-y: auto;
}

.chat-app .chat .chat-history ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.chat-app .chat .chat-history li {
  margin-bottom: 10px;
}

.chat-app .chat .chat-history .my-message {
  display: inline-block;
  max-width: 80%;
  padding: 10px;
  border-radius: 15px;
  background-color: #dcf8c6; /* Couleur de fond de votre message */
  align-self: flex-end; /* Alignement à droite */
  text-align: right; /* Alignement du texte à droite */
}

.chat-app .chat .chat-history .other-message {
  display: inline-block;
  max-width: 80%;
  padding: 10px;
  border-radius: 15px;
  background-color: #f0f0f0; /* Couleur de fond des autres messages */
  align-self: flex-start; /* Alignement à gauche */
  text-align: left; /* Alignement du texte à gauche */
}

.chat-app .chat .chat-history .message-content {
  word-wrap: break-word;
}

.chat-app .chat .chat-message {
  display: flex;
  margin-top: 20px;
}

.chat-app .chat .chat-message textarea {
  width: 80%;
  flex: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  resize: none;
  margin-right: 10px; /* Marge à droite pour le bouton */
}

.chat-app .chat .chat-message button {
  height: 60%%;
  background-color: #4CAF50; /* Couleur du bouton */
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease; /* Effet de transition */
}

.chat-app .chat .chat-message button:hover {
  background-color: #45a049; /* Couleur plus foncée au survol */
}






.patient-list {
    list-style: none;
    padding: 0;
    border-radius: 4px;
    align-items: center;
    text-align: center;
    background-color: rgba(255, 255, 255, 0.5); /* Gris avec 50% de transparence */
    border: 2px solid black; /* Bordure noire de 2 pixels */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Ombre */
    border: none; /* Pas de bordure par défaut */
    color: black; /* Texte en blanc */
    padding: 15px 32px; 
    text-align: center; 
    text-decoration: none; 
    display: inline-block; /* Affichage en ligne */
    font-size: 16px; 
    margin: 4px 2px; 
    cursor: pointer; 
    border-radius: 12px; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); 
  }

.patient-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
}

.patient-name {
    flex: 1;
    font-weight: bold;
}

.view-dossier-button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
}

.view-dossier-button:hover {
    background-color: #0056b3;
}

  </style><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <a href="#patients" class="nav-link">
          <img src="patient.png" alt="" class="ai-image">
          <span>Mes patients</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#examen" class="nav-link">
          <img src="exam.png" alt="" class="ai-file">
          <span>Exams</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#messages" class="nav-link">
          <img src="messages.png" alt="" class="ai-game-controller">
          <span>Messages</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#rendez_vous" class="nav-link">
          <img src="rendezvous.png" alt="" class="ai-book-open">
          <span>Rendez-vous</span>
        </a>
      </li>
      <li class="navbarsection">
        <a href="#notifications" class="nav-link">
          <img src="man.png" alt="" class="ai-bell">
          <span>Notifications</span>
        </a>
      </li>
      <li class="navbarsection">
      <a class="nav-link" href="#" onclick="document.getElementById('logout-form').submit();">
        <img src="switch.png" alt="" class="ai-game-controller">
        <span>Logout</span>
    </a>
      </li>
      <li class="navbarsection">
        <a href="#profile" class="nav-link">
          <img src="profilemedecin.png" alt="" class="ai-person">
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </div>

    <form id="logout-form" method="post" style="display: none;">
        <input type="hidden" name="logout" value="1">
    </form>
  <div id="home" class="section active">home</div>
  <div id="patients" class="section">
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

<div id="examen" class="section">
  <div id="partie1" class="part">
    <form class="exm" method="POST">
      <label>
        <select name="malade" id="malade" >
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
        if (!empty($patients)){
        foreach ($patients as $patient){   
                            $patientidexam=$patient['id'];
                            echo "<option value='$patientidexam'>" . $patient["name"] . " " . $patient["familyName"] . "</option>";  
                          }
        }
        else{
          echo"There is no exams";
        }
        ?>             
      </select>
                <label for="typecancer">Type de Cancer</label>
                <select id="typetancer" name="typecancer" required>
                    <option value="">Sélectionnez un type de cancer</option>
                    <option value="Cancer du sein">Cancer du sein</option>
                    <option value="Cancer du poumon">Cancer du poumon</option>
                    <option value="Cancer de la prostate">Cancer de la prostate</option>
                    <option value="Cancer colorectal">Cancer colorectal</option>
                    <option value="Cancer de la peau">Cancer de la peau</option>
                    <option value="Leucémie">Leucémie</option>
                    <option value="Lymphome">Lymphome</option>
                    <!-- Ajoutez d'autres types de cancer ici -->
                </select>
        
                <label for="stadecancer">Stade du Cancer</label>
                <select id="stadecancer" name="stadecancer" required>
                    <option value="">Sélectionnez un stade</option>
                    <option value="Stade I">Stade I</option>
                    <option value="Stade II">Stade II</option>
                    <option value="Stade III">Stade III</option>
                    <option value="Stade IV">Stade IV</option>
                    <!-- Ajoutez d'autres stades si nécessaire -->
                </select>
                <label for="biomarqueurstumoraux">Biomarqueurs Tumoraux</label>
                <textarea id="biomarqueurstumoraux" name="biomarqueurstumoraux" rows="3"></textarea>
      
              <label for="biopsie">Biopsie</label>
                <textarea id="biopsie" name="biopsie" rows="3"></textarea>
              <button type="button" onclick="afficherPartie(2)">Suivant</button>
              </form>
          </div>      
          <div id="partie2" class="part" style="display: none;">
              <form class="exm" method="POST">
            <label for="traitementsanterieurs">Traitements Antérieurs</label>
                <textarea id="traitementsanterieurs" name="traitementsanterieurs" rows="3"></textarea>
                <label for="traitementactuel">Traitement Actuel</label>
                <textarea id="traitementactuel" name="traitementactuel" rows="3"></textarea>
                <label for="effetssecondaires">Effets Secondaires</label>
                <textarea id="effetssecondaires" name="effetssecondaires" rows="3"></textarea>
                <label for="symptomes">Symptômes</label>
                <textarea id="symptomes" name="symptomes" rows="3"></textarea>
                <button type="button" onclick="afficherPartie(3)">Suivant</button>
                <button type="button" onclick="afficherPartie(1)">Retour</button>
            </form>  
          </div>
          <div id="partie3" class="part" style="display: none;">
            <form class="exm" method="POST"> 
                <label for="resultatsexamensmedicaux">Résultats des Examens Médicaux</label>
                <textarea id="resultatsexamensmedicaux" name="resultatsexamensmedicaux" rows="3"></textarea>
                <label for="evaluationetatgeneral">Évaluation de l'État Général</label>
                <textarea id="evaluationetatgeneral" name="evaluationetatgeneral" rows="3"></textarea>
                <label for="plantraitementfutur">Plan de Traitement Futur</label>
                <textarea id="plantraitementfutur" name="plantraitementfutur" rows="3"></textarea>
                <label for="recommandationsprochainrdv">Recommandations pour le Prochain Rendez-vous</label>
                <textarea id="recommandationsprochainrdv" name="recommandationsprochainrdv" rows="3"></textarea>
                <label for="poids">Poids (kg)</label>
                <input type="number" id="poids" name="poids" required>
                <label for="taille">Taille (cm)</label>
                <input type="number" id="taille" name="taille" required>
                <button type="button" onclick="afficherPartie(4)">Suivant</button>
                <button type="button" onclick="afficherPartie(2)">Retour</button>               
          
              </form>
            </div>    
          <div id="partie4" class="part" style="display: none;">
            <form class="exm" method="POST">        
                <h2>Informations Médicaments</h2>
                  <div id="medicaments">
                    <div class="medicament">
                      <label for="medicamentName">Nom du médicament :</label>
                      <input type="text" name="medicamentName[]" required>
                      <label for="dosage">Dosage :</label>
                      <input type="text" name="dosage[]" required>
                      <label for="frequence">Fréquence :</label>
                      <input type="text" name="frequence[]" required>
                      <label for="duree">Durée (jours) :</label>
                      <input type="number" name="duree[]" required>
                      <label for="remarques">Remarques :</label>
                      <input type="text" name="effetSecondaire[]">
                    </div>
                  </div>
                  <button type="button" onclick="ajouterMedicament()">Ajouter un médicament</button>
                  <button type="button" onclick="afficherPartie(3)">Retour</button>
                <input type="submit" name="enregistrerlexamen" value="Enregistrer le diagnostic">
                </form>
            </div>
</div>            
  <div id="messages" class="section">
  <div class="row">
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
      echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
      echo "<img src='doctor.png' alt='avatar'>";
      echo "<span class='name'>" . htmlspecialchars($rowchef["name"]) . " " . htmlspecialchars($rowchef["familyName"]) . "</span>";
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
                
                            echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
                            echo "<img src='doctor.png' alt='avatar'>";
                            echo "<span class='name'>" . htmlspecialchars($rowmdc["name"]) . " " . htmlspecialchars($rowmdc["familyName"]) . "</span>";
                            echo "<input type='hidden' name='reciverid' value='" . $rowmdc["id"] . "'>";
                            echo "<input type='hidden' name='recivername' value='" . $rowmdc["name"] . "'>";
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
                                echo "<li class='msgmesetautres'>";
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
                    <form id="chat-form"class="sendmessageform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>#messages" >
                        <input type="hidden" name="reciverid" value="<?php echo htmlspecialchars($reciverid); ?>">
                        <textarea class="form-control" name="nouveau_message" placeholder="Entrez votre message ici..." required></textarea>
                        <button type="submit" id="envoyerbutton"name="envoyer" class="cercle"> <img src="send.png"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    </div>


 <div id="rendez_vous" class="section">
    <div id="body_header">
        <!--This is a division tag for body header-->
        <h1>Appointment Request Form</h1>
    </div>
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
            
            <select id="$appointment_for" name="appointment_for" required>
                <?php
                session_start();   
                $servername = "localhost";
                $username = "root";
                $password = "";
                $database = "memoire";
                $conn = new mysqli($servername, $username, $password, $database);
                $mdc = $medecin['id'];
                if ($conn->connect_error) {
                    die("La connexion à la base de données a échoué : " . $conn->connect_error);
                }
                $sql = "SELECT * FROM patient p JOIN dossiermedical dm ON p.id=dm.idpatient WHERE dm.idmedecin='$mdc'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {  
                        $rdvmd = $row["id"];
                        echo "<option value='$rdvmd'>" . $row["name"] . " " . $row["familyName"] . "</option>";     
                    }
                } else {
                    echo "<li>There's no doctors.</li>";
                }
                $conn->close();
                ?>
            </select>
            <label for="appointment_description">Appointment Description:</label>
            <textarea id="appointment_description" name="appointment_description" placeholder="write here"></textarea>
            <label for="date">Date:</label>
            <input type="date" name="date" value="" required></input>
            <br>
            <label for="time">Time:</label>
            <input type="time" name="time" value="" required></input>
            <br>
        </fieldset>
        <button type="submit" name="rendezvousbtn" class="rdvbtn"><img src="date.png"></button>
    </form>
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
            echo "<div class='doctor'>Dr: " . htmlspecialchars($patientInfo["name"]) . " " . htmlspecialchars($patientInfo["familyName"]) ."</div>";
            echo "</div>"; 
            echo "</div>";
        }
    } else {
        echo "<p>Il n'y a pas de rendez-vous.</p>";
    }
    ?>
  </div>
  <div id="settings" class="section">settings</div>
  <div id="profile" class="section">
   <?php
// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

if ($error_message) {
    echo '<p style="color: red;">' . htmlspecialchars($error_message) . '</p>';
}
echo '<form class="edit-form" id="editForm" method="post">';
echo '<div class="pro">';
echo '<div class="profile-info">';
echo '<img src="prppp.png" alt="Profile Picture" class="cercle">';
echo '<div>';
echo '<h3>' . htmlspecialchars($medecin['name']) . ' ' . htmlspecialchars($medecin['familyName']) . '</h3>';
echo '<p>Medecin | Hope Care</p>';
echo '<p>Email: ' . htmlspecialchars($medecin['email']) . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '<input type="text" name="name" placeholder="Full Name" id="fullName" value="' . htmlspecialchars($medecin['name']) . '">';
echo '<input type="text" name="familyName" placeholder="Family Name" id="familyName" value="' . htmlspecialchars($medecin['familyName']) . '">';
echo '<input type="text" name="email" placeholder="Email" id="email" value="' . htmlspecialchars($medecin['email']) . '">';
echo '<input type="text" name="phoneNumber" placeholder="Phone" id="phone" value="' . htmlspecialchars($medecin['phoneNumber']) . '">';
echo '<input type="text" name="sex" placeholder="Sex" id="sex" value="' . htmlspecialchars($medecin['sex']) . '">';
echo '<input type="text" name="address" placeholder="Address" id="address" value="' . htmlspecialchars($medecin['address']) . '">';
echo '<button type="submit" name="updateprofile" class="savepr">Save</button>';
echo '</form>';

?>

  </div>
</body>

  <script>
    const editForm = document.getElementById('editForm');
    editForm.addEventListener('submit', function(event) {
        // Perform validation here if needed
    });
    function disableButton(event) {
        event.preventDefault(); // Empêcher le comportement par défaut du bouton
        setTimeout(function() {
            location.reload(); // Actualiser la page après un court délai
        }, 1000); // Délai en millisecondes avant l'actualisation
    }




    function afficherPartie(partie) {
    // Masquer toutes les parties
    document.getElementById('partie1').style.display = 'none';
    document.getElementById('partie2').style.display = 'none';
    document.getElementById('partie3').style.display = 'none';
    document.getElementById('partie4').style.display = 'none';
    // Afficher la partie demandée
    document.getElementById('partie' + partie).style.display = 'block';
  }

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



   
  
    function ajouterMedicament() {
            var divMedicaments = document.getElementById('medicaments');
            var divMedicament = document.createElement('div');
            divMedicament.className = 'medicament';
            divMedicament.innerHTML = `
                <label for="medicamentName">Nom du médicament :</label>
                <input type="text" name="medicamentName[]" required>
                <label for="dosage">Dosage :</label>
                <input type="text" name="dosage[]" required>
                <label for="frequence">Fréquence :</label>
                <input type="text" name="frequence[]" required>
                <label for="duree">Durée (jours) :</label>
                <input type="number" name="duree[]" required>
                <label for="remarques">Remarques :</label>
                <input type="text" name="remarques[]">
            `;
            divMedicaments.appendChild(divMedicament);
        }
  </script>
</body>
</html>