document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('button1');

    if (button) {
        button.addEventListener('click', function () {
            if (typeof window.unityInstance !== 'undefined') {
                // Random color generate
                const randomColor = {
                    r: Math.random(),
                    g: Math.random(),
                    b: Math.random()
                };
                window.unityInstance.SendMessage('Cube', 'ChangeColor', JSON.stringify(randomColor));
            } else {
                console.error('Unity instance not found.');
            }
        });
    }
});
