<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Sidebar
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('active');
        });

        // Jam Real-time
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', {hour12: false});
        }, 1000);

        // Diagram Absensi (Multi Bar)
        const ctx = document.getElementById('absensiDiagram').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum'],
                datasets: [
                    {
                        label: 'Hadir',
                        data: [45, 52, 48, 55, 40],
                        backgroundColor: '#2ecc71',
                        borderRadius: 6
                    },
                    {
                        label: 'Terlambat',
                        data: [5, 8, 12, 4, 15],
                        backgroundColor: '#f1c40f',
                        borderRadius: 6
                    },
                    {
                        label: 'Absen',
                        data: [10, 5, 4, 2, 6],
                        backgroundColor: '#e74c3c',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false, color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>
