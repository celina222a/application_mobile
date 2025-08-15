function verifierDates(event) {
    const dateDepart = document.querySelector('[name="date_depart"]').value;
    const heureDepart = document.querySelector('[name="heure_depart"]').value;
    const dateRetour = document.querySelector('[name="date_retour"]').value;
    const heureRetour = document.querySelector('[name="heure_retour"]').value;

    if (dateRetour && heureRetour) {
        const depart = new Date(dateDepart + 'T' + heureDepart);
        const retour = new Date(dateRetour + 'T' + heureRetour);

        if (retour <= depart) {
            alert("⚠ La date et l'heure de retour doivent être après la date et l'heure de départ.");
            event.preventDefault();
            return false;
        }
    }
    return true;
}
function verifierHeures() {
    const regexHeure = /^([01]\d|2[0-3]):([0-5]\d)$/;
    const heureDepart = document.querySelector('[name="heure_depart"]').value;
    const heureRetour = document.querySelector('[name="heure_retour"]').value;

    if (!regexHeure.test(heureDepart)) {
        alert("⚠ Heure de départ invalide. Utilisez le format 24h (ex: 14:30).");
        return false;
    }

    if (heureRetour && !regexHeure.test(heureRetour)) {
        alert("⚠ Heure de retour invalide. Utilisez le format 24h (ex: 14:30).");
        return false;
    }
    return true;
}


