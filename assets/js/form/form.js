// Je crée une fonction qui permet de basculer l'affichage d'un formulaire et de changer l'icône de flèche
window.toggleForm = function(formId, arrowId) {
    // Je récupère l'élément du formulaire et de l'icône de la flèche en utilisant les identifiants passés en paramètre
    const form = document.getElementById(formId);
    const arrow = document.getElementById(arrowId);

    // Je vérifie si le formulaire est actuellement caché (display: none)
    if (form.style.display === "none") {
        // Si le formulaire est caché, je le rends visible et je change l'icône de la flèche en "-"
        form.style.display = "block";
        arrow.innerHTML = "-";
    } else {
        // Si le formulaire est déjà visible, je le cache et je change l'icône de la flèche en "+"
        form.style.display = "none";
        arrow.innerHTML = "+";
    }
};
