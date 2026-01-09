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
                            Penjualan per Bulan (6 Bulan Terakhir)
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
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Metode Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pesananTerbaru as $index => $pesanan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $pesanan->nomor_invoice }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pesanan->tanggal)->format('d-m-Y') }}</td>
                                            <td>{{ $pesanan->customer }}</td>
                                            <td>Rp {{ number_format($pesanan->grandtotal, 0, ',', '.') }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match($pesanan->status) {
                                                        'lunas' => 'success',
                                                        'pending' => 'warning',
                                                        'batal' => 'danger',
                                                        'hutang' => 'secondary',
                                                        default => 'info'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($pesanan->status) }}</span>
                                            </td>
                                            <td>{{ ucfirst($pesanan->metode_bayar) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($pesananTerbaru->isEmpty())
                                <p class="text-center text-muted my-3">Tidak ada pesanan hari ini</p>
                                @endif
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
                            <div class="mt-3">
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($metode as $key => $jumlah)
                                        <tr>
                                            <td>{{ ucfirst($key) }}</td>
                                            <td class="text-end">{{ $jumlah }} transaksi</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
                            <div class="mt-3">
                                <table class="table table-sm">
                                    <tbody>
                                        @foreach($status as $key => $jumlah)
                                        <tr>
                                            <td>{{ ucfirst($key) }}</td>
                                            <td class="text-end">{{ $jumlah }} transaksi</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
                            <div class="mt-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Barang</th>
                                            <th class="text-end">Total Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topBarang as $index => $barang)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $barang->nama_barang }}</td>
                                            <td class="text-end">{{ number_format($barang->total_stok, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS -->
    <x-slot name="csscustom">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    </x-slot>

    <!-- Custom JS -->
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
        <script>
            // Format Rupiah Helper
            function formatRupiah(angka, prefix) {
                var number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
            }

            // Data dari Backend
            const bulan = @json($bulanan->pluck('bulan'));
            const totalBulanan = @json($bulanan->pluck('total'));

            const metodeLabels = @json(array_keys($metode->toArray()));
            const metodeData = @json(array_values($metode->toArray()));

            const statusLabels = @json(array_keys($status->toArray()));
            const statusData = @json(array_values($status->toArray()));

            // Data dari Backend (Top Barang)
            const topBarangLabels = @json($topBarang->pluck('nama_barang'));
            const topBarangData = @json($topBarang->pluck('total_stok'));

            // Warna untuk chart
            const chartColors = {
                primary: 'rgba(54, 162, 235, 0.6)',
                success: 'rgba(75, 192, 192, 0.6)',
                warning: 'rgba(255, 206, 86, 0.6)',
                danger: 'rgba(255, 99, 132, 0.6)',
                info: 'rgba(153, 102, 255, 0.6)',
                secondary: 'rgba(201, 203, 207, 0.6)'
            };

            // --- Chart Penjualan Bulanan ---
            new Chart(document.getElementById("chartBulanan"), {
                type: "bar",
                data: {
                    labels: bulan,
                    datasets: [{
                        label: "Total Penjualan (Rp)",
                        data: totalBulanan,
                        backgroundColor: chartColors.primary,
                        borderColor: chartColors.primary.replace('0.6', '1'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // --- Chart Metode Bayar ---
            new Chart(document.getElementById("chartMetode"), {
                type: "doughnut",
                data: {
                    labels: metodeLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                    datasets: [{
                        data: metodeData,
                        backgroundColor: [chartColors.primary, chartColors.success, chartColors.warning, chartColors.danger, chartColors.info, chartColors.secondary]
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
                    labels: statusLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                    datasets: [{
                        data: statusData,
                        backgroundColor: [chartColors.success, chartColors.warning, chartColors.secondary, chartColors.danger]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: "bottom" }
                    }
                }
            });

            // --- Chart Top 10 Barang ---
            new Chart(document.getElementById("chartTopBarang"), {
                type: "bar",
                data: {
                    labels: topBarangLabels,
                    datasets: [{
                        label: "Jumlah Stok",
                        data: topBarangData,
                        backgroundColor: chartColors.success,
                        borderColor: chartColors.success.replace('0.6', '1'),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    scales: { 
                        x: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // DataTables untuk pesanan hari ini (AJAX)
            var table = $('#tbdatatable').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: false,
                info: false,
                lengthChange: false,
                ordering: false,
                ajax: "{{ route('dashboard.pesananHariIniData') }}",
                columns: [
                    { 
                        data: 'DT_RowIndex', 
                        name: 'DT_RowIndex', 
                        orderable: false, 
                        searchable: false 
                    },
                    { 
                        data: 'nomor_invoice', 
                        name: 'nomor_invoice' 
                    },
                    { 
                        data: 'tanggal', 
                        name: 'tanggal',
                        render: function(data) {
                            return data ? moment(data).format("DD-MM-YYYY") : "";
                        }
                    },
                    { 
                        data: 'customer', 
                        name: 'customer' 
                    },
                    { 
                        data: 'grandtotal', 
                        name: 'grandtotal',
                        render: function(data) {
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            const badgeClass = {
                                'lunas': 'success',
                                'pending': 'warning',
                                'batal': 'danger',
                                'hutang': 'secondary'
                            }[data] || 'info';
                            
                            return `<span class="badge bg-${badgeClass}">${data ? data.charAt(0).toUpperCase() + data.slice(1) : ''}</span>`;
                        }
                    },
                    { 
                        data: 'metode_bayar', 
                        name: 'metode_bayar',
                        render: function(data) {
                            return data ? data.charAt(0).toUpperCase() + data.slice(1) : '';
                        }
                    }
                ],
                language: {
                    emptyTable: "Tidak ada data pesanan hari ini"
                }
            });

            // Refresh otomatis setiap 10 detik
            setInterval(function() {
                table.ajax.reload(null, false);
            }, 10000);
        </script>
    </x-slot>
</x-app-layout>