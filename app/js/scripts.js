// scripts.js
document.addEventListener('DOMContentLoaded', (event) => {
    const rulesButton = document.getElementById('rulesButton');
    const rulesPopup = document.getElementById('rulesPopup');
    const closeButton = document.querySelector('.close');

    rulesButton.onclick = function() {
        rulesPopup.classList.remove('hidden');
    }

    closeButton.onclick = function() {
        rulesPopup.classList.add('hidden');
    }

    window.onclick = function(event) {
        if (event.target == rulesPopup) {
            rulesPopup.classList.add('hidden');
        }
    }
});