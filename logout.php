<?php
session_start();
session_unset();
session_destroy();

// Rediriger vers la page de connexion
header("Location: connexion.php");
exit;