<script>
function initDaysCalculation(scope = document) {
    scope.querySelectorAll('input[type="date"][data-output]').forEach(input => {
        const outputId = input.getAttribute('data-output');
        const output = scope.getElementById ? scope.getElementById(outputId) : document.getElementById(outputId);
        if (!output) return;

        const update = () => {
            if (!input.value) {
                output.textContent = '';
                return;
            }
            const date = new Date(input.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            date.setHours(0, 0, 0, 0); // normalize to midnight

            if (!isNaN(date)) {
                const diff = Math.round((date - today) / (1000 * 60 * 60 * 24));

                if (diff === 0) {
                    output.textContent = 'Today';
                } else if (diff > 0) {
                    output.textContent = `${diff} day(s) from today`;
                } else {
                    output.textContent = `${Math.abs(diff)} day(s) ago`;
                }
            } else {
                output.textContent = '';
            }
        };

        input.removeEventListener('input', update); // prevent duplicate bindings
        input.addEventListener('input', update);
        update(); // run once for pre-filled values
    });
}
</script>
