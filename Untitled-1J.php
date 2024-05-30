<?php
$conn = new mysqli($servername, $username, $password, $database);
$user_id = $patient['id'];
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}
$mesrendezvous = "SELECT * FROM rendezvous WHERE patientid='$user_id' AND sender != '$user_id'";
$resultmesrendezvous = $conn->query($mesrendezvous);
if ($resultmesrendezvous->num_rows > 0) {
    while ($rowmesrendezvous = $resultmesrendezvous->fetch_assoc()) {
      echo"hi";
      echo "<span class='id'> " . htmlspecialchars($rowmesrendezvous["id"]) . "</span>";
      echo "<span class='doctor'> " . htmlspecialchars($rowmesrendezvous["doctorid"]) . "</span>";
      echo "<span class='date'> " . htmlspecialchars($rowmesrendezvous["date"]) . "</span>";
      echo "<span class='sujet'> " . htmlspecialchars($rowmesrendezvous["sujet"]) . "</span>";
    }
}
else{ 
 echo"Il n'y a pas de rendez-vous."; 
 echo"$user_id";
} ?>































    