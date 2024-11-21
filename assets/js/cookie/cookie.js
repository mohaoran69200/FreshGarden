// Je récupère l'élément de la pop-up et les boutons nécessaires pour l'interaction
const popup = document.getElementById('popup');
const acceptBtn = document.getElementById('accept-btn');
const closeBtn = document.getElementById('close-btn');

// Je vérifie si une préférence de cookies existe déjà dans le localStorage
if (!localStorage.getItem('cookiesAccepted')) {
    // Si aucune préférence n'est trouvée, je vais afficher la pop-up
    popup.style.display = 'flex';
}

// Je mets en place un écouteur d'événement pour le bouton "Accepter"
// Lorsque l'utilisateur clique sur "Accepter", je ferme la pop-up et je sauvegarde la préférence dans le localStorage
acceptBtn.addEventListener('click', function () {
    popup.style.display = 'none';
    localStorage.setItem('cookiesAccepted', 'true');
    console.log('Cookies acceptés');
});

// Je mets en place un autre écouteur d'événement pour le bouton "Fermer"
// Lorsque l'utilisateur clique sur "Fermer", je ferme la pop-up et je sauvegarde la préférence comme "false"
closeBtn.addEventListener('click', function () {
    popup.style.display = 'none';
    localStorage.setItem('cookiesAccepted', 'false');
    console.log('Pop-up fermée');
});
