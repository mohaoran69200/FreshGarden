document.addEventListener("DOMContentLoaded", function() {
    // Récupérer l'élément de la pop-up et les boutons
    const popup = document.getElementById('popup');
    const acceptBtn = document.getElementById('accept-btn');
    const closeBtn = document.getElementById('close-btn');

    // Afficher la pop-up après que la page soit chargée
    popup.style.display = 'flex';

    // Fermer la pop-up lorsque le bouton "Accepter" est cliqué
    acceptBtn.addEventListener('click', function() {
        popup.style.display = 'none'; // Cacher la pop-up
        console.log('Cookies acceptés');
    });

    // Fermer la pop-up lorsque le bouton "Fermer" est cliqué
    closeBtn.addEventListener('click', function() {
        popup.style.display = 'none'; // Cacher la pop-up
        console.log('Pop-up fermée');
    });
});
