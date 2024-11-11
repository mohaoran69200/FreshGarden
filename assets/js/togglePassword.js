document.addEventListener("DOMContentLoaded", function() {
    const passwordInputs = document.querySelectorAll('.password-input');
    const togglePasswordLabels = document.querySelectorAll('.toggle-password');

    togglePasswordLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            const input = passwordInputs[index];
            const isPasswordType = input.getAttribute('type') === 'password';

            // Je change le type de champ
            input.setAttribute('type', isPasswordType ? 'text' : 'password');

            // Je change l'icône en fonction de l'état
            if (isPasswordType) {
                // Si le mot de passe est caché, changer à visible (œil barré)
                label.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mdp">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd"/>
                        <path d="M2.5 2.5l19 19" stroke="#218838" stroke-width="2" stroke-linecap="round"/>
                    </svg>`;
            } else {
                // Si le mot de passe est visible, changer à caché (œil non barré)
                label.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mdp">
                        <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd"/>
                    </svg>`;
            }
        });
    });
});
