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
                                    <button class="btn btn-outline-primary btn-sm px-2 py-0" type="button" id="btn-add-supplier" data-bs-toggle="modal" data-bs-target="#modalSupplier">
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
                                    <span class="input-group-text label-fixed-width">Persen PPN</span>
                                    <input type="number" class="form-control" id="persen-ppn" name="persen_ppn" value="0" step="0.01" min="0" max="100" style="width: 100px;">
                                    <span class="input-group-text">%</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="applyPpnToAll()">
                                        <i class="bi bi-arrow-repeat"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Scan Barang --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barcode</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama" autocomplete="off">
                                    <button class="btn btn-outline-primary btn-sm px-2 py-0" type="button" onclick="quickAddItem()">
                                        <i class="bi bi-plus-lg"></i> Tambah Baru
                                    </button>
                                    <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                                </div>
                                <small class="text-muted">Tekan Enter untuk mencari, F2 untuk auto focus</small>
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
                                                <th width="12%">Kode</th>
                                                <th width="22%">Nama Barang</th>
                                                <th width="8%">Qty</th>
                                                <th width="12%">Harga Beli</th>
                                                <th width="12%">PPN</th>
                                                <th width="13%">Total + PPN</th>
                                                <th width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="table-secondary">
                                                <th colspan="5" class="text-end fw-bold">Subtotal:</th>
                                                <th id="subtotal-ppn" class="fw-bold text-end">0</th>
                                                <th id="subtotal" class="fw-bold text-end">0</th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-success">
                                                <th colspan="5" class="text-end fw-bold">Grand Total:</th>
                                                <th id="grandtotal-ppn" class="fw-bold text-end">0</th>
                                                <th id="grandtotal" class="fw-bold text-end">0</th>
                                                <th></th>
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
                            
                            <div class="col-md-4">
                                <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="harga_beli" id="add-harga-beli" step="0.01" min="0" required>
                                <div class="invalid-feedback">Harga beli wajib diisi</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="harga_jual" id="add-harga-jual" step="0.01" min="0" required>
                                <div class="invalid-feedback">Harga jual wajib diisi</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-control form-control-sm" name="type" id="add-type">
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
                            <i class="bi bi-save"></i> Simpan & Tambah ke Penerimaan
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
            .total-beli, .total-ppn {
                font-weight: 600 !important;
            }
            .total-beli {
                color: #198754 !important;
            }
            .total-ppn {
                color: #0d6efd !important;
            }
            #empty-table-alert {
                font-size: 0.875rem !important;
                padding: 0.5rem 1rem !important;
            }
            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                opacity: 1 !important;
            }
            /* Perbaikan untuk input-group */
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

        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.1/typeahead.bundle.min.js"></script>
        <script>
            let barang = [];
            let supplierList = [];
            let rowCounter = 0;
            let globalPpnPersen = 0;

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

            function calculatePpn(hargaBeli, qty, ppnPersen) {
                const subtotal = hargaBeli * qty;
                const ppn = (subtotal * ppnPersen) / 100;
                const total = subtotal + ppn;
                return { subtotal, ppn, total };
            }

            function updateTotals() {
                let grandSubtotal = 0;
                let grandPpn = 0;
                let grandTotal = 0;
                
                $('#tbterima tbody tr').each(function() {
                    const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                    const hargaBeli = parseFloat($(this).find('input[name="harga_beli[]"]').val()) || 0;
                    const ppnPersen = parseFloat($(this).find('input[name="ppn_persen[]"]').val()) || 0;
                    
                    const calculation = calculatePpn(hargaBeli, qty, ppnPersen);
                    
                    $(this).find('.subtotal-item').text(formatCurrency(calculation.subtotal));
                    $(this).find('.ppn-item').text(formatCurrency(calculation.ppn));
                    $(this).find('.total-item').text(formatCurrency(calculation.total));
                    
                    grandSubtotal += calculation.subtotal;
                    grandPpn += calculation.ppn;
                    grandTotal += calculation.total;
                });
                
                $('#subtotal').text(formatCurrency(grandSubtotal));
                $('#subtotal-ppn').text(formatCurrency(grandPpn));
                $('#grandtotal').text(formatCurrency(grandTotal));
                $('#grandtotal-ppn').text(formatCurrency(grandPpn));
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(amount);
            }

            function addRow(datarow, ppnPersen = null){
                let existingRow = false;
                const searchCode = datarow.code || datarow.kode_barang || '';
                
                $('#tbterima tbody tr').each(function() {
                    const rowCode = $(this).find('input[name="kode_barang[]"]').val();
                    if(searchCode && rowCode === searchCode) {
                        existingRow = true;
                        // Update quantity jika barang sudah ada
                        const qtyInput = $(this).find('input[name="qty[]"]');
                        const currentQty = parseInt(qtyInput.val()) || 0;
                        qtyInput.val(currentQty + 1);
                        
                        // Update harga jika berbeda
                        const hargaBeliInput = $(this).find('input[name="harga_beli[]"]');
                        const hargaJualInput = $(this).find('input[name="harga_jual[]"]');
                        
                        if(datarow.harga_beli && datarow.harga_beli > 0) {
                            hargaBeliInput.val(datarow.harga_beli);
                        }
                        if(datarow.harga_jual && datarow.harga_jual > 0) {
                            hargaJualInput.val(datarow.harga_jual);
                        }
                        
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
                    const usePpnPersen = ppnPersen !== null ? ppnPersen : globalPpnPersen;
                    const calculation = calculatePpn(datarow.harga_beli || 0, 1, usePpnPersen);
                    
                    const str = `<tr data-id="${datarow.id || 'new-' + rowCounter}" class="align-middle" id="row-${rowCounter}">
                        <td class="text-center">${rowCounter}</td>
                        <td>
                            <input type="hidden" name="kode_barang[]" value="${datarow.code || datarow.kode_barang || ''}">
                            <input type="hidden" name="nama_barang[]" value="${datarow.text || datarow.nama_barang || ''}">
                            <input type="hidden" name="barang_id[]" value="${datarow.id || ''}">
                            <input type="hidden" name="harga_jual[]" value="${datarow.harga_jual || 0}">
                            <input type="hidden" name="satuan[]" value="${datarow.satuan || ''}">
                            <input type="hidden" name="kategori[]" value="${datarow.kategori || ''}">
                            ${datarow.code || datarow.kode_barang || 'N/A'}
                        </td>
                        <td>${datarow.text || datarow.nama_barang || ''}</td>
                        <td>
                            <input type="number" value="1" class="form-control form-control-sm qty" min="1" name="qty[]" style="width: 70px;" required>
                        </td>
                        <td>
                            <input type="number" value="${datarow.harga_beli || 0}" step="0.01" class="form-control form-control-sm harga_beli" name="harga_beli[]" style="width: 100px;" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" value="${usePpnPersen}" step="0.01" class="form-control form-control-sm ppn-persen" name="ppn_persen[]" style="width: 70px;" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="ppn-item text-primary">${formatCurrency(calculation.ppn)}</small>
                        </td>
                        <td class="text-end">
                            <div class="total-item fw-bold total-beli">${formatCurrency(calculation.total)}</div>
                            <small class="text-muted">Sub: ${formatCurrency(calculation.subtotal)}</small>
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

            function applyPpnToAll() {
                const ppnPersen = parseFloat($('#persen-ppn').val()) || 0;
                globalPpnPersen = ppnPersen;
                
                $('#tbterima tbody tr').each(function() {
                    $(this).find('input.ppn-persen').val(ppnPersen);
                });
                
                updateTotals();
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
                $('#persen-ppn').val('0');
                $('#tempo-field').hide();
                globalPpnPersen = 0;
                
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
                $('#modalAddBarang').modal('show');
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

                // Set global PPN dari input
                globalPpnPersen = parseFloat($('#persen-ppn').val()) || 0;

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
                        url: '{{ route('penerimaan.store-barang') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btn-save-barang').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response.success) {
                                // Tambahkan barang ke tabel penerimaan
                                addRow(response.barang, globalPpnPersen);
                                
                                // Tutup modal
                                $('#modalAddBarang').modal('hide');
                                
                                // Reset form
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
                            $('#btn-save-barang').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan & Tambah ke Penerimaan');
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
                    addRow(suggestion, globalPpnPersen);
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
                                    addRow(response, globalPpnPersen);
                                    // Auto clear input
                                    $('#barcode-search').val('');
                                },
                                error: function() {
                                    // Barang tidak ditemukan, tampilkan konfirmasi untuk tambah baru
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
                                            $('#modalAddBarang').modal('show');
                                        } else if (result.isDenied) {
                                            // Cari dengan nama
                                            $.ajax({
                                                url: '{{ route('penerimaan.getbarang') }}',
                                                method: 'GET',
                                                data: { q: searchVal },
                                                dataType: 'json',
                                                success: function(data) {
                                                    if (data && data.length > 0) {
                                                        // Tampilkan suggestion
                                                        // Typeahead sudah menanganinya
                                                    } else {
                                                        $('#add-nama-barang').val(searchVal);
                                                        $('#add-kode-barang').val('');
                                                        $('#modalAddBarang').modal('show');
                                                    }
                                                }
                                            });
                                        }
                                        // Auto clear input
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

                $('.datepicker-tempo').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    startDate: '+1d',
                    language: 'id'
                });

                // Update totals on change Qty / Harga Beli / PPN
                $('#tbterima').on('input', '.qty, .harga_beli, .ppn-persen', function() {
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

                    // Serialize form data dengan menambahkan hidden fields
                    const formData = $(this).serializeArray();
                    
                    // Tambahkan persen PPN global ke form data
                    formData.push({name: 'persen_ppn_global', value: globalPpnPersen});
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('penerimaan.store') }}',
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
                    // F3 untuk tambah barang baru
                    if (e.key === 'F3') {
                        e.preventDefault();
                        quickAddItem();
                    }
                });

                // Auto focus ke input harga/qty setelah tambah barang
                $('#tbterima').on('focus', '.harga_beli, .qty, .ppn-persen', function() {
                    $(this).select();
                });

                // Auto-save on blur untuk qty dan harga
                $('#tbterima').on('blur', '.qty, .harga_beli, .ppn-persen', function() {
                    updateTotals();
                });

                // Apply PPN global ketika diubah
                $('#persen-ppn').on('change', function() {
                    globalPpnPersen = parseFloat($(this).val()) || 0;
                });
            });
        </script>
    </x-slot>
</x-app-layout>