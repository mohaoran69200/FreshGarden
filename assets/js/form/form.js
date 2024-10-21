// Fonction pour afficher/masquer les formulaires
    window.toggleForm = function(formId, arrowId) {
        const form = document.getElementById(formId);
        const arrow = document.getElementById(arrowId);
        if (form.style.display === "none") {
            form.style.display = "block";
            arrow.innerHTML = "▼"; // Change l'icône en bas
        } else {
            form.style.display = "none";
            arrow.innerHTML = "▶"; // Change l'icône en haut
        }
    };
