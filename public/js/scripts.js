// scripts.js
document.addEventListener('DOMContentLoaded', (event) => {
    const rulesButton = document.getElementById('rulesButton');
    const rulesPopup = document.getElementById('rulesPopup');
    const closeButton = document.querySelector('.close');
    const rulesContent = document.querySelector('#rulesPopup .content');

    const rulesText = `
        <h2 class="text-2xl font-bold mb-4">Regler</h2>
        <p>Her er reglerne for brug af denne side...</p>
        <ul class="list-disc pl-5">
            <li>Regel 1: Beskrivelse af regel 1.</li>
            <li>Regel 2: Beskrivelse af regel 2.</li>
            <li>Regel 3: Beskrivelse af regel 3.</li>
        </ul>
    `;

    rulesButton.onclick = function() {
        rulesContent.innerHTML = rulesText;
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