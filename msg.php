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
      echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
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
                            echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
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
                          echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
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
                          echo "<form method='post' action='".$_SERVER['PHP_SELF']."#messages'>";
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
                    <form id="chat-form"class="chat-message clearfix" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>#messages" >
                        <input type="hidden" name="reciverid" value="<?php echo htmlspecialchars($reciverid); ?>">
                        <textarea class="form-control" name="nouveau_message" placeholder="Entrez votre message ici..." required></textarea>
                        <button type="submit" id="envoyerButton"name="envoyer" class="delete-button">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>