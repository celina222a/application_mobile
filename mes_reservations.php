<?php
require_once __DIR__.'/auth.php';
require_role('EMPLOYE');
require_once __DIR__.'/config.php';

$userId       = $_SESSION['user_id'];
$chauffeur    = $_POST['chauffeur'] ?? '';
$trajet       = $_POST['trajet'] ?? '';
$nbPersonnes  = (int)($_POST['nb_personnes'] ?? 0);
$depart       = $_POST['depart'] ?? '';
$arrivee      = $_POST['arrivee'] ?? '';
$dateDebut    = $_POST['date_depart'].' '.$_POST['heure_depart'];
$dateFin      = (!empty($_POST['date_retour']) && !empty($_POST['heure_retour']))
                  ? $_POST['date_retour'].' '.$_POST['heure_retour'] : null;


$stmt = $conn->prepare("INSERT INTO reservations 
(user_id, chauffeur, trajet, nb_personnes, depart, arrivee, date_depart, date_retour, statut)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
$stmt->bind_param("ississss", $userId, $chauffeur, $trajet, $nbPersonnes, $depart, $arrivee, $dateDebut, $dateFin);


if ($stmt->execute()) {
    $_SESSION['flash_success'] = "✅ Votre demande a été bien enregistrée.";
} else {
    $_SESSION['flash_success'] = "❌ Votre demande n’a pas été acceptée.  ".$stmt->error;
}
header("Location: employe.php");
exit;
