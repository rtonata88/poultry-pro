import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Title, Tooltip, Legend, Filler } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Title, Tooltip, Legend, Filler);

window.initEggProductionChart = function(chartData) {
    const ctx = document.getElementById('eggProductionChart');
    if (!ctx) {
        console.error('Chart canvas not found');
        return;
    }

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#52525b',
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const eggs = context.parsed.y.toLocaleString();
                            const productionRate = context.dataset.productionRates[context.dataIndex];
                            return context.dataset.label + ': ' + eggs + ' eggs (' + productionRate + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#71717a',
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    }
                },
                x: {
                    ticks: {
                        color: '#71717a',
                        maxRotation: 45,
                        minRotation: 45,
                    },
                    grid: {
                        display: false,
                    }
                }
            }
        }
    });
};
