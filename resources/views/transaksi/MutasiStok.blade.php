<x-app-layout>
    <x-slot name="pagetitle">Mutasi Stok</x-slot>
    
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Mutasi Stok</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Mutasi</li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmmutasi">
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-left-right"></i> Stock Transfer Form
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        {{-- Header Form --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="date" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                                    <input type="hidden" name="petugas" value="{{ auth()->user()->name }}">
                                </div>
                            </div>
                        </div>

                        {{-- Unit Selection --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Dari Unit</span>
                                    <select class="form-select" id="unit1" name="unit1" required>
                                        <option value="">Pilih Unit Asal</option>
                                        @foreach ($unit as $item)
                                            <option value="{{ $item->id }}" data-nama="{{ $item->nama_unit }}">
                                                {{ $item->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Ke Unit</span>
                                    <select class="form-select" id="unit2" name="unit2" required>
                                        <option value="">Pilih Unit Tujuan</option>
                                        @foreach ($unit as $item)
                                            <option value="{{ $item->id }}" data-nama="{{ $item->nama_unit }}">
                                                {{ $item->nama_unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Scan Barang --}}
                        <div class="row mb-3" id="scnbarcode" style="display: none">
                            <div class="col-md-12">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barcode</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama barang" autocomplete="off">
                                    <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                                </div>
                                <small class="text-muted">Tekan Enter untuk mencari, F2 untuk auto focus</small>
                            </div>
                        </div>

                        {{-- Table Mutasi --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tbmutasi" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <thead>
                                            <tr class="bg-light">
                                                <th width="5%">#</th>
                                                <th width="15%">Kode</th>
                                                <th width="30%">Nama Barang</th>
                                                <th width="10%">Stok</th>
                                                <th width="10%">Qty Mutasi</th>
                                                <th width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr class="table-secondary">
                                                <th colspan="5" class="text-end fw-bold">Total Item:</th>
                                                <th id="total-item" class="fw-bold text-center">0</th>
                                            </tr>
                                            <tr class="table-success">
                                                <th colspan="5" class="text-end fw-bold">Total Qty:</th>
                                                <th id="total-qty" class="fw-bold text-center">0</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-2" id="empty-table-alert" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Belum ada barang yang ditambahkan. Scan barcode atau ketik nama barang.
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label form-label-sm mb-1">Catatan Mutasi</label>
                                <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Keterangan mutasi..."></textarea>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-warning btn-sm" onclick="clearform();">
                                    <i class="bi bi-x-circle"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-success btn-sm" id="btnsimpan" style="display: none">
                                    <i class="bi bi-floppy-fill"></i> Simpan Mutasi
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
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
            .stok-info {
                font-size: 0.8rem !important;
                color: #6c757d !important;
            }
            .qty-mutasi {
                width: 80px !important;
                text-align: center !important;
            }
            .input-group.input-group-sm {
                height: auto !important;
            }
            .input-group.input-group-sm > .input-group-text {
                height: calc(1.5em + 0.5rem + 2px) !important;
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
            }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/1.3.1/typeahead.bundle.min.js"></script>
        <script>
            let barang = [];
            let rowCounter = 0;
            let mutasiId = null;
            let typeaheadInstance = null;

            function updateTableAlert() {
                const rowCount = $('#tbmutasi tbody tr').length;
                if (rowCount === 0) {
                    $('#empty-table-alert').show();
                } else {
                    $('#empty-table-alert').hide();
                }
                updateTotals();
            }

            function updateTotals() {
                let totalQty = 0;
                let totalItem = 0;
                
                $('#tbmutasi tbody tr').each(function() {
                    const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                    if (qty > 0) {
                        totalQty += qty;
                        totalItem++;
                    }
                });
                
                $('#total-qty').text(totalQty);
                $('#total-item').text(totalItem);
            }

            function numbering(){
                $('#tbmutasi tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                updateTableAlert();
            }

            function addRow(datarow){
                let existingRow = false;
                let existingRowId = null;
                const searchCode = datarow.code || datarow.kode_barang || '';
                
                $('#tbmutasi tbody tr').each(function() {
                    const rowCode = $(this).find('input[name="kode_barang[]"]').val();
                    if(searchCode && rowCode === searchCode) {
                        existingRow = true;
                        existingRowId = $(this).attr('id');
                        
                        // Update quantity jika barang sudah ada
                        const currentQty = parseInt($(this).find('input[name="qty[]"]').val()) || 0;
                        const newQty = currentQty + 1;
                        const maxQty = parseInt($(this).find('input[name="qty[]"]').attr('max')) || 0;
                        
                        if (newQty <= maxQty) {
                            $(this).find('input[name="qty[]"]').val(newQty);
                            updateTotals();
                            
                            // Tampilkan notifikasi sukses
                            showSuccessToast(`Qty ${datarow.text} ditambah menjadi ${newQty}`);
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stok tidak mencukupi!',
                                text: 'Stok maksimal: ' + maxQty,
                                timer: 2000
                            });
                        }
                        
                        // Auto-focus ke barcode setelah update
                        focusBarcodeSearch();
                        return false;
                    }
                });
                
                if(!existingRow){
                    rowCounter++;
                    const str = `<tr data-id="${datarow.id}" class="align-middle" id="row-${rowCounter}">
                        <td class="text-center">${rowCounter}</td>
                        <td>
                            <input type="hidden" name="kode_barang[]" value="${datarow.code || ''}">
                            <input type="hidden" name="nama_barang[]" value="${datarow.text || ''}">
                            <input type="hidden" name="id[]" value="${datarow.id}">
                            ${datarow.code || 'N/A'}
                        </td>
                        <td>${datarow.text || ''}</td>
                        <td class="text-center">
                            <input type="number" readonly value="${datarow.stok || 0}" class="form-control form-control-sm stok-info" style="width: 80px;">
                            <small class="text-muted">tersedia</small>
                        </td>
                        <td class="text-center">
                            <input type="number" value="1" class="form-control form-control-sm qty-mutasi" min="1" 
                                   max="${datarow.stok || 0}" name="qty[]" required>
                            <small class="text-muted">mutasi</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger dellist" onclick="removeRow(${rowCounter})" title="Hapus">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>`;
                    
                    // Barang baru ditambahkan di paling atas
                    $('#tbmutasi tbody').prepend(str);
                    
                    // Update numbering
                    numbering();
                    
                    // Tampilkan notifikasi sukses
                    showSuccessToast(`${datarow.text} ditambahkan`);
                    
                    // Auto-focus ke barcode setelah menambah row baru
                    focusBarcodeSearch();
                }
            }

            function removeRow(rowId) {
                $(`#row-${rowId}`).remove();
                numbering();
                
                // Auto-focus ke barcode setelah hapus
                focusBarcodeSearch();
            }

            function clearform(){
                if ($('#tbmutasi tbody tr').length > 0) {
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
                // Reset form
                $('input[name="date"]').val('');
                $('#unit1').val('');
                $('#unit2').val('');
                $('textarea[name="note"]').val('');
                $('#scnbarcode, #btnsimpan').hide();
                
                // Clear table
                $('#tbmutasi tbody').empty();
                updateTableAlert();
                
                // Reset datepicker to today
                $('.datepicker').datepicker('setDate', new Date());
                
                // Clear barcode search field
                clearBarcodeSearch();
                
                // Auto focus ke barcode
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            function validasi(){
                const unit1 = $('#unit1').val();
                const unit2 = $('#unit2').val();
                
                if(!unit1 || !unit2) {
                    $('#scnbarcode, #btnsimpan').hide();
                    return;
                }
                
                if(unit1 === unit2) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Unit asal dan tujuan tidak boleh sama!'
                    });
                    $('#scnbarcode, #btnsimpan').hide();
                    return;
                }
                
                $('#scnbarcode, #btnsimpan').show();
                focusBarcodeSearch();
            }

            function cetakNotaMutasi(mutasiId) {
                const notaUrl = '{{ route("mutasi.nota", ":id") }}'.replace(':id', mutasiId);
                window.open(notaUrl, '_blank');
            }

            function focusBarcodeSearch() {
                setTimeout(() => {
                    $('#barcode-search').val('').focus();
                    // Clear typeahead suggestions
                    if (typeaheadInstance) {
                        typeaheadInstance.typeahead('val', '');
                    }
                }, 100);
            }

            function clearBarcodeSearch() {
                $('#barcode-search').val('');
                // Clear typeahead suggestions
                if (typeaheadInstance) {
                    typeaheadInstance.typeahead('val', '');
                }
            }

            function showSuccessToast(message) {
                // Simple toast notification
                const toast = $(`<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle-fill me-2"></i> ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>`);
                
                $('body').append(toast);
                const bsToast = new bootstrap.Toast(toast.find('.toast')[0]);
                bsToast.show();
                
                // Remove after hide
                toast.find('.toast').on('hidden.bs.toast', function () {
                    toast.remove();
                });
            }

            $(document).ready(function () {
                let currentRequest = null;
                
                // Set datepicker
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'id'
                }).datepicker('setDate', new Date());

                // Validasi unit
                $('#unit1, #unit2').on('change', function() {
                    validasi();
                });

                // Typeahead untuk barcode
                const barangBloodhound = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.whitespace,
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                        url: '{{ route('mutasi.getbarang') }}?q=%QUERY&unit=%UNIT',
                        replace: function(url, query) {
                            return url.replace('%QUERY', query).replace('%UNIT', $('#unit1').val());
                        },
                        wildcard: '%QUERY'
                    }
                });

                typeaheadInstance = $('#barcode-search').typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 1
                }, {
                    name: 'barang',
                    source: barangBloodhound,
                    display: 'text',
                    templates: {
                        suggestion: function(data) {
                            return `<div><strong>${data.code}</strong> - ${data.text} (Stok: ${data.stok})</div>`;
                        }
                    }
                }).on('typeahead:select', function(ev, suggestion) {
                    addRow(suggestion);
                    // Clear typeahead value after selection
                    $(this).typeahead('val', '');
                }).on('typeahead:close', function(ev) {
                    // Clear suggestions when closing
                    $(this).typeahead('val', '');
                });

                // Enter untuk search barcode - PERBAIKAN UTAMA
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const searchVal = $(this).val().trim();
                        const unit1 = $('#unit1').val();
                        
                        if (!unit1) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian',
                                text: 'Pilih unit asal terlebih dahulu!'
                            });
                            return;
                        }
                        
                        if (searchVal) {
                            // Clear typeahead suggestions
                            $(this).typeahead('val', '');
                            
                            $.ajax({
                                url: '{{ route('mutasi.getbarangbycode') }}',
                                method: 'GET',
                                data: { 
                                    kode: searchVal,
                                    unit: unit1 
                                },
                                dataType: 'json',
                                beforeSend: function() {
                                    if (currentRequest !== null) currentRequest.abort();
                                },
                                success: function(response) { 
                                    addRow(response);
                                    // Clear input field setelah berhasil
                                    $('#barcode-search').val('').typeahead('val', '');
                                },
                                error: function() {
                                    Swal.fire({
                                        title: "Barang tidak ditemukan!",
                                        text: "Barang dengan kode '" + searchVal + "' tidak ditemukan di unit ini.",
                                        icon: "error"
                                    });
                                    // Tetap clear input meski error
                                    $('#barcode-search').val('').typeahead('val', '');
                                }
                            });
                        }
                    }
                });

                // Update totals on change Qty
                $('#tbmutasi').on('input', 'input[name="qty[]"]', function() {
                    const max = parseInt($(this).attr('max')) || 0;
                    const value = parseInt($(this).val()) || 0;
                    
                    if (value > max) {
                        $(this).val(max);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stok tidak mencukupi!',
                            text: 'Stok tersedia: ' + max,
                            timer: 2000
                        });
                    }
                    
                    updateTotals();
                });

                // Focus ke barcode search saat halaman load
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 500);

                // Submit form
                $('#frmmutasi').on('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) { 
                        e.stopPropagation(); 
                        this.classList.add('was-validated');
                        return; 
                    }

                    // Validasi unit
                    const unit1 = $('#unit1').val();
                    const unit2 = $('#unit2').val();
                    
                    if (!unit1 || !unit2) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Unit asal dan tujuan harus dipilih!'
                        });
                        return;
                    }
                    
                    if (unit1 === unit2) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Unit asal dan tujuan tidak boleh sama!'
                        });
                        return;
                    }

                    // Validasi ada barang yang ditambahkan
                    if ($('#tbmutasi tbody tr').length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Tidak ada barang yang ditambahkan!'
                        });
                        $('#barcode-search').focus();
                        return;
                    }

                    // Validasi quantity
                    let qtyError = false;
                    $('input[name="qty[]"]').each(function() {
                        const qty = parseInt($(this).val()) || 0;
                        const max = parseInt($(this).attr('max')) || 0;
                        
                        if (qty <= 0 || qty > max) {
                            qtyError = true;
                            return false;
                        }
                    });
                    
                    if (qtyError) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Quantity harus antara 1 dan stok tersedia!'
                        });
                        return;
                    }

                    const formData = $(this).serialize();
                    
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('mutasi.store') }}',
                        data: formData,
                        dataType: 'json',
                        beforeSend: function() {
                            $('#btnsimpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                        },
                        success: function(response) {
                            if (response && response.id) {
                                mutasiId = response.id;
                                
                                Swal.fire({
                                    position: "top-end", 
                                    icon: "success", 
                                    title: "Mutasi berhasil disimpan", 
                                    showConfirmButton: false, 
                                    timer: 1500
                                }).then(() => {
                                    // Cetak nota mutasi
                                    cetakNotaMutasi(mutasiId);
                                    
                                    // Clear form
                                    doClearForm();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error", 
                                    title: "Oops...", 
                                    text: "Terjadi kesalahan saat menyimpan!"
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
                            $('#btnsimpan').prop('disabled', false).html('<i class="bi bi-floppy-fill"></i> Simpan Mutasi');
                        }
                    });
                });

                // Shortcut keyboard
                $(document).keydown(function(e) {
                    // Ctrl + S untuk simpan
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        $('#frmmutasi').submit();
                    }
                    // Esc untuk batal
                    if (e.key === 'Escape') {
                        clearform();
                    }
                    // F2 untuk focus barcode
                    if (e.key === 'F2') {
                        e.preventDefault();
                        clearBarcodeSearch();
                        $('#barcode-search').focus();
                    }
                });

                // Auto focus ke input qty
                $('#tbmutasi').on('focus', 'input[name="qty[]"]', function() {
                    $(this).select();
                });

                // Click outside to clear barcode search
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#barcode-search').length && 
                        !$(e.target).closest('.tt-menu').length) {
                        clearBarcodeSearch();
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>