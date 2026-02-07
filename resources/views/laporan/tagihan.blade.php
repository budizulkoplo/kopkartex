<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan Tagihan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan Tagihan</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-inline-flex gap-2">
                        <input type="month" id="bulan" class="form-control form-control-sm"
                               value="{{ $bulan }}" onchange="reloadTable()" />
                        
                        <select id="unit" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all">Semua Unit</option>
                            @foreach($units as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        
                        <button class="btn btn-danger btn-sm" id="btnLunasiSemua" onclick="showLunasiSemuaModal()">
                            <i class="bi bi-check2-all"></i> Lunasi Semua
                        </button>
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
                        <h5 class="card-title mb-0">Data Penjualan Tagihan</h5>
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
                                <th>Total Belanja</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="6" class="text-end">Grand Total:</td>
                                <td id="grand-total" class="text-end">0</td>
                                <td colspan="2"></td>
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
                    <h5 class="modal-title" id="modalPelunasanLabel">Konfirmasi Pelunasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melunasi voucher ini?</p>
                    <div class="mb-2">
                        <strong>Invoice:</strong> <span id="invoice-pelunasan"></span>
                    </div>
                    <div class="mb-2">
                        <strong>Nama Anggota:</strong> <span id="nama-pelunasan"></span>
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
                    <h5 class="modal-title" id="modalLunasiSemuaLabel">Konfirmasi Pelunasan Semua</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melunasi SEMUA voucher yang belum lunas pada:</p>
                    <div class="alert alert-info">
                        <strong>Bulan:</strong> <span id="bulan-lunasi-semua"></span><br>
                        <strong>Unit:</strong> <span id="unit-lunasi-semua"></span>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Aksi ini akan melunasi <span id="count-voucher" class="fw-bold">0</span> voucher yang belum lunas.
                    </div>
                    <input type="hidden" id="params-lunasi-semua">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="prosesLunasiSemua()">Ya, Lunasi Semua</button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            let table;
            let belumLunasCount = 0;
            let sudahLunasCount = 0;
            let totalCount = 0;

            function reloadTable() {
                table.ajax.reload();
            }

            function hitungTotal() {
                let data = table.rows().data().toArray();
                let grandTotal = 0;
                belumLunasCount = 0;
                sudahLunasCount = 0;
                totalCount = data.length;

                data.forEach(row => {
                    grandTotal += parseFloat(row.total_cicilan.replace(/[^0-9]/g, ''));
                    
                    // Hitung status
                    if (row.status.includes('warning')) {
                        belumLunasCount++;
                    } else if (row.status.includes('success')) {
                        sudahLunasCount++;
                    }
                });

                $('#grand-total').text('Rp ' + formatRupiah(grandTotal));
                $('#count-belum-lunas').text(belumLunasCount);
                $('#count-sudah-lunas').text(sudahLunasCount);
                $('#count-total').text(totalCount);
            }

            function formatRupiah(angka) {
                return angka.toLocaleString('id-ID');
            }

            function showPelunasanModal(id, invoice, nama) {
                $('#id-pelunasan').val(id);
                $('#invoice-pelunasan').text(invoice);
                $('#nama-pelunasan').text(nama);
                $('#modalPelunasan').modal('show');
            }

            function showLunasiSemuaModal() {
                if (belumLunasCount === 0) {
                    toastr.warning('Tidak ada voucher yang belum lunas');
                    return;
                }
                
                const bulan = $('#bulan').val();
                const unit = $('#unit').val();
                const bulanText = bulan ? new Date(bulan + '-01').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : 'Semua Bulan';
                const unitText = unit === 'all' ? 'Semua Unit' : $('#unit option:selected').text();
                
                $('#bulan-lunasi-semua').text(bulanText);
                $('#unit-lunasi-semua').text(unitText);
                $('#count-voucher').text(belumLunasCount);
                
                // Simpan parameter untuk request
                $('#params-lunasi-semua').val(JSON.stringify({
                    bulan: bulan,
                    unit: unit
                }));
                
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
                const params = JSON.parse($('#params-lunasi-semua').val());
                
                $.ajax({
                    url: "{{ route('laporan.tagihan.pelunasan_semua') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        bulan: params.bulan,
                        unit: params.unit
                    },
                    beforeSend: function() {
                        $('#modalLunasiSemua').find('button').prop('disabled', true);
                        toastr.info('Sedang memproses pelunasan semua voucher...');
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#modalLunasiSemua').modal('hide');
                            reloadTable();
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

            $(document).ready(function() {
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
                            d.bulan = $('#bulan').val();
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
                        { data: 'total_cicilan', name: 'total_cicilan', className: "text-end" },
                        { data: 'status', name: 'status' },
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
                            title: 'Laporan Penjualan Tagihan',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4, 5, 6, 7],
                                format: {
                                    body: function (data, row, column, node) {
                                        // Untuk kolom total, hapus format Rp
                                        if (column === 6) {
                                            return data.replace(/[^0-9]/g, '');
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
                            title: 'Laporan Penjualan Tagihan',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4, 5, 6, 7]
                            },
                            customize: function (win) {
                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', '10pt');
                                
                                $(win.document.body).find('h1')
                                    .css('text-align','center')
                                    .css('font-size', '14pt');
                            }
                        }
                    ]
                });

                // Event handler untuk tombol pelunasan
                $(document).on('click', '.btn-pelunasan', function() {
                    let id = $(this).data('id');
                    let invoice = $(this).data('invoice');
                    let nama = $(this).data('nama');
                    showPelunasanModal(id, invoice, nama);
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
        </style>
    </x-slot>
</x-app-layout>