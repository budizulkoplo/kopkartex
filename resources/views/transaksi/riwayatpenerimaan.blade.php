<x-app-layout>
    <x-slot name="pagetitle">Riwayat Penerimaan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Riwayat Penerimaan</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <form method="GET" action="{{ route('penerimaan.riwayat') }}" class="row g-2 align-items-center justify-content-end">
                        <div class="col-auto">
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggal_awal }}">
                        </div>
                        <div class="col-auto">
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggal_akhir }}">
                        </div>
                        <div class="col-auto">
                            <input type="text" name="supplier" class="form-control form-control-sm" placeholder="Supplier" value="{{ $supplier }}">
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
            <div class="card card-success card-outline">
                <div class="card-body">
                    <table id="tbriwayatpenerimaan" class="table table-striped table-bordered table-sm" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Supplier</th>
                                <th>Petugas</th>
                                <th class="text-end">Grand Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penerimaan as $key => $row)
                                @php
                                    $grandTotal = $row->details->sum(function($d){ return $d->jumlah * $d->harga_beli; });
                                @endphp
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $row->tgl_penerimaan->format('d-m-Y') }}</td>
                                    <td>{{ $row->nomor_invoice }}</td>
                                    <td>{{ $row->nama_supplier }}</td>
                                    <td>{{ $row->user->name ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($grandTotal,2) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info btn-detail" data-id="{{ $row->idpenerimaan }}" title="Detail">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                            <button class="btn btn-sm btn-warning btn-revisi" data-id="{{ $row->idpenerimaan }}" data-invoice="{{ $row->nomor_invoice }}" title="Revisi">
                                                <i class="bi bi-pencil-square"></i> Revisi
                                            </button>
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

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penerimaan - <span id="modalInvoice"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm" id="detailTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Beli</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Grand Total</th>
                                <th id="grandTotal"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Revisi -->
    <div class="modal fade" id="revisiModal" tabindex="-1" aria-labelledby="revisiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revisiModalLabel">Revisi Penerimaan - <span id="revisiInvoice"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm" id="tblRevisi">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty Terima</th>
                                <th>Qty Revisi</th>
                                <th>Harga Beli</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-end"><strong>Total Revisi:</strong></td>
                                <td id="totalRevisi">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanRevisi">Simpan Revisi</button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .table td, .table th { font-size: small; }
            .card-body { padding: 0.75rem; }
            #tbriwayatpenerimaan_wrapper .dt-buttons { margin-bottom: 0.5rem; }
            .btn-group .btn { margin-right: 2px; }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script>
            $(document).ready(function () {
                $('#tbriwayatpenerimaan').DataTable({
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

                // Event handler untuk tombol detail
                $(document).on('click', '.btn-detail', function() {
                    const penerimaanId = $(this).data('id');
                    loadDetailPenerimaan(penerimaanId);
                });

                // Event handler untuk tombol revisi
                $(document).on('click', '.btn-revisi', function() {
                    const penerimaanId = $(this).data('id');
                    const invoice = $(this).data('invoice');
                    
                    $('#revisiInvoice').text(invoice);
                    $('#revisiModal').data('penerimaan-id', penerimaanId);
                    
                    loadDetailForRevisi(penerimaanId);
                    $('#revisiModal').modal('show');
                });

                // Event handler untuk simpan revisi
                $('#btnSimpanRevisi').click(function() {
                    simpanRevisi();
                });
            });

            function loadDetailPenerimaan(penerimaanId) {
                $.ajax({
                    url: '/penerimaan/detail/' + penerimaanId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            let tbody = '';
                            res.detail.forEach((item, index) => {
                                tbody += `<tr>
                                    <td>${index+1}</td>
                                    <td>${item.kode_barang}</td>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.jumlah}</td>
                                    <td>${formatRupiah(item.harga_beli)}</td>
                                    <td>${formatRupiah(item.total_harga_beli)}</td>
                                </tr>`;
                            });
                            $('#detailTable tbody').html(tbody);
                            $('#grandTotal').text(formatRupiah(res.grand_total));
                            $('#modalInvoice').text(res.penerimaan.nomor_invoice);
                            $('#detailModal').modal('show');
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(err){
                        console.error(err);
                        Swal.fire('Error', 'Gagal memuat detail penerimaan', 'error');
                    }
                });
            }

            function loadDetailForRevisi(penerimaanId) {
                $.ajax({
                    url: '/penerimaan/detail/' + penerimaanId,
                    method: 'GET',
                    beforeSend: function() {
                        $('#tblRevisi tbody').html('<tr><td colspan="8" class="text-center">Memuat data...</td></tr>');
                    },
                    success: function(response) {
                        if (response.success) {
                            const tbody = $('#tblRevisi tbody');
                            tbody.empty();
                            
                            if (response.detail.length > 0) {
                                response.detail.forEach((item, index) => {
                                    const row = `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${item.kode_barang}</td>
                                            <td>${item.nama_barang}</td>
                                            <td>${item.jumlah}</td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm qty-revisi" 
                                                       value="${item.jumlah}"
                                                       min="0" 
                                                       max="${item.jumlah}"
                                                       data-id="${item.id}"
                                                       data-barang-id="${item.barang_id}"
                                                       data-old-qty="${item.jumlah}"
                                                       data-harga="${item.harga_beli}"
                                                       style="width: 80px;">
                                            </td>
                                            <td>${formatRupiah(item.harga_beli)}</td>
                                            <td class="total-row">${formatRupiah(item.total_harga_beli)}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-hapus-item" 
                                                        data-id="${item.id}">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                    tbody.append(row);
                                });
                            } else {
                                tbody.html('<tr><td colspan="8" class="text-center">Tidak ada data detail</td></tr>');
                            }
                            hitungTotalRevisi();
                        } else {
                            Swal.fire('Error', response.message || 'Gagal memuat detail penerimaan', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal memuat detail penerimaan', 'error');
                        $('#tblRevisi tbody').html('<tr><td colspan="8" class="text-center">Error memuat data</td></tr>');
                    }
                });
            }

            function simpanRevisi() {
                const penerimaanId = $('#revisiModal').data('penerimaan-id');
                const itemsToRevisi = [];
                
                $('.qty-revisi').each(function() {
                    const itemId = $(this).data('id');
                    const barangId = $(this).data('barang-id');
                    const oldQty = $(this).data('old-qty');
                    const newQty = parseInt($(this).val());
                    const harga = $(this).data('harga');
                    
                    if (newQty !== oldQty) {
                        itemsToRevisi.push({
                            id: itemId,
                            barang_id: barangId,
                            old_qty: oldQty,
                            new_qty: newQty,
                            harga_beli: harga
                        });
                    }
                });
                
                // Cek item yang dihapus
                $('.btn-hapus-item.btn-success').each(function() {
                    itemsToRevisi.push({
                        id: $(this).data('id'),
                        barang_id: $(this).data('barang-id'),
                        old_qty: $(this).data('old-qty'),
                        new_qty: 0,
                        action: 'delete'
                    });
                });
                
                if (itemsToRevisi.length === 0) {
                    Swal.fire('Info', 'Tidak ada perubahan untuk disimpan', 'info');
                    return;
                }
                
                Swal.fire({
                    title: 'Konfirmasi Revisi',
                    text: 'Apakah Anda yakin ingin menyimpan perubahan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/penerimaan/revisi',
                            method: 'POST',
                            data: {
                                penerimaan_id: penerimaanId,
                                items: itemsToRevisi,
                                _token: '{{ csrf_token() }}'
                            },
                            beforeSend: function() {
                                $('#btnSimpanRevisi').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
                            },
                            success: function(response) {
                                $('#btnSimpanRevisi').prop('disabled', false).html('Simpan Revisi');
                                
                                if (response.success) {
                                    Swal.fire('Success', response.message, 'success');
                                    $('#revisiModal').modal('hide');
                                    // Reload halaman untuk melihat perubahan
                                    location.reload();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                $('#btnSimpanRevisi').prop('disabled', false).html('Simpan Revisi');
                                Swal.fire('Error', 'Gagal menyimpan revisi', 'error');
                            }
                        });
                    }
                });
            }

            // Hitung total revisi
            function hitungTotalRevisi() {
                let total = 0;
                
                $('.qty-revisi').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    const harga = $(this).data('harga');
                    const rowTotal = qty * harga;
                    
                    $(this).closest('tr').find('.total-row').text(formatRupiah(rowTotal));
                    total += rowTotal;
                });
                
                $('#totalRevisi').text(formatRupiah(total));
            }

            // Format Rupiah helper function
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(angka);
            }

            // Event handler untuk perubahan quantity revisi
            $(document).on('input', '.qty-revisi', function() {
                const oldQty = $(this).data('old-qty');
                const maxQty = $(this).data('old-qty');
                const currentVal = parseInt($(this).val()) || 0;
                
                if (currentVal > maxQty) {
                    $(this).val(maxQty);
                    Swal.fire('Info', `Jumlah tidak boleh lebih dari ${maxQty}`, 'info');
                }
                
                if (currentVal < 0) {
                    $(this).val(0);
                }
                
                hitungTotalRevisi();
            });

            // Event handler untuk tombol hapus item
            $(document).on('click', '.btn-hapus-item', function() {
                $(this).toggleClass('btn-danger btn-success');
                if ($(this).hasClass('btn-success')) {
                    $(this).html('<i class="bi bi-check"></i> Dihapus');
                    $(this).closest('tr').find('.qty-revisi').val(0).prop('disabled', true);
                } else {
                    $(this).html('<i class="bi bi-trash"></i> Hapus');
                    const oldQty = $(this).data('old-qty');
                    $(this).closest('tr').find('.qty-revisi').val(oldQty).prop('disabled', false);
                }
                hitungTotalRevisi();
            });
        </script>
    </x-slot>
</x-app-layout>