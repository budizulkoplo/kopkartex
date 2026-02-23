{{-- resources/views/laporan/pinbrg.blade.php --}}
<x-app-layout>
    <x-slot name="pagetitle">Laporan Pinjaman Barang (PINBRG)</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Laporan Pinjaman Barang (PINBRG)</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card card-info card-outline mb-4">
                <div class="card-header pt-1 pb-1">
                    <h6 class="mb-0">Filter Laporan</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Periode</label>
                            <input type="month" class="form-control form-control-sm" name="period" id="period" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pencarian</label>
                            <input type="text" class="form-control form-control-sm" name="search" id="search" placeholder="Cari No. Anggota, No. Invoice, No. Badge...">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <button type="button" class="btn btn-sm btn-success" id="btnGenerate">
                                    <i class="bi bi-arrow-repeat"></i> Generate Data
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="btnExportDbf">
                                    <i class="bi bi-file-earmark-binary"></i> Export to DBF
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnResetFilter">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsCards">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 bg-primary bg-opacity-10 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Data</h6>
                                    <h4 class="mb-0" id="totalData">0</h4>
                                </div>
                                <div class="bg-primary rounded-circle p-2">
                                    <i class="bi bi-table text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 bg-success bg-opacity-10 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Pinjaman</h6>
                                    <h4 class="mb-0 text-success" id="totalPinjaman">Rp 0</h4>
                                </div>
                                <div class="bg-success rounded-circle p-2">
                                    <i class="bi bi-cash-coin text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 bg-info bg-opacity-10 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Sisa Pinjaman</h6>
                                    <h4 class="mb-0 text-info" id="sisaPinjaman">Rp 0</h4>
                                </div>
                                <div class="bg-info rounded-circle p-2">
                                    <i class="bi bi-piggy-bank text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 bg-warning bg-opacity-10 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Status Aktif</h6>
                                    <h6 class="mb-0"><span id="statusAktif">0</span> / <span id="statusNonAktif">0</span></h6>
                                </div>
                                <div class="bg-warning rounded-circle p-2">
                                    <i class="bi bi-check-circle text-dark"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card card-info card-outline">
                <div class="card-header pt-1 pb-1">
                    <h6 class="mb-0">Data Pinjaman Barang</h6>
                </div>
                <div class="card-body">
                    <table id="tbPinbrg" class="table table-sm table-hover w-100" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th width="3%">No</th>
                                <th>Periode</th>
                                <th>Unit Usaha</th>
                                <th>Lokasi</th>
                                <th>No. Anggota</th>
                                <th>No. Invoice</th>
                                <th>No. Pin</th>
                                <th>Tgl Pinjam</th>
                                <th>Total Harga</th>
                                <th>Jumlah Pinjaman</th>
                                <th>Sisa Pinjaman</th>
                                <th>Angs ke-1</th>
                                <th>Angs ke-2</th>
                                <th>Jenis</th>
                                <th>No. Badge</th>
                                <th>Kel</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(document).ready(function() {
                // Set default period
                const today = new Date();
                const yearMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
                $('#period').val(yearMonth);
                
                // Initialize DataTable
                let tbPinbrg = $('#tbPinbrg').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('laporan.pinbrg') }}",
                        type: "GET",
                        data: function(d) {
                            // DataTables otomatis mengirim parameter: 
                            // draw, start, length, search[value], order, columns, dll
                            
                            // Tambahkan parameter custom
                            d.period = $('#period').val();
                            
                            // Untuk pencarian, gunakan parameter search bawaan DataTables
                            // Tidak perlu d.search manual karena sudah ada d.search.value
                        },
                        error: function(xhr, error, thrown) {
                            console.error('DataTables error:', error, thrown);
                            console.error('Response:', xhr.responseJSON);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Gagal memuat data pinbrg: ' + (xhr.responseJSON?.message || error)
                            });
                        }
                    },
                    columns: [
                        { 
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            className: 'text-center',
                            orderable: false,
                            searchable: false
                        },
                        { data: 'period', name: 'period' },
                        { data: 'unit_usaha', name: 'unit_usaha' },
                        { data: 'lokasi', name: 'lokasi' },
                        { data: 'NO_AGT', name: 'NO_AGT' },
                        { data: 'NOPIN', name: 'NOPIN' },
                        { data: 'NO_PIN', name: 'NO_PIN' },
                        { 
                            data: 'TG_PIN_formatted', 
                            name: 'TG_PIN',
                            className: 'text-center'
                        },
                        { 
                            data: 'TOTAL_HARGA_formatted', 
                            name: 'TOTAL_HARGA',
                            className: 'text-end'
                        },
                        { 
                            data: 'JUM_PIN_formatted', 
                            name: 'JUM_PIN',
                            className: 'text-end'
                        },
                        { 
                            data: 'SISA_PIN_formatted', 
                            name: 'SISA_PIN',
                            className: 'text-end'
                        },
                        { 
                            data: 'ANGSUR1_formatted', 
                            name: 'ANGSUR1',
                            className: 'text-end'
                        },
                        { 
                            data: 'ANGSUR2_formatted', 
                            name: 'ANGSUR2',
                            className: 'text-end'
                        },
                        { data: 'JENIS', name: 'JENIS' },
                        { data: 'NO_BADGE', name: 'NO_BADGE' },
                        { data: 'KEL', name: 'KEL' },
                        { 
                            data: 'STATUS_badge', 
                            name: 'STATUS',
                            className: 'text-center'
                        }
                    ],
                    order: [[7, 'desc']],
                    language: {
                        emptyTable: "Tidak ada data pinjaman barang",
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari _MAX_ total data)",
                        zeroRecords: "Tidak ditemukan data yang cocok",
                        loadingRecords: "Memuat...",
                        processing: "Memproses...",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "›",
                            previous: "‹"
                        }
                    },
                    responsive: true,
                    pageLength: 100,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    drawCallback: function(settings) {
                        updateStatistics();
                    }
                });

                // Filter form submit - Gunakan untuk reload dengan parameter period
                $('#filterForm').submit(function(e) {
                    e.preventDefault();
                    tbPinbrg.ajax.reload();
                });

                // Search otomatis menggunakan fitur bawaan DataTables
                // Hapus event handler search manual karena sudah ditangani DataTables
                $('#search').off('keyup');

                // Ganti dengan event untuk filter period saja
                $('#period').on('change', function() {
                    tbPinbrg.ajax.reload();
                });
                
                // Update statistics
                function updateStatistics() {
                    // Get total records
                    let totalData = tbPinbrg.page.info().recordsTotal;
                    $('#totalData').text(totalData);
                    
                    // Calculate totals from all data (not just displayed)
                    $.ajax({
                        url: "{{ route('laporan.pinbrg') }}",
                        type: "GET",
                        data: {
                            period: $('#period').val(),
                            search: $('#search').val(),
                            get_totals: true
                        },
                        success: function(response) {
                            if (response.totals) {
                                $('#totalPinjaman').text('Rp ' + parseInt(response.totals.total_jum_pin).toLocaleString('id-ID'));
                                $('#sisaPinjaman').text('Rp ' + parseInt(response.totals.total_sisa_pin).toLocaleString('id-ID'));
                                $('#statusAktif').text(response.totals.aktif || 0);
                                $('#statusNonAktif').text(response.totals.non_aktif || 0);
                            }
                        }
                    });
                }
                
                // Filter form submit
                $('#filterForm').submit(function(e) {
                    e.preventDefault();
                    tbPinbrg.ajax.reload();
                });
                
                // Search on keyup with delay
                let searchTimer;
                $('#search').on('keyup', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        tbPinbrg.ajax.reload();
                    }, 500);
                });
                
                // Generate data
                $('#btnGenerate').click(function() {
                    const period = $('#period').val();
                    
                    Swal.fire({
                        title: 'Generate Data',
                        text: `Generate data pinbrg untuk periode ${period}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Generate!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Mohon Tunggu...',
                                text: 'Sedang generate data',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            $.ajax({
                                url: "{{ route('laporan.pinbrg.generate') }}",
                                type: "POST",
                                data: {
                                    period: period,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: response.message,
                                            timer: 2000,
                                            showConfirmButton: true
                                        }).then(() => {
                                            tbPinbrg.ajax.reload();
                                            updateStatistics();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            text: response.message
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Terjadi kesalahan server';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: errorMsg
                                    });
                                }
                            });
                        }
                    });
                });
                
                // Export to DBF
                $('#btnExportDbf').click(function() {
                    const period = $('#period').val();
                    
                    // Check if data exists first
                    $.ajax({
                        url: "{{ route('laporan.pinbrg') }}",
                        type: "GET",
                        data: {
                            period: period,
                            check_data: true
                        },
                        success: function(response) {
                            if (response.total_data === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Tidak Ada Data',
                                    text: `Tidak ada data untuk periode ${period}. Generate data terlebih dahulu.`
                                });
                                return;
                            }
                            
                            // Proceed with export
                            Swal.fire({
                                title: 'Export to DBF',
                                text: `Export data pinbrg periode ${period} ke format DBF?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Ya, Export!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        title: 'Mohon Tunggu...',
                                        text: 'Sedang memproses export',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });
                                    
                                    // Create form for download
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = "{{ route('laporan.pinbrg.export.dbf') }}";
                                    form.style.display = 'none';
                                    
                                    const tokenInput = document.createElement('input');
                                    tokenInput.type = 'hidden';
                                    tokenInput.name = '_token';
                                    tokenInput.value = "{{ csrf_token() }}";
                                    
                                    const periodInput = document.createElement('input');
                                    periodInput.type = 'hidden';
                                    periodInput.name = 'period';
                                    periodInput.value = period;
                                    
                                    form.appendChild(tokenInput);
                                    form.appendChild(periodInput);
                                    
                                    document.body.appendChild(form);
                                    form.submit();
                                    document.body.removeChild(form);
                                    
                                    Swal.close();
                                }
                            });
                        }
                    });
                });
                
                // Reset filter
                $('#btnResetFilter').click(function() {
                    $('#period').val(yearMonth);
                    $('#search').val('');
                    tbPinbrg.ajax.reload();
                });
            });
        </script>
    </x-slot>
</x-app-layout>