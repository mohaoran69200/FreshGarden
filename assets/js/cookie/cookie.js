// Récupérer l'élément de la pop-up et les boutons
const popup = document.getElementById('popup');
const acceptBtn = document.getElementById('accept-btn');
const closeBtn = document.getElementById('close-btn');

// Vérifier si une préférence de cookies existe déjà
if (!localStorage.getItem('cookiesAccepted')) {
    // Si aucune préférence, afficher la pop-up
    popup.style.display = 'flex';
}

// Fermer la pop-up et sauvegarder la préférence lorsque "Accepter" est cliqué
acceptBtn.addEventListener('click', function () {
    popup.style.display = 'none'; // Cacher la pop-up
    localStorage.setItem('cookiesAccepted', 'true'); // Sauvegarder la préférence
    console.log('Cookies acceptés');
});

// Fermer la pop-up lorsque "Fermer" est cliqué
closeBtn.addEventListener('click', function () {
    popup.style.display = 'none'; // Cacher la pop-up
    localStorage.setItem('cookiesAccepted', 'false'); // Sauvegarder que l'utilisateur a fermé la pop-up
    console.log('Pop-up fermée');
});
