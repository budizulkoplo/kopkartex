<x-app-layout>
    <x-slot name="pagetitle">Dashboard</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="app-content">
        <div class="container-fluid my-4">
            <h2 class="mb-4">📊 Dashboard Penjualan</h2>

            <div class="row g-4 mt-2">
                <!-- Chart Penjualan per Bulan -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            Penjualan per Bulan
                        </div>
                        <div class="card-body">
                            <canvas id="chartBulanan"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Daftar Ambil Pesanan -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white d-flex align-items-center pt-1 pb-1">
                            <h6 class="mb-0">Daftar Ambil Pesanan Hari Ini</h6>
                            <a href="/ambilbarang" class="btn btn-sm btn-warning ms-auto">Ambil Pesanan</a>
                        </div>
                        <div class="card-body p-2">

                            <div class="table-responsive">
                                <table id="tbdatatable" class="table table-sm table-bordered mb-0" style="font-size: small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Bayar</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pesananTerbaru as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->nomor_invoice ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</td>
                                            <td>{{ $p->customer ?? '-' }}</td>
                                            <td>{{ number_format($p->grandtotal, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $p->status_ambil == 'finish' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($p->status_ambil ?? '-') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada pesanan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <!-- Chart Metode Bayar -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            Metode Pembayaran
                        </div>
                        <div class="card-body">
                            <canvas id="chartMetode"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart Status Transaksi -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            Status Transaksi
                        </div>
                        <div class="card-body">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <!-- Chart Top Barang -->
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            Top 10 Barang Stok Terbanyak
                        </div>
                        <div class="card-body">
                            <canvas id="chartTopBarang"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS -->
    <x-slot name="csscustom">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </x-slot>

    <!-- Custom JS -->
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // --- Dummy Data (diganti dari backend Laravel jika perlu) ---
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

            // Data dari Backend (Top Barang)
            const topBarangLabels = @json($topBarang->pluck('nama_barang'));
            const topBarangData = @json($topBarang->pluck('total_stok'));

            // --- Chart Top 10 Barang ---
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

        <script>
            function loadPesanan() {
                fetch("{{ url('/admin/pesanan-hari-ini') }}")
                    .then(res => res.text())
                    .then(html => {
                        document.querySelector("#tbdatatable tbody").innerHTML = html;
                    });
            }

            setInterval(loadPesanan, 10000); // refresh tiap 10 detik
        </script>
    </x-slot>
</x-app-layout>
