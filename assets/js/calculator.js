/* assets/js/calculator.js - Interactive Tile Calculator Engine */

document.addEventListener('DOMContentLoaded', function() {
    const calcForm = document.getElementById('tile-calc-form');
    if (!calcForm) return;

    function runCalculation() {
        const length = parseFloat(document.getElementById('calc-length').value) || 0;
        const width = parseFloat(document.getElementById('calc-width').value) || 0;
        const unit = document.getElementById('calc-unit').value || 'feet';
        const wastage = parseFloat(document.getElementById('calc-wastage').value) || 10;
        const coveragePerBox = parseFloat(document.getElementById('calc-coverage-per-box')?.value || 15.5);

        if (length <= 0 || width <= 0) return;

        let lengthFt = length;
        let widthFt = width;

        if (unit === 'meter' || unit === 'm') {
            lengthFt = length * 3.28084;
            widthFt = width * 3.28084;
        } else if (unit === 'inch' || unit === 'in') {
            lengthFt = length / 12;
            widthFt = width / 12;
        }

        const rawArea = lengthFt * widthFt;
        const wastageArea = rawArea * (wastage / 100);
        const totalAreaReq = rawArea + wastageArea;
        const boxesReq = Math.ceil(totalAreaReq / coveragePerBox);
        const actualCoverage = boxesReq * coveragePerBox;

        // Update DOM
        document.getElementById('res-raw-area').textContent = rawArea.toFixed(2) + ' sq. ft.';
        document.getElementById('res-wastage-area').textContent = wastageArea.toFixed(2) + ' sq. ft.';
        document.getElementById('res-total-area').textContent = totalAreaReq.toFixed(2) + ' sq. ft.';
        document.getElementById('res-boxes-needed').textContent = boxesReq + ' Boxes';
        document.getElementById('res-actual-coverage').textContent = actualCoverage.toFixed(2) + ' sq. ft.';
    }

    calcForm.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', runCalculation);
        el.addEventListener('change', runCalculation);
    });

    calcForm.addEventListener('submit', function(e) {
        e.preventDefault();
        runCalculation();
    });

    runCalculation();
});
