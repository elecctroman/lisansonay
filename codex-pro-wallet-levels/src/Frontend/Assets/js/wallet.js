(function(){
    function renderCharts(){
        if(!window.codexProAnalytics){
            return;
        }
        const labels = codexProAnalytics.data.map(item => item.date);
        const orders = codexProAnalytics.data.map(item => item.orders_count);
        const loaded = codexProAnalytics.data.map(item => item.balance_loaded);
        const spent = codexProAnalytics.data.map(item => item.balance_spent);

        const ctxOrders = document.getElementById('codex-wallet-orders');
        const ctxLoaded = document.getElementById('codex-wallet-loaded');
        const ctxSpent = document.getElementById('codex-wallet-spent');

        if (!ctxOrders || !ctxLoaded || !ctxSpent) {
            return;
        }

        new Chart(ctxOrders, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: orders,
                    backgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(ctxLoaded, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Loaded',
                    data: loaded,
                    borderColor: '#10b981',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        new Chart(ctxSpent, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Spent',
                    data: spent,
                    borderColor: '#ef4444',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    document.addEventListener('DOMContentLoaded', renderCharts);
})();
