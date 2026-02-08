<x-app-layout>
    <x-slot name="pagetitle">Transaksi Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Bengkel - {{ Auth::user()->unit->nama_unit ?? 'Unit' }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Bengkel</li>
                        <li class="breadcrumb-item active">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form id="formTransaksi" autocomplete="off" class="needs-validation" novalidate>
                @csrf
                <div class="card card-success card-outline mb-4">
                    <div class="card-header p-2">
                        <div class="alert alert-success ps-2 p-0 mb-0" role="alert" id="detailcus" style="display: none"></div>
                    </div>
                    <div class="card-body p-3">

                        {{-- HEADER --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="tanggal" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2 align-items-center">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" value="" id="flexCheckDefault" checked>
                                        <label for="flexCheckDefault" class="mb-0">Anggota</label>
                                    </div>
                                    <input type="text" class="form-control" id="customer" name="customer" required autocomplete="off" placeholder="Cari anggota...">
                                    <input type="hidden" id="idcustomer" name="idcustomer">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Petugas</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" name="kasir" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Barang</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau ketik nama">
                                    <span class="input-group-text bg-primary"><i class="fa-solid fa-barcode text-white"></i></span>
                                </div>
                            </div>

                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Invoice</label>
                                    <div class="fs-6 fw-bold txtinv">{{ $invoice }}</div>
                                </div>
                                <div class="fs-3 fw-bold text-success topgrandtotal">Rp. 0</div>
                            </div>
                        </div>

                        {{-- TWO COLUMN LAYOUT --}}
                        <div class="row mb-3">
                            {{-- JASA COLUMN --}}
                            <div class="col-md-6 pe-3">
                                <div class="card h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Jasa Bengkel</h6>
                                        <span class="badge bg-warning">Cicilan 1x</span>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped table-bordered mb-0" id="tabelJasa" style="font-size: small;">
                                                <thead>
                                                    <tr>
                                                        <th width="70%">Nama Jasa</th>
                                                        <th width="20%">Harga</th>
                                                        <th width="10%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Row akan ditambahkan via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" id="tambahJasa" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus"></i> Tambah Jasa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BARANG COLUMN --}}
                            <div class="col-md-6 ps-3">
                                <div class="card h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0">Sparepart</h6>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-warning" id="badgeCicilan0" style="display: none">Cicilan 1x</span>
                                            <span class="badge bg-info" id="badgeCicilan1" style="display: none">Cicilan fleksibel</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="input-group input-group-sm mb-2"> 
                                            <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau cari barang">
                                            <span class="input-group-text bg-primary"><i class="fa-solid fa-barcode text-white"></i></span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped table-bordered mb-0" id="tabelBarang" style="font-size: small;">
                                                <thead>
                                                    <tr>
                                                        <th width="40%">Barang</th>
                                                        <th width="10%">Stok</th>
                                                        <th width="15%">Qty</th>
                                                        <th width="20%">Harga</th>
                                                        <th width="10%">Total</th>
                                                        <th width="5%"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Row akan ditambahkan via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" id="tambahBarang" class="btn btn-sm btn-success">
                                                <i class="bi bi-plus"></i> Tambah Barang
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TOTAL --}}
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="subtotal" id="subtotal" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="diskon" id="diskon" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="grandtotal" id="grandtotal" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Metode</span>
                                    <select class="form-select form-select-sm" id="metodebayar" name="metodebayar" required>
                                        <option value="tunai" selected>Tunai</option>
                                        <option value="cicilan">Cicilan</option>
                                    </select>
                                </div>
                                
                                <div class="input-group input-group-sm mb-2 fieldcicilan" style="display: none">
                                    <span class="input-group-text label-fixed-width">Jml.Cicilan</span>
                                    <select class="form-select form-select-sm" id="jmlcicilan" name="jmlcicilan"></select>
                                </div>
                                <div class="input-group input-group-sm mb-2 clmetode">
                                    <span class="input-group-text label-fixed-width">Dibayar</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" min="0" required>
                                </div>
                                <div class="input-group input-group-sm mb-2 clmetode">
                                    <span class="input-group-text label-fixed-width">Kembali</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="kembali" id="kembali" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-3"> 
                                    <span class="input-group-text label-fixed-width">Catatan</span> 
                                    <textarea class="form-control" name="note" rows="3" placeholder="Keterangan tambahan..."></textarea> 
                                </div>
                                <div class="alert alert-info py-2" id="infoCicilan" style="display: none; font-size: 0.85rem;">
                                    <i class="bi bi-info-circle"></i> 
                                    <span id="textInfoCicilan"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-auto d-flex gap-2">
                                <button type="button" class="btn btn-warning" onclick="clearform();">
                                    <i class="bi bi-arrow-clockwise"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-floppy-fill"></i> Simpan
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
        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Typeahead dropdown menu */
        .tt-menu {
            width: 100%;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 1050;
            max-height: 300px;
            overflow-y: auto;
        }

        /* Each suggestion */
        .tt-suggestion {
            padding: 0.5rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .tt-suggestion:hover {
            background-color: #f8f9fa;
        }
        
        .tt-suggestion:last-child {
            border-bottom: none;
        }
        
        .tt-cursor {
            background-color: #e7f3ff;
        }
        
        /* Select2 custom styling dengan ukuran lebih besar */
        .select2-results__option {
            padding: 12px 15px;
            font-size: 14px;
            min-height: 60px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .select2-results__option:last-child {
            border-bottom: none;
        }
        
        .select2-results__option--highlighted {
            background-color: #e7f3ff !important;
            color: #333 !important;
        }
        
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #ced4da;
            font-size: 14px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 15px;
            font-size: 14px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        
        .select2-container--default .select2-results > .select2-results__options {
            max-height: 350px;
        }
        
        /* Custom styles untuk select2 dropdown yang lebih besar */
        .select2-container--default .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        /* Template custom untuk barang */
        .barang-option {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
        }
        
        .barang-info {
            flex: 1;
            min-width: 0;
        }
        
        .barang-detail {
            text-align: right;
            margin-left: 15px;
            min-width: 120px;
        }
        
        .barang-code {
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
        }
        
        .barang-name {
            color: #495057;
            font-size: 12px;
            margin-top: 2px;
            display: block;
            white-space: normal;
            line-height: 1.3;
        }
        
        .barang-price {
            font-weight: 700;
            color: #28a745;
            font-size: 13px;
        }
        
        .barang-stock {
            font-size: 11px;
            margin-top: 3px;
        }
        
        .barang-stock.success {
            color: #28a745;
        }
        
        .barang-stock.danger {
            color: #dc3545;
        }
        
        .barang-cicilan {
            font-size: 11px;
            margin-top: 3px;
        }
        
        /* Template custom untuk jasa */
        .jasa-option {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
        }
        
        .jasa-name {
            font-weight: 500;
            color: #2c3e50;
            font-size: 13px;
        }
        
        .jasa-price {
            font-weight: 700;
            color: #28a745;
            font-size: 13px;
        }
        
        /* Custom styles */
        .label-fixed-width {
            min-width: 100px;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
        }
        
        .table td {
            font-size: 13px;
            vertical-align: middle;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            padding: 0.75rem 1rem;
        }
        
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .select2-container {
                width: 100% !important;
            }
            
            .label-fixed-width {
                min-width: 80px;
            }
            
            .barang-detail {
                min-width: 100px;
            }
            
            .col-md-6 {
                padding-right: 0.5rem !important;
                padding-left: 0.5rem !important;
                margin-bottom: 1rem;
            }
            
            .ps-3, .pe-3 {
                padding-right: 0.5rem !important;
                padding-left: 0.5rem !important;
            }
            
            .table-responsive {
                max-height: 250px;
            }
            
            .barang-option, .jasa-option {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .barang-detail {
                margin-left: 0;
                margin-top: 5px;
                text-align: left;
            }
        }
        
        /* Custom untuk select2 di tabel */
        #tabelBarang .select2-container,
        #tabelJasa .select2-container {
            width: 100% !important;
        }
        
        /* Ukuran dropdown lebih besar */
        .select2-results__option {
            min-height: 70px;
            padding: 10px 15px;
        }
        
        /* Loading state */
        .select2-results__message {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }
        
        /* Scrollbar untuk dropdown */
        .select2-results__options::-webkit-scrollbar {
            width: 6px;
        }
        
        .select2-results__options::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .select2-results__options::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        .select2-results__options::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script>
            var globtot = 0;
            var barang = [];
            var existingProducts = {};
            var typeaheadInstance = null;
            var enterPressed = false;
            var users = [];
            
            function loader(onoff) {
                if(onoff)
                    $('.app-wrapper').waitMe({effect : 'bounce', text : '', bg : 'rgba(255,255,255,0.7)', color : '#000', waitTime : -1, textPos : 'vertical'});
                else
                    $('.app-wrapper').waitMe('hide');
            }

            function formatRupiahWithDecimal(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }

            function kalkulasi() {
                let subtotal = 0;
                
                // Hitung total jasa
                $('#tabelJasa .harga').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                
                // Hitung total barang
                $('#tabelBarang .total').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                
                window.globtot = subtotal * (1 - ($('#diskon').val() / 100));
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(window.globtot);
                $('.topgrandtotal').text(formatRupiahWithDecimal(window.globtot));
                $('#dibayar').prop('min', window.globtot);
                $('#kembali').val(($('#dibayar').val() || 0) - window.globtot);
                
                // Update info cicilan jika metode cicilan dipilih
                if($('#metodebayar').val() == 'cicilan') {
                    updateCicilanOptions();
                    updateInfoCicilan();
                }
            }

            function cekCicilan() {
                let jmlCicilan = parseInt($('#jmlcicilan').val()) || 1;
                
                // Cek apakah ada jasa (selalu cicilan 1x)
                let hasJasa = $('#tabelJasa tbody tr').length > 0;
                
                // Cek barang dengan kategori cicilan 0
                let hasBarangCicilan0 = false;
                $('#tabelBarang tbody tr').each(function() {
                    let selectElement = $(this).find('.select2-barang');
                    let selectedOption = selectElement.find('option:selected');
                    let kategoriCicilan = selectedOption.data('cicilan') || 1;
                    
                    if(kategoriCicilan == 0) {
                        hasBarangCicilan0 = true;
                    }
                });
                
                // Update badge visibility
                if(hasBarangCicilan0) {
                    $('#badgeCicilan0').show();
                } else {
                    $('#badgeCicilan0').hide();
                }
                
                if(!hasBarangCicilan0 && $('#tabelBarang tbody tr').length > 0) {
                    $('#badgeCicilan1').show();
                } else {
                    $('#badgeCicilan1').hide();
                }
                
                // Jika ada jasa atau barang cicilan 0, maksimal cicilan adalah 1
                if((hasJasa || hasBarangCicilan0) && jmlCicilan > 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Ada jasa/barang dengan cicilan 1x, cicilan diubah menjadi 1',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#jmlcicilan').val(1);
                    return false;
                }
                return true;
            }

            function updateCicilanOptions() {
                let maxcicil = 0;
                if(window.globtot <= 1000000) { maxcicil = 3; }
                else if(window.globtot > 1000000 && window.globtot <= 2000000) { maxcicil = 5; }
                else if(window.globtot > 2000000 && window.globtot <= 3000000) { maxcicil = 10; }
                else if(window.globtot > 3000000 && window.globtot <= 4000000) { maxcicil = 15; }
                else if(window.globtot > 4000000 && window.globtot <= 5000000) { maxcicil = 20; }
                else if(window.globtot > 5000000) { maxcicil = 25; }

                let str = '';
                for (let index = 1; index <= maxcicil; index++) {
                    str += `<option value='${index}'>${index}x</option>`;
                }
                $('#jmlcicilan').html(str);
                
                // Validasi cicilan
                cekCicilan();
            }

            function updateInfoCicilan() {
                let hasJasa = $('#tabelJasa tbody tr').length > 0;
                let hasBarangCicilan0 = false;
                
                $('#tabelBarang tbody tr').each(function() {
                    let selectElement = $(this).find('.select2-barang');
                    let selectedOption = selectElement.find('option:selected');
                    let kategoriCicilan = selectedOption.data('cicilan') || 1;
                    
                    if(kategoriCicilan == 0) {
                        hasBarangCicilan0 = true;
                    }
                });
                
                if(hasJasa || hasBarangCicilan0) {
                    $('#infoCicilan').show();
                    $('#textInfoCicilan').text('Jasa dan barang tertentu hanya dapat dicicil 1x');
                } else {
                    $('#infoCicilan').hide();
                }
            }

            function invoice() {
                $.ajax({
                    url: '{{ route('bengkel.getinv') }}',
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function(xhr) { loader(true); },
                    success: function(response) {
                        $('.txtinv').text(response);
                        loader(false);
                    },
                    error: function(xhr, status, error) {
                        loader(false);
                    }
                });
            }

            function clearform() {
                $('#customer').val('');
                $('#idcustomer').val('');
                $('#detailcus').html('').hide();
                $('.topgrandtotal').text('Rp. 0');
                $('#subtotal').val(0);
                $('#diskon').val(0);
                $('#grandtotal').val(0);
                $('#dibayar').val(0);
                $('#kembali').val(0);
                $('#tabelJasa tbody').empty();
                $('#tabelBarang tbody').empty();
                $('#metodebayar').val('tunai').trigger('change');
                $('textarea[name="note"]').val('');
                $('#infoCicilan').hide();
                $('#badgeCicilan0').hide();
                $('#badgeCicilan1').hide();
                
                existingProducts = {};
                
                $('.datepicker').datepicker('setDate', new Date());
                
                clearBarcodeSearch();
                
                invoice();
            }

            function clearBarcodeSearch() {
                $('#barcode-search').val('');
                if (typeaheadInstance) {
                    $('#barcode-search').typeahead('val', '');
                }
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            // Fungsi untuk menambah produk yang sama (update qty)
            function incrementExistingProduct(idbarang, rowElement, additionalQty = 1) {
                const currentQty = parseInt(rowElement.find('.qty').val()) || 0;
                const stok = parseInt(rowElement.find('.stok').val()) || 0;
                const harga = parseFloat(rowElement.find('.harga').val()) || 0;
                
                let newQty = currentQty + additionalQty;
                
                if (stok > 0 && newQty > stok) {
                    newQty = stok;
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Qty melebihi stok!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
                
                rowElement.find('.qty').val(newQty).trigger('input');
            }

            function validateStock(datarow) {
                if (datarow.stok === 0 || datarow.stok <= 0) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Stok habis!',
                        text: `Produk "${datarow.text}" tidak tersedia (stok: ${datarow.stok})`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return false;
                }
                return true;
            }

            // === FUNGSI TYPEAHEAD ANGGOTA ===
            function activateTypeahead() {
                $('#detailcus').html('');
                $('#customer').typeahead({
                    minLength: 2,
                    displayText: function(item) {
                        return item.nomor_anggota + ' - ' + item.name;
                    },
                    source: function(query, process) {
                        return $.get('{{ route('bengkel.getanggota') }}', { query: query }, function(data) {
                            return process(data);
                        });
                    },
                    afterSelect: function(item) {
                        // hitung persentase hutang
                        let persentase = 0;
                        if (item.limit_hutang > 0) {
                            persentase = (item.total_pokok / (item.limit_hutang+item.total_pokok)) * 100;
                        }

                        // tentukan warna alert
                        let alertClass = 'alert-success';
                        if (persentase >= 50 && persentase <= 75) {
                            alertClass = 'alert-warning';
                        } else if (persentase > 75) {
                            alertClass = 'alert-danger';
                        }

                        // tampilkan detail dengan bold text
                        $('#detailcus')
                            .removeClass('alert-success alert-warning alert-danger')
                            .addClass(alertClass + ' text-dark')
                            .addClass(alertClass)
                            .html(`
                                <table class="mb-0">
                                    <tr><td class="pe-2">Nomor Anggota</td><td>:<b> ${item.nomor_anggota} - ${item.name}</b></td></tr>
                                    <tr><td>Jumlah Hutang</td><td>: ${formatRupiahWithDecimal(item.total_pokok)}</td></tr>
                                    <tr><td>Sisa Limit Hutang</td><td>: ${formatRupiahWithDecimal(item.limit_hutang)}</td></tr>
                                </table>
                            `)
                            .show();
                        $('#idcustomer').val(item.id);
                        $('#customer').val(item.nomor_anggota + ' - ' + item.name);
                    }
                });
            }
            
            function destroyTypeahead() {
                $('#customer').typeahead('destroy');
                $('#detailcus').html('').hide();
            }

            // === INIT SELECT2 JASA ===
            function initSelect2Jasa(context) {
                $(context).find('.select2-jasa').select2({
                    placeholder: 'Pilih Jasa',
                    width: '100%',
                    dropdownCssClass: 'big-dropdown',
                    ajax: {
                        url: "{{ route('bengkel.getjasa') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data })
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        return $(`
                            <div class="jasa-option">
                                <div>
                                    <div class="jasa-name">${data.text}</div>
                                </div>
                                <div class="jasa-price">
                                    ${formatRupiahWithDecimal(data.harga || 0)}
                                </div>
                            </div>
                        `);
                    },
                    templateSelection: function(data) {
                        return data.text || data.code;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    $(this).closest('tr').find('.harga').val(data.harga || 0);
                    kalkulasi();
                    
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                        updateInfoCicilan();
                    }
                });
            }

            // === INIT SELECT2 BARANG ===
            function initSelect2Barang(context) {
                $(context).find('.select2-barang').select2({
                    placeholder: 'Pilih Barang',
                    width: '100%',
                    dropdownCssClass: 'big-dropdown',
                    ajax: {
                        url: "{{ route('bengkel.getbarang') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: function(data) {
                            return {
                                results: data.map(b => ({
                                    id: b.id, 
                                    code: b.code,
                                    text: b.text, 
                                    harga_jual: b.harga_jual, 
                                    stok: b.stok,
                                    kategori_cicilan: b.kategori_cicilan || 1
                                }))
                            };
                        }
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        let stokClass = data.stok > 0 ? 'success' : 'danger';
                        let stokText = data.stok > 0 ? `Stok: ${data.stok}` : `Stok: ${data.stok} (Habis)`;
                        let cicilanText = data.kategori_cicilan == 0 ? 
                            '<div class="barang-cicilan"><span class="badge bg-warning">Cicilan 1x</span></div>' : 
                            '<div class="barang-cicilan"><span class="badge bg-info">Cicilan fleksibel</span></div>';
                        
                        return $(`
                            <div class="barang-option">
                                <div class="barang-info">
                                    <div class="barang-code">${data.code}</div>
                                    <div class="barang-name">${data.text}</div>
                                    <div class="barang-stock ${stokClass}">${stokText}</div>
                                    ${cicilanText}
                                </div>
                                <div class="barang-detail">
                                    <div class="barang-price">${formatRupiahWithDecimal(data.harga_jual || 0)}</div>
                                </div>
                            </div>
                        `);
                    },
                    templateSelection: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        return data.text;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let tr = $(this).closest('tr');
                    
                    // VALIDASI: Cek stok
                    if (data.stok === 0 || data.stok <= 0) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Stok habis!',
                            text: `Produk "${data.text}" tidak tersedia`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $(this).val(null).trigger('change');
                        return;
                    }
                    
                    // Cek apakah produk sudah ada di tabel
                    if (data.id && existingProducts[data.id]) {
                        incrementExistingProduct(data.id, existingProducts[data.id]);
                        tr.remove();
                        numbering();
                        clearBarcodeSearch();
                        return;
                    }
                    
                    // Set data cicilan pada option
                    $(this).find('option:selected').attr('data-cicilan', data.kategori_cicilan || 1);
                    
                    tr.find('.harga').val(data.harga_jual || 0);
                    tr.find('.stok').val(data.stok || 0);
                    tr.find('.stok-display').text(data.stok || 0);
                    tr.find('.harga-display').text(formatRupiahWithDecimal(data.harga_jual || 0));
                    tr.find('.qty').val(1).attr('max', data.stok || 999).trigger('input');
                    tr.find('.idbarang').val(data.id);
                    
                    // Simpan ke existingProducts
                    if (data.id && !existingProducts[data.id]) {
                        existingProducts[data.id] = tr;
                    }
                    
                    kalkulasi();
                    
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                        updateInfoCicilan();
                    }
                    
                    clearBarcodeSearch();
                    
                }).on('select2:clear', function() {
                    let tr = $(this).closest('tr');
                    let idbarang = tr.find('.idbarang').val();
                    
                    // Hapus dari existingProducts jika ada
                    if (idbarang && existingProducts[idbarang]) {
                        delete existingProducts[idbarang];
                    }
                    
                    tr.find('.harga').val(0);
                    tr.find('.stok').val(0);
                    tr.find('.stok-display').text(0);
                    tr.find('.harga-display').text('Rp. 0');
                    tr.find('.qty').val(0);
                    tr.find('.total').val(0);
                    kalkulasi();
                    
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                        updateInfoCicilan();
                    }
                });
            }

            // Add barang row from barcode search
            function addBarangRow(datarow) {
                // VALIDASI: Cek stok
                if (datarow.stok === 0 || datarow.stok <= 0) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Stok habis!',
                        text: `Produk "${datarow.text}" tidak tersedia`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    clearBarcodeSearch();
                    return;
                }
                
                // Cek apakah produk sudah ada di tabel
                if (datarow.id && existingProducts[datarow.id]) {
                    incrementExistingProduct(datarow.id, existingProducts[datarow.id]);
                    clearBarcodeSearch();
                    return;
                }
                
                let row = $(`
                    <tr>
                        <td>
                            <select name="idbarang[]" class="form-control select2-barang" required>
                                <option value="${datarow.id}" selected data-cicilan="${datarow.kategori_cicilan || 1}">${datarow.text}</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <span class="stok-display">${datarow.stok || 0}</span>
                            <input type="hidden" class="stok" value="${datarow.stok || 0}">
                        </td>
                        <td>
                            <input type="number" name="qty[]" class="form-control form-control-sm qty" value="1" min="1" max="${datarow.stok || 999}">
                            <input type="hidden" name="harga_jual[]" class="harga" value="${datarow.harga_jual || 0}">
                        </td>
                        <td class="text-end harga-display">${formatRupiahWithDecimal(datarow.harga_jual || 0)}</td>
                        <td class="text-end total">${datarow.harga_jual || 0}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm hapus-baris"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `);
                $('#tabelBarang tbody').append(row);
                initSelect2Barang(row);
                kalkulasi();
                
                // Simpan ke existingProducts
                if (datarow.id && !existingProducts[datarow.id]) {
                    existingProducts[datarow.id] = row;
                }
                
                clearBarcodeSearch();
            }

            function removeProductRow(row) {
                let idbarang = row.find('.idbarang').val();
                
                // Hapus dari existingProducts jika ada
                if (idbarang && existingProducts[idbarang]) {
                    delete existingProducts[idbarang];
                }
                
                row.remove();
                kalkulasi();
                
                if($('#metodebayar').val() == 'cicilan') {
                    cekCicilan();
                    updateInfoCicilan();
                }
            }

            $(document).ready(function() {
                // Initialize datepicker
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());

                // Payment method change handler
                $('#metodebayar').on('change', function() {
                    if($(this).val() == 'cicilan'){
                        $('.fieldcicilan').show();
                        
                        // Cek cicilan
                        if(!cekCicilan()) {
                            $('#jmlcicilan').val(1);
                        }
                        
                        updateCicilanOptions();
                        $('.clmetode').hide().find('input, select').prop('required', false).val('');
                        $('#flexCheckDefault')
                            .prop('checked', true)
                            .off('click.prevent')
                            .on('click.prevent', function(e) {
                                e.preventDefault();
                            });
                    } else {
                        $('.fieldcicilan').hide();
                        $('#jmlcicilan').html('');
                        $('.clmetode').show().find('input, select').prop('required', true);
                        $('#flexCheckDefault').off('click.prevent');
                        $('#infoCicilan').hide();
                    }
                });

                // Member checkbox handler
                $('#flexCheckDefault').on('change', function() {
                    if ($(this).is(':checked')) {
                        activateTypeahead();
                        if($('#idcustomer').val() === '') {
                            $('#customer').val('').prop('readonly', false);
                        }
                    } else {
                        destroyTypeahead();
                        if($('#idcustomer').val() === '') {
                            $('#customer').val('').prop('readonly', false);
                        }
                    }
                });

                // Nonaktifkan Enter di seluruh form
                $(window).keydown(function(event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        return false;
                    }
                });
                
                // Kecuali untuk input barcode
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === "Enter") {
                        // Biarkan event Enter berjalan untuk input barcode
                        return true;
                    }
                });

                // Barcode search typeahead
                typeaheadInstance = $('#barcode-search').typeahead({
                    minLength: 1,
                    highlight: true,
                    source: function(query, process) {
                        $.ajax({
                            url: '{{ route('bengkel.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function(data) {
                                barang = data;
                                let suggestions = data.map(function(item) {
                                    return {
                                        id: item.id,
                                        code: item.code,
                                        text: item.text,
                                        harga_jual: item.harga_jual,
                                        stok: item.stok,
                                        kategori_cicilan: item.kategori_cicilan,
                                        display: `${item.code} - ${item.text} (Stok: ${item.stok}) - ${formatRupiahWithDecimal(item.harga_jual)}`
                                    };
                                });
                                process(suggestions);
                            }
                        });
                    },
                    displayText: function(item) {
                        return item.display || item.text;
                    },
                    afterSelect: function(item) {
                        setTimeout(() => {
                            clearBarcodeSearch();
                        }, 10);
                    },
                    updater: function(item) {
                        if (enterPressed) {
                            enterPressed = false;
                            return '';
                        }
                        
                        addBarangRow(item);
                        return '';
                    }
                });

                // Barcode search enter key
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        let barcode = $(this).val().trim();
                        
                        enterPressed = true;
                        
                        if(barcode) {
                            $.ajax({
                                url: '{{ route('bengkel.getbarangbycode') }}',
                                method: 'GET',
                                data: { kode: barcode },
                                dataType: 'json',
                                beforeSend: function() { loader(true); },
                                success: function(response) {
                                    if (response.stok === 0 || response.stok <= 0) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "warning",
                                            title: "Stok habis!",
                                            text: `Produk "${response.text}" tidak tersedia`,
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        clearBarcodeSearch();
                                        loader(false);
                                        return;
                                    }
                                    
                                    addBarangRow(response);
                                    loader(false);
                                    clearBarcodeSearch();
                                },
                                error: function() {
                                    Swal.fire({
                                        position: "top-end",
                                        icon: "error",
                                        title: "Barang tidak ditemukan!",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    clearBarcodeSearch();
                                    loader(false);
                                }
                            });
                        }
                        
                        setTimeout(() => {
                            enterPressed = false;
                        }, 100);
                    }
                });

                // Add service row
                $('#tambahJasa').on('click', function() {
                    let row = $(`
                        <tr>
                            <td>
                                <select name="jasa_id[]" class="form-control select2-jasa" required></select>
                            </td>
                            <td>
                                <input type="number" name="jasa_harga[]" class="form-control form-control-sm harga" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm hapus-baris"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    `);
                    $('#tabelJasa tbody').append(row);
                    initSelect2Jasa(row);
                });

                // Add part row
                $('#tambahBarang').on('click', function() {
                    let row = $(`
                        <tr>
                            <td>
                                <select name="idbarang[]" class="form-control select2-barang" required></select>
                            </td>
                            <td class="text-center">
                                <span class="stok-display">0</span>
                                <input type="hidden" class="stok" value="0">
                            </td>
                            <td>
                                <input type="number" name="qty[]" class="form-control form-control-sm qty" value="1" min="1">
                                <input type="hidden" name="harga_jual[]" class="harga" value="0">
                            </td>
                            <td class="text-end harga-display">Rp. 0</td>
                            <td class="text-end total">0</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm hapus-baris"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    `);
                    $('#tabelBarang tbody').append(row);
                    initSelect2Barang(row);
                });

                // Quantity change handler
                $(document).on('input', '.qty', function() {
                    let tr = $(this).closest('tr');
                    let harga = parseFloat(tr.find('.harga').val()) || 0;
                    let qty = parseFloat($(this).val()) || 0;
                    let stok = parseFloat(tr.find('.stok').val()) || 0;
                    
                    if (stok > 0 && qty > stok) {
                        $(this).val(stok);
                        qty = stok;
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Melebihi stok!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                    
                    let total = harga * qty;
                    tr.find('.total').val(total).text(total);
                    kalkulasi();
                });

                // Delete row handler
                $(document).on('click', '.hapus-baris', function() {
                    removeProductRow($(this).closest('tr'));
                });

                // Update cicilan jika diubah
                $('#jmlcicilan').on('change', function() {
                    cekCicilan();
                });

                // Form submission
                $('#formTransaksi').on('submit', function(e) {
                    e.preventDefault();
                    
                    // Validasi minimal ada barang atau jasa
                    if($('#tabelJasa tbody tr').length === 0 && $('#tabelBarang tbody tr').length === 0) {
                        Swal.fire({
                            icon: "warning",
                            title: "Tidak ada transaksi",
                            text: "Tambahkan minimal 1 jasa atau 1 barang",
                            showConfirmButton: true
                        });
                        return;
                    }
                    
                    // Validasi semua barang sudah dipilih
                    let semuaBarangTerpilih = true;
                    $('#tabelBarang tbody tr').each(function() {
                        let idbarang = $(this).find('.select2-barang').val();
                        if (!idbarang || idbarang == '') {
                            semuaBarangTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaBarangTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Barang belum lengkap",
                            text: "Pastikan semua barang sudah dipilih",
                            showConfirmButton: true
                        });
                        return;
                    }
                    
                    // Validasi semua jasa sudah dipilih
                    let semuaJasaTerpilih = true;
                    $('#tabelJasa tbody tr').each(function() {
                        let idjasa = $(this).find('.select2-jasa').val();
                        if (!idjasa || idjasa == '') {
                            semuaJasaTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaJasaTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Jasa belum lengkap",
                            text: "Pastikan semua jasa sudah dipilih",
                            showConfirmButton: true
                        });
                        return;
                    }
                    
                    // Validasi khusus untuk cicilan
                    if ($('#metodebayar').val() === 'cicilan') {
                        if ($('#idcustomer').val() == '') {
                            Swal.fire({
                                icon: "warning",
                                title: "Anggota harus terisi",
                                text: "Untuk transaksi cicilan, pilih anggota terlebih dahulu",
                                showConfirmButton: true
                            });
                            return;
                        }
                        
                        // Cek cicilan sebelum submit
                        if(!cekCicilan()) {
                            Swal.fire({
                                icon: "warning",
                                title: "Periksa jumlah cicilan",
                                text: "Ada jasa/barang yang hanya boleh dicicil 1x",
                                showConfirmButton: true
                            });
                            return;
                        }
                        
                        // Validasi jumlah cicilan
                        let jmlCicilan = parseInt($('#jmlcicilan').val()) || 0;
                        if (jmlCicilan <= 0) {
                            Swal.fire({
                                icon: "warning",
                                title: "Jumlah cicilan tidak valid",
                                text: "Masukkan jumlah cicilan yang valid",
                                showConfirmButton: true
                            });
                            return;
                        }
                    } else {
                        // Validasi pembayaran untuk tunai
                        let dibayar = parseFloat($('#dibayar').val()) || 0;
                        let grandtotal = parseFloat($('#grandtotal').val()) || 0;
                        
                        if (dibayar < grandtotal) {
                            Swal.fire({
                                icon: "warning",
                                title: "Pembayaran kurang",
                                text: "Jumlah dibayar kurang dari total pembayaran",
                                showConfirmButton: true
                            });
                            return;
                        }
                    }
                    
                    // Konfirmasi transaksi
                    Swal.fire({
                        title: "Transaksi sekarang?",
                        text: "Pastikan data sudah benar",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, lanjutkan!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processSubmit();
                        }
                    });
                });
                
                function processSubmit() {
                    loader(true);
                    
                    // Persiapan data untuk dikirim
                    var formData = $('#formTransaksi').serializeArray();
                    
                    // Tambahkan data barang dan qty
                    var idbarang = [];
                    var qty = [];
                    var harga_jual = [];
                    
                    $('#tabelBarang tbody tr').each(function() {
                        idbarang.push($(this).find('.select2-barang').val());
                        qty.push($(this).find('.qty').val());
                        harga_jual.push($(this).find('.harga').val());
                    });
                    
                    formData.push({name: 'idbarang[]', value: idbarang});
                    formData.push({name: 'qty[]', value: qty});
                    formData.push({name: 'harga_jual[]', value: harga_jual});
                    
                    $.ajax({
                        url: "{{ route('bengkel.store') }}",
                        type: "POST",
                        data: $.param(formData),
                        success: function(res) {
                            loader(false);
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Nota bengkel berhasil dibuat',
                                icon: 'success',
                                confirmButtonText: 'Lihat Nota'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const url = `{{ url('/bengkel/nota') }}/${res.invoice}`;
                                    window.open(url, '_blank');
                                }
                            });
                            clearform();
                            invoice();
                        },
                        error: function(xhr) {
                            loader(false);
                            let errorMessage = 'Terjadi kesalahan saat menyimpan transaksi';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                errorMessage = xhr.responseText;
                            }
                            
                            Swal.fire({
                                title: "Error!",
                                text: errorMessage,
                                icon: "error"
                            });
                        }
                    });
                }
                
                // Initialize components
                initSelect2Barang(document);
                initSelect2Jasa(document);
                activateTypeahead();
                kalkulasi();
                invoice();
            });
        </script>
    </x-slot>
</x-app-layout>