<x-app-layout>
    <x-slot name="pagetitle">Penerimaan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Penerimaan</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Penerimaan</li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Penerimaan</h5>
                    </div>
                    <div class="card-body p-3">
                        {{-- Header Form --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Date</span>
                                    <input type="text" class="form-control datepicker" name="date" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Invoice</span>
                                    <input type="text" class="form-control" name="invoice" value="{{ $invoice ?? '' }}" readonly required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateNewInvoice()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Supplier</span>
                                    <input type="text" class="form-control typeahead" id="supplier-search" name="supplier" required>
                                    <button class="btn btn-outline-primary" type="button" id="btn-add-supplier" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Metode Bayar</span>
                                    <select class="form-control" name="metode_bayar" id="metode_bayar" required>
                                        <option value="cash">Cash</option>
                                        <option value="tempo">Tempo</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm mb-2" id="tempo-field" style="display: none;"> 
                                    <span class="input-group-text label-fixed-width">Tgl Tempo</span>
                                    <input type="text" class="form-control datepicker-tempo" name="tgl_tempo">
                                    <span class="input-group-text bg-info"><i class="bi bi-calendar-check text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barcode</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama">
                                    <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                                </div>
                            </div>
                        </div>

                        {{-- Table Penerimaan --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <thead>
                                            <tr class="bg-light">
                                                <th width="5%">#</th>
                                                <th width="15%">Kode</th>
                                                <th width="25%">Nama Barang</th>
                                                <th width="10%">Qty</th>
                                                <th width="15%">Harga Beli</th>
                                                <th width="15%">Total Harga Beli</th>
                                                <th width="15%">Harga Jual</th>
                                                <th width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <th colspan="5" class="text-end fw-bold">Grand Total:</th>
                                                <th id="grandtotal" class="fw-bold">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-2" id="empty-table-alert" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Belum ada barang yang ditambahkan. Scan barcode atau ketik nama barang.
                                </div>
                            </div>
                        </div>

                        {{-- Catatan dan Tombol --}}
                        <div class="row align-items-start">
                            <div class="col-md-8">
                                <div class="input-group input-group-sm mb-3"> 
                                    <span class="input-group-text label-fixed-width">Catatan</span> 
                                    <textarea class="form-control" name="note" rows="2" placeholder="Keterangan tambahan..."></textarea> 
                                </div>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="button" class="btn btn-warning btn-sm" onclick="clearform();">
                                    <i class="bi bi-x-circle"></i> Batal
                                </button>
                                <!-- <button type="button" class="btn btn-info btn-sm" onclick="quickAddItem()">
                                    <i class="bi bi-plus-circle"></i> Tambah Cepat
                                </button> -->
                                <button type="submit" class="btn btn-success btn-sm" id="btn-simpan">
                                    <i class="bi bi-floppy-fill"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Supplier --}}
    <div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="modalSupplierLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="modalSupplierLabel">
                        <i class="bi bi-plus-circle"></i> Tambah Supplier Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formSupplier" novalidate>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Supplier akan otomatis mendapatkan kode unik
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama_supplier" required>
                                <div class="invalid-feedback">Nama supplier wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control form-control-sm" name="telp" placeholder="0812xxxxxx">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Kontak Person</label>
                                <input type="text" class="form-control form-control-sm" name="kontak_person">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control form-control-sm" name="email" placeholder="supplier@email.com">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control form-control-sm" name="alamat" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm" id="btn-save-supplier">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Quick Add Barang --}}
    <div class="modal fade" id="modalQuickAdd" tabindex="-1" aria-labelledby="modalQuickAddLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="modalQuickAddLabel">
                        <i class="bi bi-plus-circle"></i> Tambah Barang Cepat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" class="form-control form-control-sm" id="quick-kode">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" class="form-control form-control-sm" id="quick-nama">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Harga Beli</label>
                            <input type="number" class="form-control form-control-sm" id="quick-harga-beli" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" class="form-control form-control-sm" id="quick-harga-jual" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addQuickItem()">
                        <i class="bi bi-plus"></i> Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .tt-menu {
                width: 100%;
                background-color: #fff;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                z-index: 1000;
                max-height: 250px;
                overflow-y: auto;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .tt-suggestion {
                padding: 0.5rem 1rem;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
            }
            .tt-suggestion:hover, .tt-suggestion.tt-cursor {
                background-color: #f8f9fa;
                color: #0d6efd;
            }
            .tt-suggestion:last-child {
                border-bottom: none;
            }
            .tt-suggestion.new-supplier {
                color: #198754;
                font-weight: 600;
                background-color: #f0fff4;
                border-left: 3px solid #198754;
            }
            .tt-suggestion.new-supplier:hover {
                background-color: #e2f7eb;
            }
            .label-fixed-width {
                min-width: 90px;
            }
            .w-auto {
                width: auto !important;
                min-width: 100px;
            }
            .table td, .table th {
                vertical-align: middle;
            }
            .dellist {
                cursor: pointer;
                padding: 0.25rem 0.5rem;
            }
            .dellist:hover {
                opacity: 0.8;
            }
            .total-beli {
                font-weight: 600;
                color: #198754;
            }
            #empty-table-alert {
                font-size: 0.875rem;
                padding: 0.5rem 1rem;
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.id.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.1/bloodhound.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.1/typeahead.bundle.min.js"></script>
        <script>
            let barang = [];
            let supplierList = [];
            let rowCounter = 0;

            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                updateTableAlert();
            }

            function updateTableAlert() {
                const rowCount = $('#tbterima tbody tr').length;
                if (rowCount === 0) {
                    $('#empty-table-alert').show();
                } else {
                    $('#empty-table-alert').hide();
                }
            }

            function updateTotals() {
                let grandTotal = 0;
                $('#tbterima tbody tr').each(function() {
                    const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                    const hargaBeli = parseFloat($(this).find('input[name="harga_beli[]"]').val()) || 0;
                    const total = qty * hargaBeli;
                    $(this).find('.total-beli').text(formatCurrency(total));
                    grandTotal += total;
                });
                $('#grandtotal').text(formatCurrency(grandTotal));
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            function addRow(datarow){
    let existingRow = false;
    $('#tbterima tbody tr').each(function() {
        if(datarow.id == $(this).data('id')) {
            existingRow = true;
            // Update quantity jika barang sudah ada
            const qtyInput = $(this).find('input[name="qty[]"]');
            const currentQty = parseInt(qtyInput.val()) || 0;
            qtyInput.val(1);
            updateTotals();
            
            // Auto-focus ke barcode setelah update
            setTimeout(() => {
                $('#barcode-search').val('').focus();
            }, 100);
            return false;
        }
    });
    
    if(!existingRow){
        rowCounter++;
        const str = `<tr data-id="${datarow.id}" class="align-middle" id="row-${rowCounter}">
            <td class="text-center">${rowCounter}</td>
            <td>
                <input type="hidden" name="kode_barang[]" value="${datarow.code || ''}">
                <input type="hidden" name="nama_barang[]" value="${datarow.text}">
                ${datarow.code || 'N/A'}
            </td>
            <td>${datarow.text}</td>
            <td>
                <input type="number" value="1" class="form-control form-control-sm qty" min="1" name="qty[]" style="width: 80px;" required>
                <input type="hidden" name="id[]" value="${datarow.id}">
            </td>
            <td>
                <input type="number" value="${datarow.harga_beli || 0}" step="0.01" class="form-control form-control-sm harga_beli" name="harga_beli[]" style="width: 120px;" required>
            </td>
            <td class="total-beli text-end">${formatCurrency(datarow.harga_beli || 0)}</td>
            <td>
                <input type="number" value="${datarow.harga_jual || 0}" step="0.01" class="form-control form-control-sm" name="harga_jual[]" style="width: 120px;" required>
            </td>
            <td class="text-center">
                <span class="badge bg-danger dellist" onclick="removeRow(${rowCounter})" title="Hapus">
                    <i class="bi bi-trash3-fill"></i>
                </span>
            </td>
        </tr>`;
        $('#tbterima tbody').append(str);
        updateTotals();
        updateTableAlert();
        
        // Auto-focus ke barcode setelah menambah row baru
        setTimeout(() => {
            $('#barcode-search').val('').focus();
        }, 100);
    }
}

    function addQuickItem() {
        const kode = $('#quick-kode').val().trim();
        const nama = $('#quick-nama').val().trim();
        const hargaBeli = parseFloat($('#quick-harga-beli').val()) || 0;
        const hargaJual = parseFloat($('#quick-harga-jual').val()) || 0;

        if (!kode || !nama) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Kode dan Nama barang harus diisi!'
            });
            return;
        }

        // Cek apakah kode sudah ada di tabel
        let kodeExists = false;
        $('#tbterima tbody tr').each(function() {
            const existingKode = $(this).find('input[name="kode_barang[]"]').val();
            if (existingKode === kode) {
                kodeExists = true;
                return false;
            }
        });

        if (kodeExists) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Kode barang sudah ada dalam daftar!'
            });
            return;
        }

        const datarow = {
            id: 'quick-' + Date.now(), // ID sementara
            code: kode,
            text: nama,
            harga_beli: hargaBeli,
            harga_jual: hargaJual
        };

        addRow(datarow);
        
        // Reset form
        $('#quick-kode').val('');
        $('#quick-nama').val('');
        $('#quick-harga-beli').val(0);
        $('#quick-harga-jual').val(0);
        
        $('#modalQuickAdd').modal('hide');
    }

            function removeRow(rowId) {
    $(`#row-${rowId}`).remove();
    numbering();
    updateTotals();
    
    // Auto-focus ke barcode setelah hapus
    setTimeout(() => {
        $('#barcode-search').focus();
    }, 100);
}

            function clearform(){
                if ($('#tbterima tbody tr').length > 0) {
                    Swal.fire({
                        title: 'Bersihkan Form?',
                        text: "Semua data yang belum disimpan akan hilang.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Bersihkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doClearForm();
                        }
                    });
                } else {
                    doClearForm();
                }
            }

            function doClearForm(){
    // Reset header form
    $('input[name="invoice"]').val('');
    $('input[name="supplier"]').val('');
    $('textarea[name="note"]').val('');
    $('select[name="metode_bayar"]').val('cash');
    $('input[name="tgl_tempo"]').val('');
    $('#tempo-field').hide();
    
    // Clear table
    $('#tbterima tbody').empty();
    updateTotals();
    updateTableAlert();
    
    // Generate new invoice
    generateNewInvoice();
    
    // Auto focus ke barcode
    setTimeout(() => {
        $('#barcode-search').focus();
    }, 100);
}

            function generateNewInvoice() {
                $.ajax({
                    url: '{{ route("penerimaan.getinvoice") }}',
                    type: 'GET',
                    beforeSend: function() {
                        $('input[name="invoice"]').val('Loading...');
                    },
                    success: function(response) {
                        $('input[name="invoice"]').val(response);
                    },
                    error: function() {
                        $('input[name="invoice"]').val('ERROR');
                        Swal.fire({
                            icon: 'error', 
                            title: 'Gagal', 
                            text: 'Tidak dapat generate invoice baru',
                            timer: 2000
                        });
                    }
                });
            }

            function quickAddItem() {
                $('#modalQuickAdd').modal('show');
            }

            function addQuickItem() {
                const kode = $('#quick-kode').val().trim();
                const nama = $('#quick-nama').val().trim();
                const hargaBeli = parseFloat($('#quick-harga-beli').val()) || 0;
                const hargaJual = parseFloat($('#quick-harga-jual').val()) || 0;

                if (!kode || !nama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Kode dan Nama barang harus diisi!'
                    });
                    return;
                }

                const datarow = {
                    id: 'quick-' + Date.now(),
                    code: kode,
                    text: nama,
                    harga_beli: hargaBeli,
                    harga_jual: hargaJual
                };

                addRow(datarow);
                
                // Reset form
                $('#quick-kode').val('');
                $('#quick-nama').val('');
                $('#quick-harga-beli').val(0);
                $('#quick-harga-jual').val(0);
                
                $('#modalQuickAdd').modal('hide');
            }

            // Fungsi untuk load supplier
            function loadSuppliers(query = '') {
                $.ajax({
                    url: '{{ route('penerimaan.getsupplier') }}',
                    type: 'GET',
                    data: { q: query },
                    dataType: 'json',
                    success: function(data){
                        supplierList = data;
                    }
                });
            }

            $(document).ready(function () {
                let currentRequest = null;
                let lastInvoice = '{{ $invoice ?? "" }}';
                
                // Set invoice awal
                if (lastInvoice) {
                    $('input[name="invoice"]').val(lastInvoice);
                } else {
                    generateNewInvoice();
                }

                // Inisialisasi load supplier
                loadSuppliers();

                // Toggle field tempo berdasarkan metode bayar
                $('#metode_bayar').on('change', function() {
                    if ($(this).val() === 'tempo') {
                        $('#tempo-field').show();
                        $('input[name="tgl_tempo"]').prop('required', true);
                    } else {
                        $('#tempo-field').hide();
                        $('input[name="tgl_tempo"]').prop('required', false);
                    }
                });

                // Typeahead untuk supplier dengan option untuk tambah baru
                const supplierBloodhound = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.whitespace,
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route('penerimaan.getsupplier') }}?q=%QUERY',
                        wildcard: '%QUERY',
                        transform: function(response) {
                            if (response && response.length === 0 && $('#supplier-search').val().trim() !== '') {
                                response.push({
                                    id: 'new',
                                    text: '[+] Tambah supplier baru: "' + $('#supplier-search').val().trim() + '"'
                                });
                            }
                            return response;
                        }
                    }
                });

                $('#supplier-search').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 2
                }, {
                    name: 'suppliers',
                    source: supplierBloodhound,
                    display: 'text',
                    templates: {
                        suggestion: function(data) {
                            if (data.id === 'new') {
                                return '<div class="tt-suggestion new-supplier">' + data.text + '</div>';
                            }
                            return '<div>' + data.text + '</div>';
                        }
                    }
                }).on('typeahead:select', function(ev, suggestion) {
                    // Cek apakah yang dipilih adalah opsi tambah baru
                    if (suggestion.text && suggestion.text.startsWith('[+] Tambah supplier baru:')) {
                        // Ambil nama supplier dari teks
                        const supplierName = suggestion.text.replace('[+] Tambah supplier baru: "', '').replace('"', '');
                        
                        // Tampilkan modal dengan nama yang sudah terisi
                        $('#modalSupplier input[name="nama_supplier"]').val(supplierName);
                        $('#modalSupplier').modal('show');
                        
                        // Kosongkan input supplier
                        $('#supplier-search').val('');
                        return;
                    }
                });

                // Submit form tambah supplier
                $('#formSupplier').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }
                    
                    const formData = $(this).serialize();
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('penerimaan.store-supplier') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btn-save-supplier').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response.success) {
                                // Set nilai input supplier dengan nama yang baru ditambahkan
                                $('#supplier-search').val(response.supplier.text);
                                
                                // Reload daftar supplier
                                loadSuppliers();
                                
                                // Tutup modal
                                $('#modalSupplier').modal('hide');
                                
                                // Reset form
                                $('#formSupplier')[0].reset();
                                $('#formSupplier').removeClass('was-validated');
                                
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Supplier berhasil ditambahkan',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    $('#supplier-search').focus();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan saat menyimpan supplier!'
                            });
                        },
                        complete: function() {
                            $('#btn-save-supplier').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan');
                        }
                    });
                });

                // Reset modal saat ditutup
                $('#modalSupplier').on('hidden.bs.modal', function () {
                    $('#formSupplier')[0].reset();
                    $('#formSupplier').removeClass('was-validated');
                });

                $('#modalQuickAdd').on('hidden.bs.modal', function () {
                    $('#quick-kode').val('');
                    $('#quick-nama').val('');
                    $('#quick-harga-beli').val(0);
                    $('#quick-harga-jual').val(0);
                });

                // Typeahead untuk barcode (barang)
                // Typeahead untuk barcode (barang) dengan auto-clear
const barangBloodhound = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: '{{ route('penerimaan.getbarang') }}?q=%QUERY',
        wildcard: '%QUERY'
    }
});

$('#barcode-search').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
}, {
    name: 'barang',
    source: barangBloodhound,
    display: 'text',
    templates: {
        suggestion: function(data) {
            return '<div><strong>' + data.code + '</strong> - ' + data.text + '</div>';
        }
    }
}).on('typeahead:select', function(ev, suggestion) {
    addRow(suggestion);
    // Auto clear input setelah memilih
    $(this).typeahead('val', '');
});

// Enter untuk search barcode dengan auto-clear
$('#barcode-search').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const searchVal = $(this).val().trim();
        if (searchVal) {
            $.ajax({
                url: '{{ route('penerimaan.getbarangbycode') }}',
                method: 'GET',
                data: { kode: searchVal },
                dataType: 'json',
                beforeSend: function() {
                    if (currentRequest !== null) currentRequest.abort();
                },
                success: function(response) { 
                    addRow(response);
                    // Auto clear input
                    $('#barcode-search').val('');
                },
                error: function() {
                    Swal.fire({
                        title: "Barang tidak ditemukan!",
                        text: "Ingin tambah barang baru?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tambah Baru',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#quick-kode').val(searchVal);
                            $('#modalQuickAdd').modal('show');
                        }
                        // Auto clear input
                        $('#barcode-search').val('').focus();
                    });
                }
            });
        }
    }
});

                $('#barcode-search').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 2
                }, {
                    name: 'barang',
                    source: barangBloodhound,
                    display: 'text',
                    templates: {
                        suggestion: function(data) {
                            return '<div><strong>' + data.code + '</strong> - ' + data.text + '</div>';
                        }
                    }
                }).on('typeahead:select', function(ev, suggestion) {
                    addRow(suggestion);
                });

                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'id'
                }).datepicker('setDate', new Date());

                $('.datepicker-tempo').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    startDate: '+1d',
                    language: 'id'
                });

                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchVal = $(this).val().trim();
                        if (searchVal) {
                            $.ajax({
                                url: '{{ route('penerimaan.getbarangbycode') }}',
                                method: 'GET',
                                data: { kode: searchVal },
                                dataType: 'json',
                                beforeSend: function() {
                                    if (currentRequest !== null) currentRequest.abort();
                                },
                                success: function(response) { 
                                    addRow(response); 
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Barang tidak ditemukan!",
                                        text: "Ingin tambah barang baru?",
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, Tambah Baru',
                                        cancelButtonText: 'Batal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#quick-kode').val(searchVal);
                                            $('#modalQuickAdd').modal('show');
                                        }
                                    });
                                }
                            });
                        }
                    }
                });

                // Update totals on change Qty / Harga Beli
                $('#tbterima').on('input', '.qty, .harga_beli', function() {
                    updateTotals();
                });

                // Focus ke barcode search saat halaman load
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 500);

                $('#frmterima').on('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) { 
                        e.stopPropagation(); 
                        this.classList.add('was-validated');
                        return; 
                    }

                    // Validasi supplier harus diisi
                    if (!$('#supplier-search').val().trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Supplier harus diisi!'
                        });
                        $('#supplier-search').focus();
                        return;
                    }

                    // Validasi metode bayar tempo
                    if ($('#metode_bayar').val() === 'tempo' && !$('input[name="tgl_tempo"]').val().trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Tanggal tempo harus diisi untuk pembayaran tempo!'
                        });
                        $('input[name="tgl_tempo"]').focus();
                        return;
                    }

                    // Validasi ada barang yang ditambahkan
                    if ($('#tbterima tbody tr').length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Tidak ada barang yang ditambahkan!'
                        });
                        $('#barcode-search').focus();
                        return;
                    }

                    const formData = $(this).serialize();
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('penerimaan.store') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btn-simpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    position: "top-end", 
                                    icon: "success", 
                                    title: "Data berhasil disimpan", 
                                    showConfirmButton: false, 
                                    timer: 2000
                                }).then(() => {
                                    // Redirect ke nota
                                    const notaUrl = '{{ route("penerimaan.nota", ":invoice") }}'.replace(':invoice', response.invoice);
                                    window.open(notaUrl, '_blank');
                                    clearform();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error", 
                                    title: "Oops...", 
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = "Terjadi kesalahan saat menyimpan!";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: "error", 
                                title: "Oops...", 
                                text: errorMsg
                            });
                        },
                        complete: function() {
                            $('#btn-simpan').prop('disabled', false).html('<i class="bi bi-floppy-fill"></i> Simpan');
                        }
                    });
                });

                // Shortcut keyboard
                // Shortcut keyboard untuk input cepat
$(document).keydown(function(e) {
    // Ctrl + S untuk simpan
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        $('#frmterima').submit();
    }
    // Esc untuk batal
    if (e.key === 'Escape') {
        clearform();
    }
    // F2 untuk focus barcode
    if (e.key === 'F2') {
        e.preventDefault();
        $('#barcode-search').focus();
    }
    // F3 untuk quick add
    if (e.key === 'F3') {
        e.preventDefault();
        $('#modalQuickAdd').modal('show');
    }
});

// Auto focus ke input harga/qty setelah tambah barang
$('#tbterima').on('focus', '.harga_beli, .qty', function() {
    $(this).select();
});

// Auto-save on blur untuk qty dan harga
$('#tbterima').on('blur', '.qty, .harga_beli, .harga_jual', function() {
    updateTotals();
});

                // Auto focus ke input harga setelah tambah barang
                $('#tbterima').on('focus', '.harga_beli, .qty', function() {
                    $(this).select();
                });
            });

            // Shortcut keyboard untuk input cepat
$(document).keydown(function(e) {
    // Ctrl + S untuk simpan
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        $('#frmterima').submit();
    }
    // Esc untuk batal
    if (e.key === 'Escape') {
        clearform();
    }
    // F2 untuk focus barcode
    if (e.key === 'F2') {
        e.preventDefault();
        $('#barcode-search').focus();
    }
    // F3 untuk quick add
    if (e.key === 'F3') {
        e.preventDefault();
        $('#modalQuickAdd').modal('show');
    }
});

// Auto focus ke input harga/qty setelah tambah barang
$('#tbterima').on('focus', '.harga_beli, .qty', function() {
    $(this).select();
});

// Auto-save on blur untuk qty dan harga
$('#tbterima').on('blur', '.qty, .harga_beli, .harga_jual', function() {
    updateTotals();
});
        </script>
    </x-slot>
</x-app-layout>