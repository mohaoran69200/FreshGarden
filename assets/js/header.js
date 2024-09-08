// Gestion du Menu Burger pour la navigation mobile
document.addEventListener('DOMContentLoaded', function() {
    const burger = document.querySelector('.burger-menu');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeMenu = document.querySelector('#close-menu');

    // Ouverture du menu mobile
    burger.addEventListener('click', function() {
        mobileMenu.classList.toggle('open');
    });

    // Fermeture du menu mobile
    closeMenu.addEventListener('click', function() {
        mobileMenu.classList.remove('open');
    });
});

// Sticky Header - fixe le header en haut lors du défilement
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    header.classList.toggle('header--sticky', window.scrollY > 50);
});

// Gestion du Dropdown dans le header
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.dropdown-content');
    const isDropdownButton = event.target.closest('#connexion-button');

    if (!isDropdownButton && !event.target.closest('.dropdown-content')) {
        dropdown.classList.remove('dropdown-content--active');
    }

    if (isDropdownButton) {
        dropdown.classList.toggle('dropdown-content--active');
    }
});
