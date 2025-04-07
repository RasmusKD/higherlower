document.addEventListener('DOMContentLoaded', () => {
    const rulesButton = document.getElementById('rulesButton');
    const rulesPopup = document.getElementById('rulesPopup');
    const closeButton = document.getElementById('closeRules');

    if (rulesButton && rulesPopup && closeButton) {
        rulesButton.addEventListener('click', () => {
            rulesPopup.classList.remove('hidden');
        });

        closeButton.addEventListener('click', () => {
            rulesPopup.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === rulesPopup) {
                rulesPopup.classList.add('hidden');
            }
        });
    }
});
