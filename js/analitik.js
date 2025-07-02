document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.chartLabels === 'undefined' || typeof window.chartDataDana === 'undefined') return;
    const ctx = document.getElementById('grafikDana').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: window.chartLabels,
            datasets: [{
                label: 'Dana Terkumpul (Rp)',
                data: window.chartDataDana,
                backgroundColor: 'rgba(45, 124, 255, 0.6)',
                borderColor: 'rgba(45, 124, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
