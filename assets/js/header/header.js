// Gestion du Menu Burger pour la navigation mobile
document.addEventListener('DOMContentLoaded', function () {
    const burger = document.querySelector('.burger-menu');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeMenu = document.querySelector('.close-menu');

    // Ouverture du menu mobile
    burger.addEventListener('click', function () {
        mobileMenu.classList.toggle('open');
    });

    // Fermeture du menu mobile
    closeMenu.addEventListener('click', function () {
        mobileMenu.classList.remove('open');
    });
});

// Gestion du Dropdown dans le header
document.addEventListener('click', function (event) {
    const dropdown = document.querySelector('.dropdown-content');
    const isDropdownButton = event.target.closest('#connexion-button');

    // Ferme le dropdown si on clique en dehors
    if (!isDropdownButton && !event.target.closest('.dropdown-content')) {
        dropdown.classList.remove('dropdown-content--active');
    }

    // Ouvre/ferme le dropdown
    if (isDropdownButton) {
        dropdown.classList.toggle('dropdown-content--active');
    }
});

document.addEventListener("DOMContentLoaded", function() {
    const mobileConnexionButton = document.getElementById("mobile-connexion-button");
    const mobileDropdownContent = document.getElementById("mobile-dropdown-content");

    // Gestion du clic sur le bouton de connexion mobile
    mobileConnexionButton.addEventListener("click", function() {
        mobileDropdownContent.classList.toggle("dropdown-content--active"); // Toggle la classe pour afficher/cacher le dropdown
    });

    // Fermer le dropdown si l'utilisateur clique en dehors
    window.addEventListener("click", function(event) {
        if (!mobileConnexionButton.contains(event.target) && !mobileDropdownContent.contains(event.target)) {
            mobileDropdownContent.classList.remove("dropdown-content--active");
        }
    });
});

