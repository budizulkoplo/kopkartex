<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan Tagihan Cicilan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan Tagihan Cicilan</h3>
                    <small class="text-muted">Periode: <span id="periode-label">
                        @php
                            if(isset($periode) && strlen($periode) == 6) {
                                $tahun = substr($periode, 0, 4);
                                $bulan = substr($periode, 4, 2);
                                $nama_bulan = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                                echo $nama_bulan[$bulan] . ' ' . $tahun;
                            } else {
                                echo date('F Y');
                            }
                        @endphp
                    </span></small>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-inline-flex gap-2">
                        <input type="month" id="bulan" class="form-control form-control-sm"
                               value="{{ $bulan_tampil }}" onchange="changePeriode()" />
                        
                        <select id="unit" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all">Semua Unit</option>
                            @foreach($units as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        
                        <button class="btn btn-danger btn-sm" id="btnLunasiSemua" onclick="showLunasiSemuaModal()">
                            <i class="bi bi-check2-all"></i> Lunasi Semua
                        </button>
                        <button class="btn btn-info btn-sm" onclick="exportData()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistik Cards -->
            <div class="row mt-3" id="statistics-cards">
                <div class="col-md-2 col-sm-4">
                    <div class="card bg-light border">
                        <div class="card-body py-2 text-center">
                            <h6 class="mb-1">Total Tagihan</h6>
                            <h4 class="mb-0 text-primary" id="total-tagihan">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card bg-success bg-opacity-10 border-success">
                        <div class="card-body py-2 text-center">
                            <h6 class="mb-1">Sudah Lunas</h6>
                            <h4 class="mb-0 text-success" id="sudah-lunas">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4">
                    <div class="card bg-warning bg-opacity-10 border-warning">
                        <div class="card-body py-2 text-center">
                            <h6 class="mb-1">Belum Lunas</h6>
                            <h4 class="mb-0 text-warning" id="belum-lunas">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card bg-info bg-opacity-10 border-info">
                        <div class="card-body py-2 text-center">
                            <h6 class="mb-1">Total Nominal</h6>
                            <h4 class="mb-0 text-info" id="total-nominal">Rp 0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card bg-danger bg-opacity-10 border-danger">
                        <div class="card-body py-2 text-center">
                            <h6 class="mb-1">Belum Lunas (Rp)</h6>
                            <h4 class="mb-0 text-danger" id="nominal-belum-lunas">Rp 0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">Data Tagihan Cicilan</h5>
                        <div>
                            <span class="badge bg-warning">Belum Lunas: <span id="count-belum-lunas">0</span></span>
                            <span class="badge bg-success ms-2">Sudah Lunas: <span id="count-sudah-lunas">0</span></span>
                            <span class="badge bg-info ms-2">Total: <span id="count-total">0</span></span>
                        </div>
                    </div>
                    <table id="tbvoucher" class="table table-sm table-bordered" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>NIK Anggota</th>
                                <th>Nama Anggota</th>
                                <th>Unit</th>
                                <th>Cicilan</th>
                                <th>Sisa Tenor</th>
                                <th>Pokok</th>
                                <th>Bunga</th>
                                <th>Total</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th>Status Bayar</th>
                                <th>Periode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="8" class="text-end">Grand Total:</td>
                                <td id="grand-total-pokok" class="text-end">0</td>
                                <td id="grand-total-bunga" class="text-end">0</td>
                                <td id="grand-total" class="text-end">0</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Pelunasan -->
    <div class="modal fade" id="modalPelunasan" tabindex="-1" aria-labelledby="modalPelunasanLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPelunasanLabel">Konfirmasi Pelunasan Cicilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melunasi cicilan ini?</p>
                    <div class="mb-2">
                        <strong>Invoice:</strong> <span id="invoice-pelunasan"></span>
                    </div>
                    <div class="mb-2">
                        <strong>Nama Anggota:</strong> <span id="nama-pelunasan"></span>
                    </div>
                    <div class="mb-2">
                        <strong>Total Cicilan:</strong> <span id="total-pelunasan"></span>
                    </div>
                    <input type="hidden" id="id-pelunasan">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="prosesPelunasan()">Ya, Lunasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Lunasi Semua -->
    <div class="modal fade" id="modalLunasiSemua" tabindex="-1" aria-labelledby="modalLunasiSemuaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLunasiSemuaLabel">Konfirmasi Pelunasan Semua Cicilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melunasi SEMUA cicilan yang belum lunas pada:</p>
                    <div class="alert alert-info">
                        <strong>Periode:</strong> <span id="periode-lunasi-semua"></span><br>
                        <strong>Unit:</strong> <span id="unit-lunasi-semua"></span>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Aksi ini akan melunasi <span id="count-voucher" class="fw-bold">0</span> cicilan yang belum lunas.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="prosesLunasiSemua()">Ya, Lunasi Semua</button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            let table;
            let belumLunasCount = 0;
            let sudahLunasCount = 0;
            let totalCount = 0;
            let currentPeriode = "{{ $periode }}";

            function changePeriode() {
                const bulanInput = $('#bulan').val();
                if (bulanInput) {
                    // Konversi dari YYYY-MM ke YYYYMM
                    const parts = bulanInput.split('-');
                    currentPeriode = parts[0] + parts[1];
                    
                    // Update label periode
                    const monthNames = [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    const monthIndex = parseInt(parts[1]) - 1;
                    $('#periode-label').text(monthNames[monthIndex] + ' ' + parts[0]);
                    
                    reloadTable();
                    loadStatistics();
                }
            }

            function reloadTable() {
                table.ajax.reload();
            }

            function loadStatistics() {
                const unit = $('#unit').val();
                
                $.ajax({
                    url: "{{ route('laporan.tagihan.statistics') }}",
                    type: "GET",
                    data: {
                        periode: currentPeriode,
                        unit: unit
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('#total-tagihan').text(data.total);
                            $('#sudah-lunas').text(data.lunas_count);
                            $('#belum-lunas').text(data.belum_lunas_count);
                            $('#total-nominal').text('Rp ' + formatRupiah(data.total_nominal));
                            $('#nominal-belum-lunas').text('Rp ' + formatRupiah(data.nominal_belum_lunas));
                        }
                    },
                    error: function() {
                        toastr.error('Gagal memuat statistik');
                    }
                });
            }

            function hitungTotal() {
                let data = table.rows().data().toArray();
                let grandTotal = 0;
                let grandTotalPokok = 0;
                let grandTotalBunga = 0;
                belumLunasCount = 0;
                sudahLunasCount = 0;
                totalCount = data.length;

                data.forEach(row => {
                    grandTotal += parseFloat(row.total_cicilan.replace(/[^0-9]/g, ''));
                    grandTotalPokok += parseFloat(row.pokok.replace(/[^0-9]/g, ''));
                    grandTotalBunga += parseFloat(row.bunga.replace(/[^0-9]/g, ''));
                    
                    // Hitung status
                    if (row.status.includes('warning')) {
                        belumLunasCount++;
                    } else if (row.status.includes('success')) {
                        sudahLunasCount++;
                    }
                });

                $('#grand-total').text('Rp ' + formatRupiah(grandTotal));
                $('#grand-total-pokok').text('Rp ' + formatRupiah(grandTotalPokok));
                $('#grand-total-bunga').text('Rp ' + formatRupiah(grandTotalBunga));
                $('#count-belum-lunas').text(belumLunasCount);
                $('#count-sudah-lunas').text(sudahLunasCount);
                $('#count-total').text(totalCount);
            }

            function formatRupiah(angka) {
                return angka.toLocaleString('id-ID');
            }

            function showPelunasanModal(id, invoice, nama, total) {
                $('#id-pelunasan').val(id);
                $('#invoice-pelunasan').text(invoice);
                $('#nama-pelunasan').text(nama);
                $('#total-pelunasan').text(total);
                $('#modalPelunasan').modal('show');
            }

            function showLunasiSemuaModal() {
                if (belumLunasCount === 0) {
                    toastr.warning('Tidak ada cicilan yang belum lunas');
                    return;
                }
                
                const unit = $('#unit').val();
                const unitText = unit === 'all' ? 'Semua Unit' : $('#unit option:selected').text();
                
                // Format periode untuk tampilan
                if (currentPeriode && currentPeriode.length === 6) {
                    const tahun = currentPeriode.substring(0, 4);
                    const bulanAngka = currentPeriode.substring(4, 6);
                    const monthNames = [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    const periodeText = monthNames[parseInt(bulanAngka) - 1] + ' ' + tahun;
                    $('#periode-lunasi-semua').text(periodeText);
                } else {
                    $('#periode-lunasi-semua').text(currentPeriode);
                }
                
                $('#unit-lunasi-semua').text(unitText);
                $('#count-voucher').text(belumLunasCount);
                
                $('#modalLunasiSemua').modal('show');
            }

            function prosesPelunasan() {
                let id = $('#id-pelunasan').val();
                
                $.ajax({
                    url: "{{ route('laporan.tagihan.pelunasan') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    beforeSend: function() {
                        $('#modalPelunasan').find('button').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#modalPelunasan').modal('hide');
                            reloadTable();
                            loadStatistics();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Terjadi kesalahan saat memproses pelunasan');
                    },
                    complete: function() {
                        $('#modalPelunasan').find('button').prop('disabled', false);
                    }
                });
            }

            function prosesLunasiSemua() {
                $.ajax({
                    url: "{{ route('laporan.tagihan.pelunasan_semua') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        periode: currentPeriode,
                        unit: $('#unit').val()
                    },
                    beforeSend: function() {
                        $('#modalLunasiSemua').find('button').prop('disabled', true);
                        toastr.info('Sedang memproses pelunasan semua cicilan...');
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#modalLunasiSemua').modal('hide');
                            reloadTable();
                            loadStatistics();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Terjadi kesalahan saat memproses pelunasan');
                    },
                    complete: function() {
                        $('#modalLunasiSemua').find('button').prop('disabled', false);
                    }
                });
            }

            function exportData() {
                const params = new URLSearchParams({
                    periode: currentPeriode,
                    unit: $('#unit').val()
                });
                
                
            }

            $(document).ready(function() {
                // Load initial statistics
                loadStatistics();
                
                table = $('#tbvoucher').DataTable({
                    ordering: false,
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                    ajax: {
                        url: "{{ route('laporan.tagihan.data') }}",
                        type: "GET",
                        data: function (d) {
                            d.periode = currentPeriode;
                            d.unit = $('#unit').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'nomor_invoice', name: 'nomor_invoice' },
                        { data: 'nik', name: 'nik' },
                        { data: 'nama_anggota', name: 'nama_anggota' },
                        { data: 'nama_unit', name: 'nama_unit' },
                        { data: 'cicilan', name: 'cicilan' },
                        { data: 'sisa_tenor', name: 'sisa_tenor', orderable: false, searchable: false },
                        { data: 'pokok', name: 'pokok', className: "text-end" },
                        { data: 'bunga', name: 'bunga', className: "text-end" },
                        { data: 'total_cicilan', name: 'total_cicilan', className: "text-end" },
                        { data: 'metode_bayar', name: 'metode_bayar' },
                        { data: 'status', name: 'status' },
                        { data: 'status_bayar', name: 'status_bayar' },
                        { data: 'periode_tagihan', name: 'periode_tagihan' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
                    ],
                    drawCallback: function(settings) {
                        hitungTotal();
                        // Update button Lunasi Semua
                        $('#btnLunasiSemua').prop('disabled', belumLunasCount === 0);
                    },
                    dom:
                        "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                        "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'Laporan Tagihan Cicilan - Periode {{ $periode }}',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                format: {
                                    body: function (data, row, column, node) {
                                        // Untuk kolom harga (pokok, bunga, total), hapus format Rp
                                        if (column === 8 || column === 9 || column === 10) {
                                            return data.replace(/[^0-9]/g, '');
                                        }
                                        // Untuk kolom status, ambil teks saja
                                        if (column === 12 || column === 13) {
                                            return $(data).text();
                                        }
                                        return data;
                                    }
                                }
                            },
                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row c', sheet).attr('s', '55');
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="bi bi-printer"></i> Print',
                            className: 'btn btn-primary btn-sm',
                            title: 'Laporan Tagihan Cicilan - Periode {{ $periode }}',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                            },
                            customize: function (win) {
                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', '10pt');
                                
                                $(win.document.body).find('h1')
                                    .css('text-align','center')
                                    .css('font-size', '14pt');
                                    
                                // Tambahkan header statistik
                                $(win.document.body).prepend(
                                    '<div style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;">' +
                                    '<h4>Statistik Tagihan Cicilan</h4>' +
                                    '<div>Total Tagihan: <strong>' + $('#total-tagihan').text() + '</strong></div>' +
                                    '<div>Sudah Lunas: <strong>' + $('#sudah-lunas').text() + '</strong></div>' +
                                    '<div>Belum Lunas: <strong>' + $('#belum-lunas').text() + '</strong></div>' +
                                    '<div>Total Nominal: <strong>' + $('#total-nominal').text() + '</strong></div>' +
                                    '</div>'
                                );
                            }
                        }
                    ]
                });

                // Event handler untuk tombol pelunasan
                $(document).on('click', '.btn-pelunasan', function() {
                    let id = $(this).data('id');
                    let invoice = $(this).data('invoice');
                    let nama = $(this).data('nama');
                    let total = $(this).data('total');
                    showPelunasanModal(id, invoice, nama, total);
                });
            });
        </script>
        
        <style>
            .badge {
                font-size: 0.85em;
                padding: 0.4em 0.7em;
            }
            #btnLunasiSemua:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .dataTables_wrapper .dataTables_filter input {
                margin-left: 10px;
            }
            #statistics-cards .card {
                min-height: 90px;
            }
            #statistics-cards h4 {
                font-size: 1.4rem;
            }
            #statistics-cards h6 {
                font-size: 0.85rem;
            }
        </style>
    </x-slot>
</x-app-layout>