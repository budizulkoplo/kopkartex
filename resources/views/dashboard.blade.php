<x-app-layout>
    <x-slot name="pagetitle">Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="app-content"> <!--begin::Container-->

        <div class="container-fluid my-4">
        <h2 class="mb-4">📊 Dashboard Penjualan</h2>
        <div class="row g-4">
            <!-- Chart Penjualan per Bulan -->
            <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Penjualan per Bulan</div>
                <div class="card-body">
                <canvas id="chartBulanan"></canvas>
                </div>
            </div>
            </div>
        </div>
        <div class="row g-4">
            <!-- Chart Metode Bayar -->
            <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Metode Pembayaran</div>
                <div class="card-body">
                <canvas id="chartMetode"></canvas>
                </div>
            </div>
            </div>
            <!-- Chart Status Transaksi -->
            <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">Status Transaksi</div>
                <div class="card-body">
                <canvas id="chartStatus"></canvas>
                </div>
            </div>
            </div>
        </div>
        </div>

         <div class="row g-4">
            <!-- Chart Top Barang -->
            <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">Top 10 Barang Stok Terbanyak</div>
                <div class="card-body">
                <canvas id="chartTopBarang"></canvas>
                </div>
            </div>
            </div>
        </div>


    </div>
    <x-slot name="csscustom">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </x-slot>
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // --- Dummy Data (ini bisa diganti dari backend Laravel) ---
            const bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"];
            const totalBulanan = [12000000, 15000000, 10000000, 17000000, 20000000, 13000000];

            const metodeLabels = ["Tunai", "Potong Gaji", "Cicilan"];
            const metodeData = [40, 25, 15];

            const statusLabels = ["Lunas", "Hutang", "Pending", "Batal"];
            const statusData = [50, 10, 5, 3];

            // --- Chart Penjualan Bulanan ---
            new Chart(document.getElementById("chartBulanan"), {
                type: "bar",
                data: {
                labels: bulan,
                datasets: [{
                    label: "Total Penjualan (Rp)",
                    data: totalBulanan,
                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
                },
                options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
                }
            });

            // --- Chart Metode Bayar ---
            new Chart(document.getElementById("chartMetode"), {
                type: "doughnut",
                data: {
                labels: metodeLabels,
                datasets: [{
                    data: metodeData,
                    backgroundColor: ["#36A2EB", "#4BC0C0", "#FF6384"]
                }]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" }
                }
                }
            });

            // --- Chart Status Transaksi ---
            new Chart(document.getElementById("chartStatus"), {
                type: "pie",
                data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: ["#4CAF50", "#FF9800", "#FFC107", "#F44336"]
                }]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" }
                }
                }
            });


            const topBarangLabels = @json($topBarang->pluck('nama_barang'));
            const topBarangData = @json($topBarang->pluck('total_stok'));


            // Chart Top 10 Barang
            new Chart(document.getElementById("chartTopBarang"), {
                type: "bar",
                data: {
                labels: topBarangLabels,
                datasets: [{
                    label: "Jumlah Stok",
                    data: topBarangData,
                    backgroundColor: "rgba(75, 192, 192, 0.6)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1
                }]
                },
                options: {
                indexAxis: "y",
                scales: { x: { beginAtZero: true } }
                }
            });
            </script>
    </x-slot>
</x-app-layout>
