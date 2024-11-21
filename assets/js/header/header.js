// Gestion du Menu Burger pour la navigation mobile
document.addEventListener('DOMContentLoaded', function () {
    // Je récupère les éléments nécessaires pour le menu burger et le menu mobile
    const burger = document.querySelector('.burger-menu');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeMenu = document.querySelector('.close-menu');

    // Lorsque l'utilisateur clique sur le burger menu, je bascule l'état du menu mobile (l'ouvre ou le ferme)
    burger.addEventListener('click', function () {
        mobileMenu.classList.toggle('open');
    });

    // Lorsque l'utilisateur clique sur le bouton de fermeture, je ferme le menu mobile
    closeMenu.addEventListener('click', function () {
        mobileMenu.classList.remove('open');
    });
});

// Gestion du Dropdown dans le header
document.addEventListener('click', function (event) {
    // Je récupère l'élément du dropdown dans l'entête
    const dropdown = document.querySelector('.dropdown-content');
    // Je vérifie si l'utilisateur a cliqué sur le bouton du dropdown
    const isDropdownButton = event.target.closest('#connexion-button');

    // Si l'utilisateur clique en dehors du dropdown ou du bouton, je ferme le dropdown
    if (!isDropdownButton && !event.target.closest('.dropdown-content')) {
        dropdown.classList.remove('dropdown-content--active');
    }

    // Si l'utilisateur clique sur le bouton de connexion, je bascule l'état du dropdown (l'ouvre ou le ferme)
    if (isDropdownButton) {
        dropdown.classList.toggle('dropdown-content--active');
    }
});

// Gestion du Dropdown pour la connexion sur mobile
document.addEventListener("DOMContentLoaded", function() {
    // Je récupère les éléments nécessaires pour le bouton de connexion mobile et le contenu du dropdown
    const mobileConnexionButton = document.getElementById("mobile-connexion-button");
    const mobileDropdownContent = document.getElementById("mobile-dropdown-content");
    // Je ferme le dropdown si l'utilisateur clique en dehors de la zone du bouton ou du dropdown
    window.addEventListener("click", function(event) {
        if (!mobileConnexionButton.contains(event.target) && !mobileDropdownContent.contains(event.target)) {
            mobileDropdownContent.classList.remove("dropdown-content--active"); // Retire la classe qui rend le dropdown visible
        }
    });
});
