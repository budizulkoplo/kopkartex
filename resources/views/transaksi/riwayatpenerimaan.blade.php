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
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="{{ route('penerimaan.riwayat') }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h5 class="card-title mb-0">Data Penerimaan</h5>
                    <div class="card-tools">
                        @if($penerimaan->count() > 0)
                            <span class="badge bg-info">
                                Total: Rp {{ number_format($penerimaan->sum('grandtotal'), 0, ',', '.') }}
                            </span>
                            <span class="badge bg-secondary ms-1">
                                {{ $penerimaan->total() }} data
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($penerimaan->count() > 0)
                    <div class="table-responsive">
                        <table id="tbriwayatpenerimaan" class="table table-striped table-bordered table-hover table-sm" style="width:100%; font-size: small;">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="12%">Invoice</th>
                                    <th>Supplier</th>
                                    <th width="10%">Metode Bayar</th>
                                    <th width="10%">Status Bayar</th>
                                    <th width="12%">Petugas</th>
                                    <th width="12%" class="text-end">Grand Total</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penerimaan as $key => $row)
                                    @php
                                        $totalQty = $row->details->sum('jumlah');
                                    @endphp
                                    <tr>
                                        <td>{{ ($penerimaan->currentPage() - 1) * $penerimaan->perPage() + $key + 1 }}</td>
                                        <td>{{ $row->tgl_penerimaan->format('d-m-Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $row->nomor_invoice }}</span>
                                            @if($row->note)
                                                <i class="bi bi-chat-text text-primary ms-1" title="{{ $row->note }}"></i>
                                            @endif
                                        </td>
                                        <td>{{ $row->nama_supplier }}</td>
                                        <td>
                                            @if($row->metode_bayar == 'tempo')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Tempo
                                                    @if($row->tgl_tempo)
                                                        <br><small>{{ \Carbon\Carbon::parse($row->tgl_tempo)->format('d-m-Y') }}</small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-cash"></i> Cash</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->status_bayar == 'pending')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-hourglass"></i> Pending
                                                </span>
                                                <button class="btn btn-xs btn-outline-primary btn-update-status ms-1" 
                                                        data-id="{{ $row->idpenerimaan }}"
                                                        title="Update Status">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Paid
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $row->user->name ?? '-' }}</td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($row->grandtotal ?? 0, 0, ',', '.') }}
                                            <br>
                                            <small class="text-muted">{{ $totalQty }} item</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- <button class="btn btn-info btn-detail" 
                                                        data-id="{{ $row->idpenerimaan }}"
                                                        data-invoice="{{ $row->nomor_invoice }}"
                                                        title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button> -->
                                                <a href="/penerimaan/nota/{{ $row->nomor_invoice }}" 
                                                   target="_blank"
                                                   class="btn btn-warning"
                                                   title="Cetak Nota">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <button class="btn btn-primary btn-revisi" 
                                                        data-id="{{ $row->idpenerimaan }}" 
                                                        data-invoice="{{ $row->nomor_invoice }}"
                                                        title="Revisi">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @if(auth()->user()->hasRole('superadmin'))
                                                <button class="btn btn-danger btn-batal" 
                                                        data-id="{{ $row->idpenerimaan }}"
                                                        data-invoice="{{ $row->nomor_invoice }}"
                                                        title="Batalkan">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($penerimaan->count() > 0)
                            <tfoot>
                                <tr class="table-success">
                                    <td colspan="7" class="text-end fw-bold">TOTAL HALAMAN:</td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($penerimaan->sum('grandtotal'), 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    @if($penerimaan->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $penerimaan->firstItem() }} - {{ $penerimaan->lastItem() }} dari {{ $penerimaan->total() }} data
                        </div>
                        <div>
                            {{ $penerimaan->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                    @endif
                    
                    @else
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2 mb-0">Tidak ada data penerimaan</p>
                            <small class="text-muted">Coba gunakan filter yang berbeda</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-eye"></i> Detail Penerimaan - <span id="modalInvoice"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal</strong></td>
                                    <td>: <span id="modalTanggal"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier</strong></td>
                                    <td>: <span id="modalSupplier"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Petugas</strong></td>
                                    <td>: <span id="modalPetugas"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Metode Bayar</strong></td>
                                    <td>: <span id="modalMetodeBayar"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Bayar</strong></td>
                                    <td>: <span id="modalStatusBayar"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Catatan</strong></td>
                                    <td>: <span id="modalCatatan"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th width="10%" class="text-center">Jumlah</th>
                                    <th width="15%" class="text-end">Harga Beli</th>
                                    <th width="15%" class="text-end">Harga Jual</th>
                                    <th width="15%" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="6" class="text-end">Grand Total</th>
                                    <th class="text-end" id="grandTotal"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="cetakDetail()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Revisi -->
    <div class="modal fade" id="revisiModal" tabindex="-1" aria-labelledby="revisiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="revisiModalLabel">
                        <i class="bi bi-pencil-square"></i> Revisi Penerimaan - <span id="revisiInvoice"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Catatan: 
                        <ul class="mb-0 mt-1">
                            <li>Untuk mengurangi stok, masukkan jumlah yang lebih kecil dari jumlah asli</li>
                            <li>Untuk menghapus item, klik tombol hapus dan item akan dikembalikan ke stok</li>
                            <li>Revisi akan dicatat dalam history</li>
                        </ul>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tblRevisi">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th width="10%" class="text-center">Stok Sekarang</th>
                                    <th width="10%" class="text-center">Qty Terima</th>
                                    <th width="10%" class="text-center">Qty Revisi</th>
                                    <th width="15%" class="text-end">Harga Beli</th>
                                    <th width="15%" class="text-end">Subtotal</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Total Revisi:</strong></td>
                                    <td id="totalRevisi" class="text-end fw-bold">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSimpanRevisi">
                        <i class="bi bi-save"></i> Simpan Revisi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Batalkan -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-exclamation-triangle"></i> Konfirmasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin membatalkan penerimaan <strong id="confirmInvoice"></strong>?</p>
                    <p class="text-danger"><small><i class="bi bi-info-circle"></i> Semua stok yang terkait akan dikembalikan dan data tidak dapat dipulihkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tidak
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmBatal">
                        <i class="bi bi-check-circle"></i> Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .table td, .table th { 
                font-size: small;
                vertical-align: middle;
            }
            .card-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }
            .btn-group .btn {
                border-radius: 4px !important;
            }
            .badge {
                font-size: 0.75em;
            }
            .table-hover tbody tr:hover {
                background-color: rgba(0,0,0,.02);
            }
            #detailTable tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .modal-xl {
                max-width: 1200px;
            }
            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script>
            $(document).ready(function () {
                // Inisialisasi DataTable hanya jika ada data
                @if($penerimaan->count() > 0)
                const table = $('#tbriwayatpenerimaan').DataTable({
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari _MAX_ total data)",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        }
                    },
                    dom: 
                        "<'row'<'col-md-6'l><'col-md-6'f>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row'<'col-md-5'i><'col-md-7'p>>",
                    order: [[1, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [0, 8] } // Kolom # dan Aksi tidak bisa di-sort
                    ]
                });
                @endif

                // Event handler untuk tombol detail
                $(document).on('click', '.btn-detail', function() {
                    const penerimaanId = $(this).data('id');
                    const invoice = $(this).data('invoice');
                    loadDetailPenerimaan(penerimaanId, invoice);
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

                // Event handler untuk tombol batal
                $(document).on('click', '.btn-batal', function() {
                    const penerimaanId = $(this).data('id');
                    const invoice = $(this).data('invoice');
                    
                    $('#confirmInvoice').text(invoice);
                    $('#confirmModal').data('penerimaan-id', penerimaanId);
                    $('#confirmModal').modal('show');
                });

                // Event handler untuk update status bayar
                $(document).on('click', '.btn-update-status', function() {
                    updateStatusBayar($(this).data('id'));
                });

                // Event handler untuk simpan revisi
                $('#btnSimpanRevisi').click(function() {
                    simpanRevisi();
                });

                // Event handler untuk konfirmasi batal
                $('#btnConfirmBatal').click(function() {
                    batalkanPenerimaan($('#confirmModal').data('penerimaan-id'));
                });
            });

            function loadDetailPenerimaan(penerimaanId, invoice) {
                // Gunakan URL langsung
                const url = `/penerimaan/detail/${penerimaanId}`;
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#detailModal .modal-body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Memuat data...</p></div>');
                        $('#detailModal').modal('show');
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#modalInvoice').text(res.penerimaan.nomor_invoice);
                            $('#modalTanggal').text(res.penerimaan.tgl_penerimaan);
                            $('#modalSupplier').text(res.penerimaan.nama_supplier);
                            $('#modalPetugas').text(res.penerimaan.user_name);
                            $('#modalMetodeBayar').text(res.penerimaan.metode_bayar == 'tempo' ? 'Tempo' : 'Cash');
                            $('#modalStatusBayar').text(res.penerimaan.status_bayar == 'pending' ? 'Pending' : 'Paid');
                            $('#modalCatatan').text(res.penerimaan.note || '-');
                            
                            let tbody = '';
                            let grandTotal = 0;
                            
                            res.detail.forEach((item, index) => {
                                const subtotal = item.jumlah * item.harga_beli;
                                grandTotal += subtotal;
                                
                                tbody += `<tr>
                                    <td class="text-center">${index+1}</td>
                                    <td>${item.kode_barang || 'N/A'}</td>
                                    <td>${item.nama_barang}</td>
                                    <td class="text-center">${formatNumber(item.jumlah)}</td>
                                    <td class="text-end">Rp ${formatNumber(item.harga_beli)}</td>
                                    <td class="text-end">Rp ${formatNumber(item.harga_jual)}</td>
                                    <td class="text-end fw-bold">Rp ${formatNumber(subtotal)}</td>
                                </tr>`;
                            });
                            
                            $('#detailTable tbody').html(tbody);
                            $('#grandTotal').html(`<strong>Rp ${formatNumber(grandTotal)}</strong>`);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            $('#detailModal').modal('hide');
                        }
                    },
                    error: function(err){
                        console.error(err);
                        Swal.fire('Error', 'Gagal memuat detail penerimaan', 'error');
                        $('#detailModal').modal('hide');
                    }
                });
            }

            function loadDetailForRevisi(penerimaanId) {
                // Gunakan URL langsung
                const url = `/penerimaan/detail/${penerimaanId}`;
                
                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: function() {
                        $('#tblRevisi tbody').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>');
                    },
                    success: function(response) {
                        if (response.success) {
                            const tbody = $('#tblRevisi tbody');
                            tbody.empty();
                            
                            if (response.detail.length > 0) {
                                response.detail.forEach((item, index) => {
                                    const subtotal = item.jumlah * item.harga_beli;
                                    const row = `
                                        <tr>
                                            <td class="text-center">${index + 1}</td>
                                            <td>${item.kode_barang || 'N/A'}</td>
                                            <td>${item.nama_barang}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">-</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold">${formatNumber(item.jumlah)}</span>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm qty-revisi" 
                                                       value="${item.jumlah}"
                                                       min="0" 
                                                       data-id="${item.id}"
                                                       data-barang-id="${item.barang_id}"
                                                       data-old-qty="${item.jumlah}"
                                                       data-harga="${item.harga_beli}"
                                                       style="width: 80px; margin: 0 auto;">
                                            </td>
                                            <td class="text-end">Rp ${formatNumber(item.harga_beli)}</td>
                                            <td class="text-end fw-bold total-row">Rp ${formatNumber(subtotal)}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-item" 
                                                        data-id="${item.id}"
                                                        data-barang-id="${item.barang_id}"
                                                        data-old-qty="${item.jumlah}"
                                                        title="Hapus item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                    tbody.append(row);
                                });
                            } else {
                                tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data detail</td></tr>');
                            }
                            hitungTotalRevisi();
                        } else {
                            Swal.fire('Error', response.message || 'Gagal memuat detail penerimaan', 'error');
                            $('#revisiModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal memuat detail penerimaan', 'error');
                        $('#revisiModal').modal('hide');
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
                    const newQty = parseInt($(this).val()) || 0;
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
                $('.btn-hapus-item.bg-danger').each(function() {
                    const itemId = $(this).data('id');
                    const barangId = $(this).data('barang-id');
                    const oldQty = $(this).data('old-qty');
                    
                    // Hapus dari itemsToRevisi jika sudah ada
                    const existingIndex = itemsToRevisi.findIndex(item => item.id === itemId);
                    if (existingIndex > -1) {
                        itemsToRevisi.splice(existingIndex, 1);
                    }
                    
                    itemsToRevisi.push({
                        id: itemId,
                        barang_id: barangId,
                        old_qty: oldQty,
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
                    html: `<p>Anda akan melakukan revisi pada ${itemsToRevisi.length} item.</p>
                          <p class="text-warning"><small><i class="bi bi-exclamation-triangle"></i> Revisi akan mempengaruhi stok barang.</small></p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan Revisi!',
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
                                $('#btnSimpanRevisi').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Revisi');
                                
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        $('#revisiModal').modal('hide');
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                $('#btnSimpanRevisi').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Revisi');
                                Swal.fire('Error', 'Gagal menyimpan revisi', 'error');
                            }
                        });
                    }
                });
            }

            function batalkanPenerimaan(penerimaanId) {
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Penerimaan akan dibatalkan dan stok akan dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, batalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `/penerimaan/batalkan/${penerimaanId}`;
                        
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            beforeSend: function() {
                                $('#btnConfirmBatal').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Dibatalkan!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        $('#confirmModal').modal('hide');
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Gagal membatalkan penerimaan', 'error');
                            },
                            complete: function() {
                                $('#btnConfirmBatal').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Ya, Batalkan');
                            }
                        });
                    }
                });
            }

            function updateStatusBayar(penerimaanId) {
                Swal.fire({
                    title: 'Update Status Bayar',
                    text: "Ubah status menjadi 'Paid'?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Update!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `/penerimaan/update-status/${penerimaanId}`;
                        
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                status_bayar: 'paid',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Gagal mengupdate status', 'error');
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
                    
                    $(this).closest('tr').find('.total-row').text('Rp ' + formatNumber(rowTotal));
                    total += rowTotal;
                });
                
                $('#totalRevisi').text('Rp ' + formatNumber(total));
            }

            // Format helper functions
            function formatNumber(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            // Event handler untuk perubahan quantity revisi
            $(document).on('input', '.qty-revisi', function() {
                const currentVal = parseInt($(this).val()) || 0;
                
                if (currentVal < 0) {
                    $(this).val(0);
                }
                
                hitungTotalRevisi();
            });

            // Event handler untuk tombol hapus item
            $(document).on('click', '.btn-hapus-item', function(e) {
                e.preventDefault();
                const $btn = $(this);
                
                if ($btn.hasClass('bg-danger')) {
                    // Kembalikan ke normal
                    $btn.removeClass('bg-danger text-white').addClass('btn-outline-danger');
                    $btn.closest('tr').find('.qty-revisi').prop('disabled', false);
                    $btn.html('<i class="bi bi-trash"></i>');
                } else {
                    // Tandai untuk dihapus
                    $btn.removeClass('btn-outline-danger').addClass('bg-danger text-white');
                    $btn.closest('tr').find('.qty-revisi').prop('disabled', true);
                    $btn.html('<i class="bi bi-check-lg"></i> Dihapus');
                }
                
                hitungTotalRevisi();
            });

            function cetakDetail() {
                window.print();
            }

            // Print hanya tabel detail saat cetak
            window.onbeforeprint = function() {
                $('.modal-dialog').addClass('d-none');
                $('#detailTable').removeClass('d-none').addClass('table-print');
            };

            window.onafterprint = function() {
                $('.modal-dialog').removeClass('d-none');
                $('#detailTable').removeClass('table-print');
            };
        </script>
    </x-slot>
</x-app-layout>