document.addEventListener('DOMContentLoaded', function() {
    // Ambil data penjualan dan buat chart
    fetch('../api/admin/orders.php?action=sales_data')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: data.values,
                        borderColor: '#6f4e37',
                        backgroundColor: 'rgba(111,78,55,0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        })
        .catch(err => console.error('Gagal memuat chart', err));
});