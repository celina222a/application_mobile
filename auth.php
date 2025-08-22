<?php
session_start();

/**
 * Vérifie que l'utilisateur est connecté
 */
function require_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: connexion.php");
        exit;
    }
}

/**
 * Vérifie que l'utilisateur a bien le rôle attendu
 */
function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        echo "⛔ Accès refusé. Vous n'avez pas les droits pour cette page.";
        exit;
    }
}

/**
 * Vérifie si l'utilisateur a l'un des rôles donnés (utile si plusieurs rôles peuvent accéder à la même page)
 */
function require_roles(array $roles) {
    require_login();
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        echo "⛔ Accès refusé. Vous n'avez pas les droits nécessaires.";
        exit;
    }
}
