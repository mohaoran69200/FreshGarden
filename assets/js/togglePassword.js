document.addEventListener("DOMContentLoaded", function() {
    const passwordInputs = document.querySelectorAll('.password-input');
    const togglePasswordLabels = document.querySelectorAll('.toggle-password');

    togglePasswordLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            const input = passwordInputs[index];
            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                label.textContent = '👁️‍🗨️'; // Changer l'icône pour masquer
            } else {
                input.setAttribute('type', 'password');
                label.textContent = '👁️'; // Changer l'icône pour afficher
            }
        });
    });
});
