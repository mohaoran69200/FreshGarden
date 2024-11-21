document.addEventListener("DOMContentLoaded", function() {
    // Je sélectionne tous les champs de mot de passe et les étiquettes de toggle (icônes)
    const passwordInputs = document.querySelectorAll('.password-input');
    const togglePasswordLabels = document.querySelectorAll('.toggle-password');

    // Je parcours toutes les étiquettes pour leur ajouter un gestionnaire d'événement
    togglePasswordLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            // Je récupère l'élément input correspondant à l'étiquette cliquée
            const input = passwordInputs[index];
            // Je vérifie si l'input est de type 'password' (mot de passe masqué)
            const isPasswordType = input.getAttribute('type') === 'password';

            // Je change le type de champ entre 'password' et 'text' en fonction de son état
            input.setAttribute('type', isPasswordType ? 'text' : 'password');

            // Je change l'icône en fonction de l'état du champ (mot de passe caché ou visible)
            if (isPasswordType) {
                // Si le mot de passe est caché, je remplace l'icône par un œil barré (visible)
                label.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mdp">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd"/>
                        <path d="M2.5 2.5l19 19" stroke="#218838" stroke-width="2" stroke-linecap="round"/>
                    </svg>`;
            } else {
                // Si le mot de passe est visible, je remplace l'icône par un œil normal (caché)
                label.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mdp">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd"/>
                    </svg>`;
            }
        });
    });
});
