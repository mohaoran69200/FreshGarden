document.addEventListener('DOMContentLoaded', () => {
    // Je sélectionne les éléments du DOM nécessaires pour l'adresse, le code postal et la ville
    const addressInput = document.querySelector('[data-action=address-input]');
    const postalCodeInput = document.querySelector('[data-action=postal-code-input]');
    const cityInput = document.querySelector('[data-action=city-input]');
    const dropdownContainer = document.querySelector('#dropdown-menu');
    const dropdownResults = document.querySelector('#dropdown-results');

    // Je crée un élément pour afficher un message si aucun résultat n'est trouvé
    const noResult = document.createElement('li');
    noResult.className = 'no-result';
    noResult.innerText = 'Aucun résultat';

    // Je mets en place un événement "keyup" sur l'input de l'adresse pour déclencher la recherche
    addressInput.addEventListener('keyup', debounce(function (e) {
        const value = e.target.value.trim();

        // Je vérifie que la valeur saisie est suffisante pour lancer une recherche (min 3 caractères et max 200)
        if (value.length < 3 || value.length > 200) {
            dropdownContainer.classList.add('hidden');
            dropdownResults.innerHTML = '';
            return;
        }

        // Je fais une requête à l'API pour obtenir les résultats de l'adresse
        fetch(`https://api-adresse.data.gouv.fr/search/?q=${value}&limit=5`)
            .then(response => response.json())
            .then(data => {
                const features = data.features;

                // Je rends le conteneur des résultats visible
                dropdownContainer.classList.remove('hidden');
                dropdownResults.innerHTML = '';

                // Je vérifie s'il y a des résultats à afficher
                if (features && features.length > 0) {
                    // Je parcours chaque résultat et je les affiche sous forme de liste déroulante
                    features.forEach(feature => {
                        const li = document.createElement('li');
                        li.className = 'dropdown-item';
                        li.innerText = feature.properties.label;

                        // Je mets en place un événement "click" pour remplir les champs avec l'adresse sélectionnée
                        li.addEventListener('click', () => {
                            addressInput.value = feature.properties.name;
                            postalCodeInput.value = feature.properties.postcode;
                            cityInput.value = feature.properties.city || feature.properties.context.split(', ')[0];

                            dropdownContainer.classList.add('hidden');
                            dropdownResults.innerHTML = '';
                        });

                        dropdownResults.appendChild(li);
                    });
                } else {
                    dropdownResults.appendChild(noResult);
                }
            })
            .catch(() => {
                // Je gère l'erreur si l'API ne répond pas
                alert('Erreur API, veuillez réessayer !');
            });

        // Je ferme les résultats de la recherche si l'utilisateur clique ailleurs
        document.addEventListener('click', function (event) {
            if (!addressInput.contains(event.target)) {
                dropdownContainer.classList.add('hidden');
                dropdownResults.innerHTML = '';
            }
        });
    }, 500));
});

// Fonction debounce pour limiter les requêtes API en cas de frappe rapide
function debounce(callback, delay) {
    let timer;
    return function () {
        const args = arguments;
        clearTimeout(timer);
        timer = setTimeout(() => {
            callback.apply(this, args);
        }, delay);
    };
}
