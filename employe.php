<?php
require_once __DIR__.'/auth.php';
require_role('EMPLOYE');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation de voiture</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-400 via-blue-300 to-purple-200 min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Header -->
    <header class="w-full max-w-3xl bg-white/90 backdrop-blur-sm rounded-xl shadow-lg p-4 flex justify-between items-center mb-5">
        <h1 class="text-2xl font-extrabold text-indigo-700">Réservation de voiture</h1>
    <a href="logout.php" 
       class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
       Déconnexion
    </a>
</header>

    <!-- Contenu principal -->
    <main class="w-full max-w-3xl bg-white/90 rounded-2xl shadow-xl p-7">

        <!-- Messages flash -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800 font-semibold">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800 font-semibold">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" action="mes_reservations.php" onsubmit="return verifierDates(event)" class="grid grid-cols-1 gap-6">

            <!-- Chauffeur -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Chauffeur</label>
                <select name="chauffeur" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                  <option value="avec" selected>Avec chauffeur</option>
                  <option value="sans">Sans chauffeur</option>
                </select>
                <!-- Icône -->
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>

            <!-- Type de trajet -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Type de trajet</label>
                <select name="trajet" id="trajet" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                  <option value="aller_simple" selected>Aller simple</option>
                  <option value="aller_retour">Aller-retour</option>
                </select>
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
            </div>

            <!-- Nombre de personnes -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Nombre de personnes</label>
                <input type="number" name="nb_personnes"  min="1" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 11V7a5 5 0 10-10 0v4a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/></svg>
                </span>
            </div>

            <!-- Lieu de départ -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Lieu de départ</label>
                <input type="text" name="depart"  required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414A6 6 0 1112.414 13.414l4.243 4.243z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 19l-1.414-1.414"/></svg>
                </span>
            </div>

            <!-- Lieu d'arrivée -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Lieu d'arrivée</label>
                <input type="text" name="arrivee" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414A6 6 0 1112.414 13.414l4.243 4.243z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 19l-1.414-1.414"/></svg>
                </span>
            </div>

            <!-- Date départ -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Date départ</label>
                <input type="date" name="date_depart"  required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>

            <!-- Heure départ -->
            <div class="relative">
                <label class="block font-medium text-gray-700 mb-1">Heure départ</label>
                <input type="text" name="heure_depart" pattern="^([01]\d|2[0-3]):([0-5]\d)$" placeholder="HH:MM"  required title="Entrez une heure au format 24h (ex: 17:30)" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>
                </span>
            </div>

            <!-- Date retour (optionnel, affiché seulement si aller-retour) -->
            <div class="relative" id="date_retour_group" style="display:none;">
                <label class="block font-medium text-gray-700 mb-1">Date retour</label>
                <input type="date" name="date_retour"  id="date_retour"
                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>

            <!-- Heure retour (optionnel, affiché seulement si aller-retour) -->
            <div class="relative" id="heure_retour_group" style="display:none;">
                <label class="block font-medium text-gray-700 mb-1">Heure retour</label>
                <input type="text" name="heure_retour" pattern="^([01]\d|2[0-3]):([0-5]\d)$" placeholder="HH:MM"  id="heure_retour"
                    title="Entrez une heure au format 24h (ex: 17:30)" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 transition">
                <span class="absolute left-3 top-9 text-indigo-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>
                </span>
            </div>

            <!-- Bouton -->
            <button type="submit" class="mt-3 w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-extrabold py-3 rounded-xl transition-all duration-200 shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-300 text-lg">
                Réserver
            </button>
        </form>
    </main>

    <script>
    // Affiche/masque les champs retour selon le type de trajet
    function updateRetourFields() {
        const trajet = document.getElementById('trajet').value;
        const dateRetourGroup = document.getElementById('date_retour_group');
        const heureRetourGroup = document.getElementById('heure_retour_group');
        if (trajet === 'aller_retour') {
            dateRetourGroup.style.display = '';
            heureRetourGroup.style.display = '';
        } else {
            dateRetourGroup.style.display = 'none';
            heureRetourGroup.style.display = 'none';
        }
    }
    document.getElementById('trajet').addEventListener('change', updateRetourFields);

    // Initialise le champ à l'affichage
    updateRetourFields();
    </script>
</body>
</html>