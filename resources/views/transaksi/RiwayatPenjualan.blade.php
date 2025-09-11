<x-app-layout>
    <x-slot name="pagetitle">Riwayat Penjualan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Riwayat Penjualan</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <form method="GET" action="{{ route('jual.riwayat') }}" class="row g-2 align-items-center justify-content-end">
                        <div class="col-auto">
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggal_awal }}">
                        </div>
                        <div class="col-auto">
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggal_akhir }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbpenjualan" class="table table-striped table-bordered table-sm" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No Invoice</th>
                                <th>Tanggal</th>
                                <th>Kasir</th>
                                <th>Customer</th>
                                <th class="text-end">Grand Total</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan as $p)
                            <tr>
                                <td>
                                    <a href="{{ url('/penjualan/nota/'.$p->nomor_invoice) }}" target="_blank">
                                        {{ $p->nomor_invoice }}
                                    </a>
                                </td>
                                <td>{{ $p->tanggal }}</td>
                                <td>{{ $p->kasir }}</td>
                                <td>{{ $p->customer }}</td>
                                <td class="text-end">{{ number_format($p->grandtotal,2) }}</td>
                                <td>{{ ucfirst($p->metode_bayar) }}</td>
                                <td>{{ ucfirst($p->status) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ url('/penjualan/nota/'.$p->nomor_invoice) }}" class="btn btn-sm btn-primary" target="_blank" title="Cetak Nota">
                                            <i class="bi bi-printer-fill"></i> Cetak
                                        </a>
                                        @if($p->status == 'lunas' || ($p->metode_bayar == 'cicilan' && $p->status == 'hutang'))
                                        <button type="button" class="btn btn-sm btn-danger btn-retur" data-invoice="{{ $p->nomor_invoice }}" data-id="{{ $p->id }}" title="Retur Barang">
                                            <i class="bi bi-arrow-return-left"></i> Retur
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Retur -->
    <div class="modal fade" id="returModal" tabindex="-1" aria-labelledby="returModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returModalLabel">Retur Barang - <span id="modalInvoice"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm" id="tblRetur">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanRetur">Simpan Retur</button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .table td, .table th { font-size: small; }
            .card-body { padding: 0.75rem; }
            #tbpenjualan_wrapper .dt-buttons { margin-bottom: 0.5rem; }
            .btn-group .btn { margin-right: 2px; }
        </style>
    </x-slot>

    <x-slot name="jscustom">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script> window.JSZip = JSZip; </script>
    <script>
        $(document).ready(function () {
            $('#tbpenjualan').DataTable({
                responsive: true,
                pageLength: 50,
                lengthMenu: [[25,50,100,-1],[25,50,100,'All']],
                ordering: false,
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
                        exportOptions: { columns: ':visible' }
                    }
                ]
            });

            // Event handler untuk tombol retur
            $(document).on('click', '.btn-retur', function() {
                const penjualanId = $(this).data('id');
                const invoice = $(this).data('invoice');
                
                $('#modalInvoice').text(invoice);
                $('#returModal').data('penjualan-id', penjualanId);
                
                // Load detail penjualan
                loadDetailPenjualan(penjualanId);
                
                $('#returModal').modal('show');
            });

            // Event handler untuk simpan retur
            $('#btnSimpanRetur').click(function() {
                simpanRetur();
            });
        });

        function loadDetailPenjualan(penjualanId) {
            $.ajax({
                url: '/penjualan/detail/' + penjualanId,
                method: 'GET',
                beforeSend: function() {
                    $('#tblRetur tbody').html('<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
                },
                success: function(response) {
                    if (response.success) {
                        const tbody = $('#tblRetur tbody');
                        tbody.empty();
                        
                        if (response.detail.length > 0) {
                            response.detail.forEach((item, index) => {
                                const row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.kode_barang}</td>
                                        <td>${item.nama_barang}</td>
                                        <td>${item.qty}</td>
                                        <td>${formatRupiah(item.harga)}</td>
                                        <td>${formatRupiah(item.qty * item.harga)}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-retur-item" 
                                                    data-id="${item.id}" 
                                                    data-barang-id="${item.barang_id}" 
                                                    data-qty="${item.qty}">
                                                <i class="bi bi-arrow-return-left"></i> Retur
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.html('<tr><td colspan="7" class="text-center">Tidak ada data detail</td></tr>');
                        }
                    } else {
                        Swal.fire('Error', response.message || 'Gagal memuat detail penjualan', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal memuat detail penjualan', 'error');
                    $('#tblRetur tbody').html('<tr><td colspan="7" class="text-center">Error memuat data</td></tr>');
                }
            });
        }

        function simpanRetur() {
            const penjualanId = $('#returModal').data('penjualan-id');
            const itemsToRetur = [];
            
            $('.btn-retur-item.btn-success').each(function() {
                itemsToRetur.push({
                    id: $(this).data('id'),
                    barang_id: $(this).data('barang-id'),
                    qty: $(this).data('qty')
                });
            });
            
            if (itemsToRetur.length === 0) {
                Swal.fire('Info', 'Pilih minimal satu barang untuk diretur', 'info');
                return;
            }
            
            Swal.fire({
                title: 'Konfirmasi Retur',
                text: 'Apakah Anda yakin ingin melakukan retur barang yang dipilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Retur!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/penjualan/retur',
                        method: 'POST',
                        data: {
                            penjualan_id: penjualanId,
                            items: itemsToRetur,
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function() {
                            $('#btnSimpanRetur').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
                        },
                        success: function(response) {
                            $('#btnSimpanRetur').prop('disabled', false).html('Simpan Retur');
                            
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success');
                                $('#returModal').modal('hide');
                                // Reload halaman untuk melihat perubahan
                                location.reload();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            $('#btnSimpanRetur').prop('disabled', false).html('Simpan Retur');
                            Swal.fire('Error', 'Gagal melakukan retur', 'error');
                        }
                    });
                }
            });
        }

        // Format Rupiah helper function
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        // Toggle selection pada item retur
        $(document).on('click', '.btn-retur-item', function() {
            $(this).toggleClass('btn-danger btn-success');
            if ($(this).hasClass('btn-success')) {
                $(this).html('<i class="bi bi-check"></i> Dipilih');
            } else {
                $(this).html('<i class="bi bi-arrow-return-left"></i> Retur');
            }
        });
    </script>
</x-slot>
</x-app-layout>