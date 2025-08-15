<?php
session_start();

// Vérifie que l'utilisateur est connecté
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit;
    }
}

// Vérifie le rôle
function require_role($role) {
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
}
