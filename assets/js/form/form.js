window.toggleForm = function(formId, arrowId) {
    const form = document.getElementById(formId);
    const arrow = document.getElementById(arrowId);
    if (form.style.display === "none") {
        form.style.display = "block";
        arrow.innerHTML = "-"; // Change à '-' lorsque le formulaire est visible
    } else {
        form.style.display = "none";
        arrow.innerHTML = "+"; // Change à '+' lorsque le formulaire est masqué
    }
};
