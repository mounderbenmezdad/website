document.addEventListener('DOMContentLoaded', function() {
    const toggleForm = document.getElementById('toggleForm');
    const loginForm = document.querySelector('.login');
    const signupForm = document.querySelector('.signup');

    toggleForm.addEventListener('click', function(event) {
        event.preventDefault(); 
        loginForm.classList.toggle('hidden');
        signupForm.classList.toggle('hidden');

        // Modifier le texte du lien en fonction de l'état du formulaire
        if (loginForm.classList.contains('hidden')) {
            toggleForm.textContent = 'Log-in';
        } else {
            toggleForm.textContent = 'Sign-up';
        }
    });
});

function togglePasswordVisibility() {
    var mdp = document.getElementById("mdp");
    var eyeShowIcon = document.getElementById("eyeShowIcon");
    var eyeHideIcon = document.getElementById("eyeHideIcon");

    if (mdp.type === "password") {
        mdp.type = "text";
        eyeShowIcon.style.display = "none";
        eyeHideIcon.style.display = "inline-block";
    } else {
        mdp.type = "password";
        eyeShowIcon.style.display = "inline-block";
        eyeHideIcon.style.display = "none";
    }
}
