<?php
require_once __DIR__ . '/auth.php';
require_role('CHEF_PARC'); 
require_once __DIR__ . '/config.php';


$search = $_GET['search'] ?? '';
$filter_etat = $_GET['etat'] ?? '';

function etat_label($etat) {
    switch ($etat) {
        case 'new': return 'new';
        case 'accepted': return 'accepted';
        case 'cancelled': return 'cancelled';
        default: return htmlspecialchars($etat);
    }
}
function etat_class($etat) {
    switch ($etat) {
        case 'new': return 'bg-blue-100 text-blue-700';
        case 'accepted': return 'bg-green-100 text-green-700';
        case 'cancelled': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
}

$sql = "SELECT r.id, u.nom, r.chauffeur, r.trajet, r.nb_personnes, 
               r.depart, r.arrivee, r.date_depart, r.date_retour, r.etat
        FROM reservations r
        LEFT JOIN utilisateurs u ON r.user_id = u.id
        WHERE 1";

if ($search) {
    $search_sql = $conn->real_escape_string($search);
    $sql .= " AND (u.nom LIKE '%$search_sql%' OR r.trajet LIKE '%$search_sql%')";
}
if ($filter_etat) {
    $filter_sql = $conn->real_escape_string($filter_etat);
    $sql .= " AND r.etat = '$filter_sql'";
}
$sql .= " ORDER BY r.date_depart DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des réservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-tr from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
<div class="w-full max-w-md sm:max-w-2xl mx-auto my-2 p-2 sm:p-4 bg-white rounded-2xl shadow-lg">
    <h1 class="text-lg sm:text-2xl font-bold text-indigo-700 mb-1 sm:mb-2">Espace Chef de Parc</h1>
    <div class="flex justify-end mb-4">
    <a href="logout.php" 
       class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
       Déconnexion
    </a>
</div>

    <h2 class="text-base sm:text-lg font-semibold text-indigo-500 mb-3 sm:mb-6">Liste des réservations</h2>
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="mb-3 px-4 py-2 bg-green-100 text-green-700 rounded shadow text-center text-sm">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="mb-3 px-4 py-2 bg-red-100 text-red-700 rounded shadow text-center text-sm">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    <form method="get" class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3 mb-4 items-center">
        <input type="text" name="search" placeholder="Recherche employé ou trajet..." value="<?= htmlspecialchars($search) ?>"
            class="flex-1 w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:border-indigo-400 shadow-sm"/>
        <select name="etat" class="border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:border-indigo-400 shadow-sm w-full sm:w-auto">
            <option value="">Tous les états</option>
            <option value="new" <?= $filter_etat == 'new' ? 'selected' : '' ?>>new</option>
            <option value="accepted" <?= $filter_etat == 'accepted' ? 'selected' : '' ?>>accepted</option>
            <option value="cancelled" <?= $filter_etat == 'cancelled' ? 'selected' : '' ?>>cancelled</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 shadow transition w-full sm:w-auto">Filtrer</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200">
    <table id="reservations-table" class="min-w-[420px] w-full text-xs sm:text-sm divide-y divide-gray-200 rounded-xl bg-white">
        <thead class="bg-indigo-50">
            <tr>
                <th class="px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-indigo-700">Employé</th>
                <th class="px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-indigo-700">Trajet</th>
                <th class="px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-indigo-700">Départ</th>
                <th class="px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-indigo-700">Arrivée</th>
                <th class="px-2 py-2 sm:px-4 sm:py-3 text-center font-semibold text-indigo-700">État</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-indigo-50 transition cursor-pointer" data-id="<?= htmlspecialchars($row['id']); ?>">
                    <td class="px-2 py-2 sm:px-4 sm:py-2"><?= htmlspecialchars($row['nom'] ?? '—'); ?></td>
                    <td class="px-2 py-2 sm:px-4 sm:py-2"><?= htmlspecialchars($row['trajet']); ?></td>
                    <td class="px-2 py-2 sm:px-4 sm:py-2"><?= htmlspecialchars($row['depart']); ?></td>
                    <td class="px-2 py-2 sm:px-4 sm:py-2"><?= htmlspecialchars($row['arrivee']); ?></td>
                    <td class="px-2 py-2 sm:px-4 sm:py-2 text-center">
                        <span class="etat px-2 py-1 sm:px-3 sm:py-1 rounded-xl font-semibold <?= etat_class($row['etat']); ?> text-xs sm:text-base whitespace-nowrap">
                            <?= etat_label($row['etat']); ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center text-gray-500 py-8">Aucune réservation trouvée</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <p class="mt-4 text-gray-500 text-xs sm:text-sm text-center">Double-cliquez sur une ligne pour voir le détail de la réservation.</p>
</div>
<!-- Modale pour AJAX détail -->
<div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 hidden" id="modal-bg">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-[98vw] mx-1 p-3 sm:p-6 relative animate-fadeIn max-h-[80vh] overflow-y-auto">
        <button class="absolute top-2 right-3 text-2xl text-gray-500 hover:text-indigo-500" onclick="closeModal()">&times;</button>
        <div id="modal-content"></div>
    </div>
</div>
<script>
function closeModal() {
    document.getElementById('modal-bg').classList.add('hidden');
    document.getElementById('modal-content').innerHTML = '';
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr[data-id]').forEach(function(row) {
        row.addEventListener('dblclick', function() {
            var id = this.getAttribute('data-id');
            if (id) {
                fetch('reservation_detail.php?id=' + id + '&modal=1')
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('modal-content').innerHTML = html;
                        document.getElementById('modal-bg').classList.remove('hidden');
                    });
            }
        });
    });
    document.getElementById('modal-bg').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
});
</script>

<script>
    function test22() {
        const btnCancel = document.getElementById('btnCancel');
        if (btnCancel) {
            const motifWrap = document.getElementById('motifWrap');
            const motifInput = document.getElementById('motif');
            motifWrap.classList.add('active');
            motifInput.focus();
        }
    }toggleCancelButton

    function toggleCancelButton(textarea) {
        const confirmCancel = document.getElementById('confirmCancel');
        confirmCancel.disabled = (textarea.value.trim() === "");
    }

</script>

<style>
@keyframes fadeIn { from {opacity:0;transform:scale(0.98);} to {opacity:1;transform:scale(1);} }
.animate-fadeIn { animation: fadeIn .2s; }
</style>
</body>
</html>