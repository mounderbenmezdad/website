<?php
session_start();
if (isset($_POST['current_section'])) {
    $_SESSION['current_section'] = $_POST['current_section'];
}
?>
