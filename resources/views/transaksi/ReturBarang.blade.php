<x-app-layout>
    <x-slot name="pagetitle">Retur Barang</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Retur Barang</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('retur.list') }}">List</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmretur">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Retur Barang</h5>
                    </div>
                    <div class="card-body p-3">
                        {{-- Header Form --}}
                        <div class="retur-header-grid mb-3">
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">Tanggal</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control datepicker" name="tgl_retur" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                            </div>
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">No. Retur</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="invoice" value="{{ $invoice ?? '' }}" readonly required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateNewInvoice()" title="Generate nomor baru">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">Unit</label>
                                <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->unit->nama_unit ?? 'Unit' }}" disabled>
                            </div>
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">Petugas</label>
                                <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name }}" disabled>
                            </div>
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">Supplier</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control typeahead" id="supplier-search" name="supplier" required>
                                    <input type="hidden" id="supplier_id" name="supplier_id">
                                    <input type="hidden" id="kode_supplier" name="kode_supplier">
                                    <button class="btn btn-outline-primary" type="button" id="btn-add-supplier" data-bs-toggle="modal" data-bs-target="#modalSupplier" title="Tambah supplier">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="retur-field">
                                <label class="form-label form-label-sm mb-1">Invoice Beli</label>
                                <select class="form-select form-select-sm" id="penerimaan-search" name="penerimaan_id" required></select>
                            </div>
                        </div>

                        {{-- Scan Barang --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label form-label-sm mb-1">Barcode / Nama Barang</label>
                                <div class="input-group input-group-sm mb-1"> 
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama" autocomplete="off">
                                    <button class="btn btn-outline-primary" type="button" onclick="quickAddItem()">
                                        <i class="bi bi-plus-lg"></i> Tambah Baru
                                    </button>
                                    <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                                </div>
                                <small class="text-muted">Tekan Enter untuk mencari, F2 untuk auto focus</small>
                            </div>
                        </div>

                        {{-- Table Retur --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="tbretur" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <thead>
                                            <tr class="bg-light">
                                                <th width="5%">#</th>
                                                <th width="12%">Kode</th>
                                                <th width="23%">Nama Barang</th>
                                                <th width="8%">Qty Beli</th>
                                                <th width="8%">Stok</th>
                                                <th width="10%">Qty Retur</th>
                                                <th width="12%">Harga Beli</th>
                                                <th width="12%">Harga Jual</th>
                                                <th width="12%">Subtotal</th>
                                                <th width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <th colspan="8" class="text-end fw-bold">Grand Total:</th>
                                                <th id="grandtotal" class="fw-bold text-end">0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-2" id="empty-table-alert" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Belum ada barang yang diretur. Scan barcode atau ketik nama barang.
                                </div>
                            </div>
                        </div>

                        {{-- Catatan dan Tombol --}}
                        <div class="row align-items-end">
                            <!-- Catatan -->
                            <div class="col-md-8">
                                <label class="form-label form-label-sm mb-1">Catatan</label>
                                <textarea class="form-control form-control-sm"
                                        name="note"
                                        rows="2"
                                        placeholder="Keterangan tambahan..."></textarea>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-md-4 d-flex justify-content-end gap-2">
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="clearform();">
                                    <i class="bi bi-x-circle"></i> Batal
                                </button>

                                <button type="submit"
                                        class="btn btn-success btn-sm"
                                        id="btn-simpan">
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

    {{-- Modal Tambah Barang Baru --}}
    <div class="modal fade" id="modalAddBarang" tabindex="-1" aria-labelledby="modalAddBarangLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalAddBarangLabel">
                        <i class="bi bi-plus-circle"></i> Tambah Barang Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddBarang" novalidate>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="kode_barang" id="add-kode-barang" required>
                                <div class="invalid-feedback">Kode barang wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama_barang" id="add-nama-barang" required>
                                <div class="invalid-feedback">Nama barang wajib diisi</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Satuan</label>
                                <select class="form-control form-control-sm" name="idsatuan" id="add-satuan">
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuans ?? [] as $satuan)
                                        <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select class="form-control form-control-sm" name="idkategori" id="add-kategori">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoris ?? [] as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Kelompok Unit</label>
                                <select class="form-control form-control-sm" name="kelompok_unit" id="add-kelompok">
                                    <option value="toko">Toko</option>
                                    <option value="bengkel">Bengkel</option>
                                    <option value="air">Air</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="harga_beli" id="add-harga-beli" step="0.01" min="0" required>
                                <div class="invalid-feedback">Harga beli wajib diisi</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="harga_jual" id="add-harga-jual" step="0.01" min="0" required>
                                <div class="invalid-feedback">Harga jual wajib diisi</div>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-control form-control-sm" name="type" id="add-type" placeholder="Contoh: Original, KW, Premium, dll">
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi (Opsional)</label>
                                <textarea class="form-control form-control-sm" name="deskripsi" id="add-deskripsi" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-save-barang">
                            <i class="bi bi-save"></i> Simpan & Tambah ke Retur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="csscustom">
       <style>
            .twitter-typeahead {
                flex: 1;
                position: relative;
                display: block !important;
            }
            .twitter-typeahead .tt-hint {
                display: none !important;
            }
            .twitter-typeahead .tt-input {
                width: 100% !important;
                height: calc(1.5em + 0.5rem + 2px) !important;
                padding: 0.25rem 0.5rem !important;
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
                border: 1px solid #ced4da !important;
                border-radius: 0.25rem !important;
            }
            .twitter-typeahead .tt-input:focus {
                border-color: #80bdff !important;
                outline: 0 !important;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            }
            .tt-menu {
                width: 100% !important;
                background-color: #fff !important;
                border: 1px solid #ced4da !important;
                border-radius: 0.25rem !important;
                z-index: 1000 !important;
                max-height: 250px !important;
                overflow-y: auto !important;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1) !important;
            }
            .tt-suggestion {
                padding: 0.5rem 1rem !important;
                cursor: pointer !important;
                border-bottom: 1px solid #f0f0f0 !important;
                font-size: 0.875rem !important;
            }
            .tt-suggestion:hover, .tt-suggestion.tt-cursor {
                background-color: #f8f9fa !important;
                color: #0d6efd !important;
            }
            .tt-suggestion:last-child {
                border-bottom: none !important;
            }
            .tt-suggestion.new-supplier {
                color: #198754 !important;
                font-weight: 600 !important;
                background-color: #f0fff4 !important;
                border-left: 3px solid #198754 !important;
            }
            .tt-suggestion.new-supplier:hover {
                background-color: #e2f7eb !important;
            }
            .retur-header-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(220px, 1fr));
                gap: 0.75rem 1rem;
                align-items: end;
            }
            .retur-field {
                min-width: 0;
            }
            .retur-field .form-label {
                font-weight: 600;
                color: #495057;
            }
            .retur-field .twitter-typeahead {
                min-width: 0;
            }
            #penerimaan-search + .select2-container {
                width: 100% !important;
                display: block;
            }
            #penerimaan-search + .select2-container .select2-selection--single {
                min-height: calc(1.5em + 0.5rem + 2px);
                height: calc(1.5em + 0.5rem + 2px);
                border-color: #ced4da;
                font-size: 0.875rem;
            }
            #penerimaan-search + .select2-container .select2-selection__rendered {
                line-height: calc(1.5em + 0.5rem);
                padding-left: 0.5rem;
                padding-right: 2rem;
            }
            #penerimaan-search + .select2-container .select2-selection__arrow {
                height: calc(1.5em + 0.5rem);
            }
            .label-fixed-width {
                min-width: 90px !important;
                font-size: 0.875rem !important;
            }
            .table td, .table th {
                vertical-align: middle !important;
            }
            .dellist {
                cursor: pointer !important;
                padding: 0.25rem 0.5rem !important;
            }
            .dellist:hover {
                opacity: 0.8 !important;
            }
            #empty-table-alert {
                font-size: 0.875rem !important;
                padding: 0.5rem 1rem !important;
            }
            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                opacity: 1 !important;
            }
            .input-group.input-group-sm {
                height: auto !important;
            }
            .input-group.input-group-sm > .input-group-text {
                height: calc(1.5em + 0.5rem + 2px) !important;
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
            }
            .input-group.input-group-sm > .btn {
                height: calc(1.5em + 0.5rem + 2px) !important;
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
                padding: 0.25rem 0.5rem !important;
            }
            .input-group .btn-sm {
                height: calc(1.5em + 0.5rem + 2px) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.25rem 0.5rem !important;
            }
            .type-badge {
                font-size: 0.7rem !important;
                padding: 0.1rem 0.4rem !important;
                background-color: #6c757d !important;
                color: white !important;
                border-radius: 0.25rem !important;
                margin-left: 5px !important;
            }
            @media (max-width: 768px) {
                .retur-header-grid {
                    grid-template-columns: 1fr;
                }
                .table-responsive {
                    font-size: 0.8rem !important;
                }
                .table th, .table td {
                    padding: 0.3rem !important;
                }
                input.form-control-sm {
                    font-size: 0.8rem !important;
                    padding: 0.2rem 0.3rem !important;
                }
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.1/typeahead.bundle.min.js"></script>
        <script>
            let barang = [];
            let supplierList = [];
            let rowCounter = 0;

            function numbering(){
                $('#tbretur tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                updateTableAlert();
            }

            function updateTableAlert() {
                const rowCount = $('#tbretur tbody tr').length;
                if (rowCount === 0) {
                    $('#empty-table-alert').show();
                } else {
                    $('#empty-table-alert').hide();
                }
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            function updateTotals() {
                let grandTotal = 0;
                
                $('#tbretur tbody tr').each(function() {
                    const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                    const hargaBeli = parseFloat($(this).find('input[name="harga_beli[]"]').val()) || 0;
                    const subtotal = qty * hargaBeli;
                    
                    $(this).find('.subtotal-item').text(formatCurrency(subtotal));
                    grandTotal += subtotal;
                });
                
                $('#grandtotal').text(formatCurrency(grandTotal));
            }

            function addRow(datarow, options = {}){
                // Cek apakah barang sudah ada di tabel
                let existingRow = false;
                const searchCode = datarow.code || datarow.kode_barang || '';
                const qtyBeli = parseFloat(datarow.qty_beli || datarow.jumlah || 0) || 0;
                
                $('#tbretur tbody tr').each(function() {
                    const rowCode = $(this).find('input[name="kode_barang[]"]').val();
                    if(searchCode && rowCode === searchCode) {
                        existingRow = true;

                        const qtyInput = $(this).find('input[name="qty[]"]');
                        if (!options.draft && options.increment !== false) {
                            const currentQty = parseFloat(qtyInput.val()) || 0;
                            const maxRetur = parseFloat(qtyInput.attr('max')) || 0;
                            
                            if (currentQty <= 0 && maxRetur > 0) {
                                qtyInput.val(Math.min(1, maxRetur));
                            } else if (currentQty + 1 <= maxRetur) {
                                qtyInput.val(currentQty + 1);
                            }
                        }
                        
                        $(this).prependTo('#tbretur tbody');
                        numbering();
                        updateTotals();
                        clearAndFocusBarcode(false);
                        setTimeout(() => qtyInput.focus().select(), 100);
                        return false;
                    }
                });
                
                if(!existingRow){
                    rowCounter++;
                    
                    // Validasi stok minimal 1 untuk bisa diretur
                    if (!options.draft && datarow.stok <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Habis',
                            text: 'Barang ' + (datarow.text || datarow.nama_barang) + ' tidak memiliki stok untuk diretur'
                        });
                        clearAndFocusBarcode();
                        return;
                    }
                    
                    const typeBadge = datarow.type ? `<span class="type-badge">${datarow.type}</span>` : '';
                    
                    const rowQty = options.draft ? 0 : (parseFloat(datarow.qty) || 1);
                    const maxRetur = Math.max(Math.min(parseFloat(datarow.stok) || 0, qtyBeli || parseFloat(datarow.stok) || 0), 0);
                    const str = `<tr data-id="${datarow.id || 'new-' + rowCounter}" data-stok="${datarow.stok}" class="align-middle" id="row-${rowCounter}">
                        <td class="text-center">${rowCounter}</td>
                        <td>
                            <input type="hidden" name="kode_barang[]" value="${datarow.code || datarow.kode_barang || ''}">
                            <input type="hidden" name="nama_barang[]" value="${datarow.text || datarow.nama_barang || ''}">
                            <input type="hidden" name="barang_id[]" value="${datarow.id || ''}">
                            <input type="hidden" name="satuan[]" value="${datarow.satuan || ''}">
                            <input type="hidden" name="kategori[]" value="${datarow.kategori || ''}">
                            <input type="hidden" name="qty_beli[]" value="${qtyBeli}">
                            ${datarow.code || datarow.kode_barang || 'N/A'}
                        </td>
                        <td>
                            ${datarow.text || datarow.nama_barang || ''}
                            ${typeBadge}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">${formatQty(qtyBeli)}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info max-stok" data-stok="${datarow.stok}">${formatQty(datarow.stok)}</span>
                        </td>
                        <td>
                            <input type="number" value="${rowQty}" class="form-control form-control-sm qty" min="0" max="${maxRetur}" name="qty[]" style="width: 90px;" required>
                            <small class="text-muted">Max: ${formatQty(maxRetur)}</small>
                        </td>
                        <td>
                            <input type="number" value="${datarow.harga_beli || 0}" step="0.01" class="form-control form-control-sm harga_beli" name="harga_beli[]" style="width: 100px;" required>
                        </td>
                        <td>
                            <input type="number" value="${datarow.harga_jual || 0}" step="0.01" class="form-control form-control-sm harga_jual" name="harga_jual[]" style="width: 100px;" required>
                        </td>
                        <td class="text-end">
                            <span class="subtotal-item fw-bold">${formatCurrency(rowQty * (datarow.harga_beli || 0))}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger dellist" onclick="removeRow(${rowCounter})" title="Hapus">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>`;
                    $('#tbretur tbody').prepend(str);
                    numbering();
                    updateTotals();
                    updateTableAlert();
                    
                    clearAndFocusBarcode(!options.draft);
                }
            }

            function formatQty(value) {
                const qty = parseFloat(value) || 0;
                return Number.isInteger(qty) ? qty.toString() : qty.toString().replace(/\.?0+$/, '');
            }

            function clearAndFocusBarcode(focus = true) {
                $('#barcode-search').typeahead('val', '');
                $('#barcode-search').val('');
                if (focus) {
                    setTimeout(() => {
                        $('#barcode-search').focus();
                    }, 100);
                }
            }

            function removeRow(rowId) {
                $(`#row-${rowId}`).remove();
                numbering();
                updateTotals();
                
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            function loadInvoiceDraftItems(invoice) {
                $('#tbretur tbody').empty();

                if (!invoice || !invoice.details || invoice.details.length === 0) {
                    updateTotals();
                    numbering();
                    Swal.fire('Perhatian', 'Invoice ini tidak memiliki detail barang.', 'warning');
                    return;
                }

                invoice.details.forEach(function(item) {
                    addRow(item, { draft: true, increment: false });
                });

                updateTotals();
                numbering();
                $('#barcode-search').focus();
            }

            function clearform(){
                if ($('#tbretur tbody tr').length > 0) {
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
                $('input[name="invoice"]').val('');
                $('#penerimaan-search').val(null).trigger('change');
                $('#supplier-search').val('');
                $('#supplier_id').val('');
                $('#kode_supplier').val('');
                $('textarea[name="note"]').val('');
                $('#tbretur tbody').empty();
                updateTotals();
                updateTableAlert();
                
                generateNewInvoice();
                
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            function generateNewInvoice() {
                $.ajax({
                    url: '{{ route("retur.getinvoice") }}',
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
                            text: 'Tidak dapat generate nomor retur baru',
                            timer: 2000
                        });
                    }
                });
            }

            function quickAddItem() {
                const searchVal = $('#barcode-search').val().trim();
                if (searchVal) {
                    $('#add-kode-barang').val(searchVal);
                    $('#add-nama-barang').val('');
                } else {
                    $('#add-kode-barang').val('');
                    $('#add-nama-barang').val('');
                }
                $('#add-harga-beli').val('');
                $('#add-harga-jual').val('');
                $('#add-type').val('');
                $('#modalAddBarang').modal('show');
            }

            function loadSuppliers(query = '') {
                $.ajax({
                    url: '{{ route('retur.getsupplier') }}',
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
                
                if (lastInvoice) {
                    $('input[name="invoice"]').val(lastInvoice);
                } else {
                    generateNewInvoice();
                }

                loadSuppliers();

                // Typeahead untuk supplier dengan option tambah baru
                const supplierBloodhound = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.whitespace,
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route('retur.getsupplier') }}?q=%QUERY',
                        wildcard: '%QUERY',
                        transform: function(response) {
                            if (response && response.length === 0 && $('#supplier-search').val().trim() !== '') {
                                response.push({
                                    id: 'new',
                                    kode_supplier: '',
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
                            return '<div><strong>' + (data.kode_supplier || '') + '</strong> - ' + data.text + '</div>';
                        }
                    }
                }).on('typeahead:select', function(ev, suggestion) {
                    if (suggestion.text && suggestion.text.startsWith('[+] Tambah supplier baru:')) {
                        const supplierName = suggestion.text.replace('[+] Tambah supplier baru: "', '').replace('"', '');
                        
                        $('#modalSupplier input[name="nama_supplier"]').val(supplierName);
                        $('#modalSupplier').modal('show');
                        
                        $('#supplier-search').val('');
                        $('#supplier_id').val('');
                        $('#kode_supplier').val('');
                        return;
                    }
                    
                    $('#supplier-search').val(suggestion.text);
                    $('#supplier_id').val(suggestion.id);
                    $('#kode_supplier').val(suggestion.kode_supplier || '');
                    $('#penerimaan-search').val(null).trigger('change');
                    $('#tbretur tbody').empty();
                    updateTotals();
                    numbering();
                    $('#penerimaan-search').focus();
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
                        url: '{{ route('retur.store-supplier') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btn-save-supplier').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#supplier-search').val(response.supplier.text);
                                $('#supplier_id').val(response.supplier.id);
                                $('#kode_supplier').val(response.supplier.kode_supplier);
                                
                                loadSuppliers();
                                
                                $('#modalSupplier').modal('hide');
                                
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

                // Submit form tambah barang baru
                $('#formAddBarang').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }
                    
                    const formData = $(this).serialize();
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('retur.store-barang') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btn-save-barang').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response.success) {
                                // Barang baru memiliki stok 0, tapi tetap bisa ditambahkan
                                // untuk keperluan retur nanti (jika ada stok)
                                addRow(response.barang);
                                
                                $('#modalAddBarang').modal('hide');
                                
                                $('#formAddBarang')[0].reset();
                                $('#formAddBarang').removeClass('was-validated');
                                
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Barang berhasil ditambahkan',
                                    showConfirmButton: false,
                                    timer: 1500
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
                            let errorMsg = 'Terjadi kesalahan saat menyimpan barang!';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: errorMsg
                            });
                        },
                        complete: function() {
                            $('#btn-save-barang').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan & Tambah ke Retur');
                        }
                    });
                });

                // Reset modal saat ditutup
                $('#modalSupplier').on('hidden.bs.modal', function () {
                    $('#formSupplier')[0].reset();
                    $('#formSupplier').removeClass('was-validated');
                });

                $('#modalAddBarang').on('hidden.bs.modal', function () {
                    $('#formAddBarang')[0].reset();
                    $('#formAddBarang').removeClass('was-validated');
                    $('#barcode-search').focus();
                });

                // Typeahead untuk barcode (barang)
                const barangBloodhound = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.whitespace,
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route('retur.getbarang') }}?q=%QUERY&penerimaan_id=%PENERIMAAN',
                        replace: function(url, query) {
                            return url.replace('%QUERY', query).replace('%PENERIMAAN', $('#penerimaan-search').val() || '');
                        },
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
                            const typeBadge = data.type ? `<span class="type-badge">${data.type}</span>` : '';
                            const stokWarning = data.stok <= 0 ? ' <span class="badge bg-danger">Stok Habis</span>' : '';
                            return `<div>
                                <strong>${data.code}</strong> - ${data.text} ${typeBadge} ${stokWarning}
                                <br>
                                <small class="text-muted">
                                    Stok: ${data.stok} | Beli: ${formatCurrency(data.harga_beli)} | Jual: ${formatCurrency(data.harga_jual)}
                                    ${data.satuan ? `| Satuan: ${data.satuan}` : ''}
                                </small>
                            </div>`;
                        }
                    }
                }).on('typeahead:select', function(ev, suggestion) {
                    addRow(suggestion);
                    $(this).typeahead('val', '');
                });

                $('#penerimaan-search').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih invoice pembelian',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('retur.get-penerimaan-invoice') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                supplier_id: $('#supplier_id').val() || ''
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: item.nomor_invoice + ' - ' + (item.tgl_penerimaan || '-') + ' - ' + formatCurrency(item.grandtotal || 0),
                                        payload: item
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id || !data.payload) {
                            return data.text;
                        }

                        return $(`
                            <div>
                                <strong>${data.payload.nomor_invoice}</strong> - ${data.payload.nama_supplier || '-'}
                                <br>
                                <small class="text-muted">${data.payload.tgl_penerimaan || '-'} | ${formatCurrency(data.payload.grandtotal || 0)}</small>
                            </div>
                        `);
                    }
                }).on('select2:opening', function(e) {
                    if (!$('#supplier_id').val()) {
                        e.preventDefault();
                        Swal.fire('Perhatian', 'Pilih supplier terlebih dahulu.', 'warning');
                        $('#supplier-search').focus();
                    }
                }).on('select2:select', function(e) {
                    const invoice = e.params.data.payload;
                    loadInvoiceDraftItems(invoice);
                }).on('select2:clear', function() {
                    $('#tbretur tbody').empty();
                    updateTotals();
                    numbering();
                });

                // Enter untuk search barcode
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchVal = $(this).val().trim();
                        const supplierId = $('#supplier_id').val();
                        if (!supplierId) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian',
                                text: 'Pilih supplier terlebih dahulu.'
                            });
                            $('#supplier-search').focus();
                            return;
                        }
                        const penerimaanId = $('#penerimaan-search').val();
                        if (!penerimaanId) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian',
                                text: 'Pilih invoice pembelian terlebih dahulu.'
                            });
                            $('#penerimaan-search').focus();
                            return;
                        }
                        if (searchVal) {
                            $.ajax({
                                url: '{{ route('retur.getbarangbycode') }}',
                                method: 'GET',
                                data: { kode: searchVal, penerimaan_id: penerimaanId },
                                dataType: 'json',
                                beforeSend: function() {
                                    if (currentRequest !== null) currentRequest.abort();
                                },
                                success: function(response) { 
                                    addRow(response);
                                    $('#barcode-search').val('');
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Barang tidak ditemukan!",
                                        text: "Ingin tambah barang baru dengan kode '" + searchVal + "'?",
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, Tambah Baru',
                                        cancelButtonText: 'Batal',
                                        showDenyButton: true,
                                        denyButtonText: 'Cari dengan Nama'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $('#add-kode-barang').val(searchVal);
                                            $('#add-nama-barang').val('');
                                            $('#add-type').val('');
                                            $('#modalAddBarang').modal('show');
                                        } else if (result.isDenied) {
                                            $.ajax({
                                                url: '{{ route('retur.getbarang') }}',
                                                method: 'GET',
                                                data: { q: searchVal },
                                                dataType: 'json',
                                                success: function(data) {
                                                    if (data && data.length > 0) {
                                                        // Typeahead sudah menanganinya
                                                    } else {
                                                        $('#add-nama-barang').val(searchVal);
                                                        $('#add-kode-barang').val('');
                                                        $('#add-type').val('');
                                                        $('#modalAddBarang').modal('show');
                                                    }
                                                }
                                            });
                                        }
                                        $('#barcode-search').val('').focus();
                                    });
                                }
                            });
                        }
                    }
                });

                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'id'
                }).datepicker('setDate', new Date());

                // Update totals on change Qty / Harga Beli
                $('#tbretur').on('input', '.qty, .harga_beli', function() {
                    updateTotals();
                });

                // Validasi qty tidak melebihi stok
                $('#tbretur').on('input', '.qty', function() {
                    const maxStok = parseFloat($(this).attr('max')) || 0;
                    const currentVal = parseFloat($(this).val()) || 0;
                    
                    if (currentVal > maxStok) {
                        $(this).val(maxStok);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Tidak Cukup',
                            text: 'Maksimal retur ' + maxStok + ' item',
                            timer: 1500
                        });
                    }
                });

                // Validasi harga jual harus lebih tinggi dari harga beli (opsional)
                $('#tbretur').on('blur', '.harga_jual', function() {
                    const hargaJual = parseFloat($(this).val()) || 0;
                    const hargaBeli = parseFloat($(this).closest('tr').find('.harga_beli').val()) || 0;
                    
                    if (hargaJual > 0 && hargaBeli > 0 && hargaJual < hargaBeli) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Harga jual lebih rendah dari harga beli. Apakah Anda yakin?',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Simpan',
                            cancelButtonText: 'Perbaiki'
                        }).then((result) => {
                            if (!result.isConfirmed) {
                                $(this).val(hargaBeli);
                                $(this).focus();
                            }
                        });
                    }
                });

                // Focus ke barcode search saat halaman load
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 500);

                $('#frmretur').on('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) { 
                        e.stopPropagation(); 
                        this.classList.add('was-validated');
                        return; 
                    }

                    const penerimaanId = $('#penerimaan-search').val();
                    if (!$('#supplier_id').val()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Supplier harus dipilih terlebih dahulu!'
                        });
                        $('#supplier-search').focus();
                        return;
                    }

                    if (!penerimaanId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Invoice pembelian harus dipilih!'
                        });
                        $('#penerimaan-search').focus();
                        return;
                    }

                    // Validasi supplier harus dipilih
                    const supplierId = $('#supplier_id').val();
                    const supplierName = $('#supplier-search').val().trim();
                    if (!supplierId || !supplierName) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Supplier harus dipilih dari daftar!'
                        });
                        $('#supplier-search').focus();
                        return;
                    }

                    // Validasi ada barang yang diretur
                    if ($('#tbretur tbody tr').length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Tidak ada barang yang diretur!'
                        });
                        $('#barcode-search').focus();
                        return;
                    }

                    // Validasi qty tidak melebihi stok
                    let validStok = true;
                    let hasReturQty = false;
                    let errorMessage = '';
                    
                    $('input[name="qty[]"]').each(function(index) {
                        const qty = parseFloat($(this).val()) || 0;
                        const maxStok = parseFloat($(this).attr('max')) || 0;
                        const namaBarang = $(this).closest('tr').find('input[name="nama_barang[]"]').val();

                        if (qty > 0) {
                            hasReturQty = true;
                        }
                        
                        if (qty > maxStok) {
                            validStok = false;
                            errorMessage += `- ${namaBarang}: Qty (${qty}) > maksimal retur (${maxStok})\n`;
                        }
                        
                        if (qty < 0) {
                            validStok = false;
                            errorMessage += `- ${namaBarang}: Qty tidak boleh minus\n`;
                        }
                    });

                    if (!hasReturQty) {
                        validStok = false;
                        errorMessage += '- Isi minimal 1 qty retur\n';
                    }
                    
                    if (!validStok) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok Tidak Valid',
                            html: `<div class="text-start"><small>${errorMessage.replace(/\n/g, '<br>')}</small></div>`,
                            confirmButtonText: 'Perbaiki'
                        });
                        return;
                    }

                    proceedWithSubmit();
                });

                function proceedWithSubmit() {
                    const formData = $('#frmretur').serializeArray();
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('retur.store') }}',
                        data: $.param(formData),
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
                                    const notaUrl = '{{ route("retur.nota", ":invoice") }}'.replace(':invoice', response.invoice);
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
                }

                // Shortcut keyboard
                $(document).keydown(function(e) {
                    // Ctrl + S untuk simpan
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        $('#frmretur').submit();
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
                    // F3 untuk tambah barang baru
                    if (e.key === 'F3') {
                        e.preventDefault();
                        quickAddItem();
                    }
                    // F4 untuk focus supplier
                    if (e.key === 'F4') {
                        e.preventDefault();
                        $('#supplier-search').focus();
                    }
                });

                // Auto focus ke input qty/harga setelah tambah barang
                $('#tbretur').on('focus', '.qty, .harga_beli, .harga_jual', function() {
                    $(this).select();
                });

                // Auto-save on blur
                $('#tbretur').on('blur', '.qty, .harga_beli, .harga_jual', function() {
                    updateTotals();
                });

                // Reset supplier saat input dikosongkan
                $('#supplier-search').on('input', function() {
                    $('#supplier_id').val('');
                    $('#kode_supplier').val('');
                    $('#penerimaan-search').val(null).trigger('change');
                    $('#tbretur tbody').empty();
                    updateTotals();
                    numbering();
                });
            });
        </script>
    </x-slot>
</x-app-layout>
