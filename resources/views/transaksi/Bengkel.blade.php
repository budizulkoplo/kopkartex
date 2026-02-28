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
                                    <input type="text" class="form-control" id="customer" name="nonamecustomer" required autocomplete="off" placeholder="Cari anggota...">
                                    <input type="hidden" id="idcustomer" name="idcustomer">
                                    <input type="hidden" id="customer-name" name="customer">
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

                        {{-- INPUT QTY --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center bg-light p-2 rounded border">
                                    <div class="me-3 fw-bold text-primary" style="min-width: 100px;">
                                        <i class="bi bi-plus-circle"></i> QTY:
                                    </div>
                                    <div style="width: 150px;">
                                        <input type="number" class="form-control form-control-sm" id="input-qty" value="1" min="1" onfocus="this.select()">
                                    </div>
                                    <div class="ms-3 text-muted small">
                                        <i class="bi bi-arrow-return-left"></i> isi qty, lalu Enter untuk input produk
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL JASA --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Jasa Bengkel</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped table-bordered mb-2" id="tabelJasa" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Jasa</th>
                                                    <th>Harga</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row akan ditambahkan via JavaScript -->
                                            </tbody>
                                        </table>
                                        <div class="text-end">
                                            <button type="button" id="tambahJasa" class="btn btn-primary btn-sm">
                                                <i class="bi bi-plus"></i> Tambah Jasa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TABEL BARANG --}}
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Sparepart</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped table-bordered mb-2" id="tabelBarang" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode</th>
                                                    <th>Nama Barang</th>
                                                    <th>Stok</th>
                                                    <th>Qty</th>
                                                    <th>Harga</th>
                                                    <th>Total</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row akan ditambahkan via JavaScript -->
                                            </tbody>
                                        </table>
                                        <div class="text-end">
                                            <button type="button" id="tambahBarang" class="btn btn-success btn-sm">
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
                                    <input type="number" class="form-control form-control-sm" id="jmlcicilan" name="jmlcicilan" min="1" value="1" onfocus="this.select()" onkeyup="cekCicilan()">
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
                                    <span id="textInfoCicilan">Ada barang dengan cicilan 1x</span>
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
        
        /* Select2 custom styling */
        .select2-results__option {
            padding: 8px 12px;
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
            height: 31px;
            border: 1px solid #ced4da;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px;
            padding-left: 8px;
            font-size: 0.875rem;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        
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
        
        @media (max-width: 768px) {
            .select2-container {
                width: 100% !important;
            }
            
            .label-fixed-width {
                min-width: 80px;
            }
        }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script>
            var globtot = 0;
            var existingJasa = {};
            var existingBarang = {};
            var typeaheadInstance = null;
            var enterPressed = false;
            
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

            function numbering(tableId) {
                $(tableId + ' tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function kalkulasi() {
                let subtotal = 0;
                
                // Hitung total jasa
                $('#tabelJasa tbody tr').each(function() {
                    let harga = parseFloat($(this).find('.harga-jasa').val()) || 0;
                    subtotal += harga;
                });
                
                // Hitung total barang
                $('#tabelBarang tbody tr').each(function() {
                    let qty = parseFloat($(this).find('.barangqty').val()) || 0;
                    let harga = parseFloat($(this).find('.hargajual').val()) || 0;
                    let total = qty * harga;
                    $(this).find('.totalitm').text(formatRupiahWithDecimal(total));
                    subtotal += total;
                });
                
                window.globtot = subtotal * (1 - ($('#diskon').val() / 100));
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(window.globtot);
                $('.topgrandtotal').text(formatRupiahWithDecimal(window.globtot));
                $('#dibayar').prop('min', window.globtot);
                $('#kembali').val(($('#dibayar').val() || 0) - window.globtot);
                
                if($('#metodebayar').val() == 'cicilan') {
                    cekCicilan();
                }
            }

            function cekCicilan() {
                let jmlCicilan = parseInt($('#jmlcicilan').val()) || 1;
                let hasCicilan0 = false;
                
                // Cek barang dengan kategori cicilan 0
                $('#tabelBarang tbody tr').each(function() {
                    let selectElement = $(this).find('.namabarang');
                    let selectedOption = selectElement.find('option:selected');
                    let kategoriCicilan = selectedOption.data('cicilan') || 1;
                    
                    if(kategoriCicilan == 0) {
                        hasCicilan0 = true;
                    }
                });
                
                // Cek jasa (selalu cicilan 1x)
                let hasJasa = $('#tabelJasa tbody tr').length > 0;
                
                if((hasJasa || hasCicilan0) && jmlCicilan > 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Ada jasa/barang cicilan 1x, cicilan diubah menjadi 1',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#jmlcicilan').val(1);
                    $('#infoCicilan').show();
                    return false;
                }
                
                $('#infoCicilan').hide();
                return true;
            }

            function clearBarcodeSearch() {
                $('#barcode-search').val('');
                if (typeaheadInstance) {
                    $('#barcode-search').typeahead('val', '');
                }
            }

            function focusToQtyInput() {
                setTimeout(() => {
                    $('#input-qty').focus().select();
                }, 100);
            }

            function focusToBarcode() {
                setTimeout(() => {
                    $('#barcode-search').focus();
                }, 100);
            }

            // FUNGSI UNTUK JASA
            function addJasaRow(datarow = null) {
                let newRow = $(`
                    <tr>
                        <td></td>
                        <td>
                            <select class="form-select form-select-sm namajasa" style="width:100%" required>
                                ${datarow ? `<option value="${datarow.id}" selected data-harga="${datarow.harga}">${datarow.text}</option>` : ''}
                            </select>
                            <input type="hidden" name="jasa_id[]" class="idjasa" value="${datarow ? datarow.id : ''}">
                        </td>
                        <td>
                            <input type="number" name="jasa_harga[]" class="form-control form-control-sm harga-jasa" value="${datarow ? datarow.harga : 0}" readonly>
                        </td>
                        <td>
                            <span class="badge btn bg-danger dellist" onclick="removeJasaRow($(this).closest('tr'))">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `);
                
                $('#tabelJasa tbody').prepend(newRow);
                numbering('#tabelJasa');
                initSelect2Jasa(newRow);
                
                if (datarow && datarow.id) {
                    existingJasa[datarow.id] = newRow;
                }
                
                kalkulasi();
            }

            function removeJasaRow(row) {
                let idjasa = row.find('.idjasa').val();
                if (idjasa && existingJasa[idjasa]) {
                    delete existingJasa[idjasa];
                }
                row.remove();
                numbering('#tabelJasa');
                kalkulasi();
                
                if($('#metodebayar').val() == 'cicilan') {
                    cekCicilan();
                }
            }

            function initSelect2Jasa(context) {
                context.find('.namajasa').select2({
                    placeholder: "Pilih jasa",
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('bengkel.getjasa') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) { 
                            return { q: params.term }; 
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(j => ({
                                    id: j.id, 
                                    text: j.text, 
                                    harga: j.harga
                                }))
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        let harga = formatRupiahWithDecimal(data.harga);
                        
                        return $(
                            `<div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${data.text}</strong>
                                </div>
                                <div class="text-primary fw-bold">${harga}</div>
                            </div>`
                        );
                    },
                    templateSelection: function(data) {
                        return data.text || data.id;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let row = $(this).closest('tr');
                    
                    row.find('.harga-jasa').val(data.harga);
                    row.find('.idjasa').val(data.id);
                    
                    if (data.id && !existingJasa[data.id]) {
                        existingJasa[data.id] = row;
                    }
                    
                    kalkulasi();
                    
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                    }
                }).on('select2:clear', function() {
                    let row = $(this).closest('tr');
                    let idjasa = row.find('.idjasa').val();
                    
                    if (idjasa && existingJasa[idjasa]) {
                        delete existingJasa[idjasa];
                    }
                    
                    row.find('.harga-jasa').val(0);
                    row.find('.idjasa').val('');
                    kalkulasi();
                });
            }

            // FUNGSI UNTUK BARANG
            function addBarangRow(datarow = null, qty = 1) {
                if (datarow && datarow.stok <= 0) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Stok habis!',
                        text: `Produk "${datarow.text}" tidak tersedia`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    clearBarcodeSearch();
                    focusToBarcode();
                    return;
                }
                
                // Cek apakah produk sudah ada
                if (datarow && datarow.id && existingBarang[datarow.id]) {
                    let existingRow = existingBarang[datarow.id];
                    let currentQty = parseInt(existingRow.find('.barangqty').val()) || 0;
                    let newQty = currentQty + qty;
                    let maxStok = parseInt(existingRow.find('.barangqty').attr('max')) || datarow.stok;
                    
                    if (newQty > maxStok) {
                        newQty = maxStok;
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Melebihi stok!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                    
                    existingRow.find('.barangqty').val(newQty);
                    kalkulasi();
                    clearBarcodeSearch();
                    focusToBarcode();
                    return;
                }
                
                let newRow = $(`
                    <tr data-id="${datarow ? datarow.id : 0}">
                        <td></td>
                        <td class="kodebarang">${datarow ? datarow.code : ''}</td>
                        <td>
                            <select class="form-select form-select-sm namabarang" style="width:100%" required>
                                ${datarow ? `<option value="${datarow.id}" selected data-cicilan="${datarow.kategori_cicilan || 1}">${datarow.text}</option>` : ''}
                            </select>
                            <input type="hidden" name="idbarang[]" class="idbarang" value="${datarow ? datarow.id : ''}">
                        </td>
                        <td class="text-center">
                            <span class="stoktext">${datarow ? datarow.stok : 0}</span>
                            <input type="hidden" class="stok" value="${datarow ? datarow.stok : 0}">
                        </td>
                        <td>
                            <input type="number" name="qty[]" class="form-control form-control-sm barangqty" 
                                   value="${qty}" min="1" max="${datarow ? datarow.stok : 999}" 
                                   onfocus="this.select()" onkeyup="kalkulasi()" required>
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="${datarow ? datarow.harga_jual : 0}">
                        </td>
                        <td class="hargajualtext">${datarow ? formatRupiahWithDecimal(datarow.harga_jual) : ''}</td>
                        <td class="totalitm"></td>
                        <td>
                            <span class="badge btn bg-danger dellist" onclick="removeBarangRow($(this).closest('tr'))">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `);
                
                $('#tabelBarang tbody').prepend(newRow);
                numbering('#tabelBarang');
                initSelect2Barang(newRow);
                
                if (datarow && datarow.id) {
                    existingBarang[datarow.id] = newRow;
                }
                
                kalkulasi();
                clearBarcodeSearch();
                focusToBarcode();
            }

            function removeBarangRow(row) {
                let idbarang = row.find('.idbarang').val();
                if (idbarang && existingBarang[idbarang]) {
                    delete existingBarang[idbarang];
                }
                row.remove();
                numbering('#tabelBarang');
                kalkulasi();
                
                if($('#metodebayar').val() == 'cicilan') {
                    cekCicilan();
                }
            }

            function initSelect2Barang(context) {
                context.find('.namabarang').select2({
                    placeholder: "Pilih barang",
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('bengkel.getbarang') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) { 
                            return { q: params.term }; 
                        },
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
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        
                        let stokInfo = data.stok > 0 ? 
                            `<span class="text-success">Stok: ${data.stok}</span>` : 
                            `<span class="text-danger">Stok: ${data.stok}</span>`;
                        
                        let cicilanInfo = data.kategori_cicilan == 0 ? 
                            `<span class="badge bg-warning">Cicilan 1x</span>` : 
                            `<span class="badge bg-info">Cicilan fleksibel</span>`;
                        
                        let harga = formatRupiahWithDecimal(data.harga_jual);
                        
                        return $(
                            `<div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${data.code}</strong> - ${data.text}
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold">${harga}</div>
                                    <div class="small">${stokInfo}</div>
                                    <div class="small">${cicilanInfo}</div>
                                </div>
                            </div>`
                        );
                    },
                    templateSelection: function(data) {
                        return data.text || data.code;
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let row = $(this).closest('tr');
                    
                    if (data.stok <= 0) {
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
                    
                    // Cek apakah produk sudah ada di row lain
                    if (data.id && existingBarang[data.id] && existingBarang[data.id] !== row) {
                        let existingRow = existingBarang[data.id];
                        let currentQty = parseInt(existingRow.find('.barangqty').val()) || 0;
                        let newQty = currentQty + 1;
                        let maxStok = parseInt(existingRow.find('.barangqty').attr('max')) || data.stok;
                        
                        if (newQty > maxStok) {
                            newQty = maxStok;
                        }
                        
                        existingRow.find('.barangqty').val(newQty);
                        row.remove();
                        numbering('#tabelBarang');
                        kalkulasi();
                        clearBarcodeSearch();
                        focusToBarcode();
                        return;
                    }
                    
                    row.attr("data-id", data.id);
                    row.find('.kodebarang').text(data.code);
                    row.find('.hargajual').val(data.harga_jual);
                    row.find('.hargajualtext').text(formatRupiahWithDecimal(data.harga_jual));
                    row.find('.stoktext').text(data.stok);
                    row.find('.stok').val(data.stok);
                    row.find('.barangqty').val(1).attr("max", data.stok);
                    row.find('.idbarang').val(data.id);
                    
                    $(this).find('option:selected').attr('data-cicilan', data.kategori_cicilan);
                    
                    if (data.id && !existingBarang[data.id]) {
                        existingBarang[data.id] = row;
                    }
                    
                    kalkulasi();
                    
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                    }
                    
                    clearBarcodeSearch();
                    focusToBarcode();
                    
                }).on('select2:clear', function() {
                    let row = $(this).closest('tr');
                    let idbarang = row.find('.idbarang').val();
                    
                    if (idbarang && existingBarang[idbarang]) {
                        delete existingBarang[idbarang];
                    }
                    
                    row.attr("data-id", 0);
                    row.find('.kodebarang').text('');
                    row.find('.hargajual').val(0);
                    row.find('.hargajualtext').text('');
                    row.find('.stoktext').text('0');
                    row.find('.stok').val(0);
                    row.find('.barangqty').val(0);
                    row.find('.idbarang').val('');
                    kalkulasi();
                });
            }

            // TYPEAHEAD UNTUK ANGGOTA
            function activateTypeahead() {
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
                        let persentase = 0;
                        if (item.limit_hutang > 0) {
                            persentase = (item.total_pokok / (item.limit_hutang + item.total_pokok)) * 100;
                        }

                        let alertClass = 'alert-success';
                        if (persentase >= 50 && persentase <= 75) {
                            alertClass = 'alert-warning';
                        } else if (persentase > 75) {
                            alertClass = 'alert-danger';
                        }

                        $('#detailcus')
                            .removeClass('alert-success alert-warning alert-danger')
                            .addClass(alertClass + ' text-dark')
                            .html(`
                                <table class="mb-0">
                                    <tr><td class="pe-2">Nomor Anggota</td><td>:<b> ${item.nomor_anggota} - ${item.name}</b></td></tr>
                                    <tr><td>Jumlah Hutang</td><td>: ${formatRupiahWithDecimal(item.total_pokok)}</td></tr>
                                    <tr><td>Sisa Limit Hutang</td><td>: ${formatRupiahWithDecimal(item.limit_hutang)}</td></tr>
                                </table>
                            `)
                            .show();
                        $('#idcustomer').val(item.id);
                        $('#customer-name').val(item.name);
                        $('#customer').val(item.nomor_anggota + ' - ' + item.name);
                    }
                });
            }

            function destroyTypeahead() {
                $('#customer').typeahead('destroy');
                $('#detailcus').html('').hide();
            }

            function clearform() {
                $('#customer').val('');
                $('#idcustomer').val('');
                $('#customer-name').val('');
                $('#detailcus').html('').hide();
                $('#tabelJasa tbody').empty();
                $('#tabelBarang tbody').empty();
                $('#subtotal').val(0);
                $('#diskon').val(0);
                $('#grandtotal').val(0);
                $('#dibayar').val(0);
                $('#kembali').val(0);
                $('textarea[name="note"]').val('');
                $('#metodebayar').val('tunai').trigger('change');
                $('#input-qty').val(1);
                $('.topgrandtotal').text('Rp. 0');
                
                existingJasa = {};
                existingBarang = {};
                
                $('.datepicker').datepicker('setDate', new Date());
                clearBarcodeSearch();
                invoice();
            }

            $(document).ready(function() {
                // Initialize datepicker
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());

                // Payment method change
                $('#metodebayar').on('change', function() {
                    if($(this).val() == 'cicilan'){
                        $('.fieldcicilan').show();
                        $('.clmetode').hide().find('input, select').prop('required', false).val('');
                        $('#flexCheckDefault')
                            .prop('checked', true)
                            .off('click.prevent')
                            .on('click.prevent', function(e) {
                                e.preventDefault();
                            });
                        cekCicilan();
                    } else {
                        $('.fieldcicilan').hide();
                        $('.clmetode').show().find('input, select').prop('required', true);
                        $('#flexCheckDefault').off('click.prevent');
                        $('#infoCicilan').hide();
                    }
                });

                // Member checkbox
                $('#flexCheckDefault').on('change', function() {
                    if ($(this).is(':checked')) {
                        activateTypeahead();
                    } else {
                        destroyTypeahead();
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                        $('#customer-name').val('');
                    }
                });

                // Nonaktifkan Enter di seluruh form
                $(window).keydown(function(event) {
                    if (event.key === "Enter") {
                        if ($(event.target).is('#barcode-search') || $(event.target).is('#input-qty')) {
                            return true;
                        }
                        event.preventDefault();
                        return false;
                    }
                });

                // Typeahead untuk barcode
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
                                let suggestions = data.map(function(item) {
                                    return {
                                        id: item.id,
                                        code: item.code,
                                        text: item.text,
                                        harga_jual: item.harga_jual,
                                        stok: item.stok,
                                        kategori_cicilan: item.kategori_cicilan,
                                        display: `${item.code} - ${item.text} (Stok: ${item.stok})`
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
                        
                        if (item.stok <= 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'warning',
                                title: 'Stok habis!',
                                text: `Produk "${item.text}" tidak tersedia`,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            return '';
                        }
                        
                        $('#barcode-search').data('selected-item', item);
                        focusToQtyInput();
                        return '';
                    }
                });

                // Handle Enter di input qty
                $('#input-qty').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        
                        let selectedItem = $('#barcode-search').data('selected-item');
                        let qty = parseInt($(this).val()) || 1;
                        
                        if (selectedItem) {
                            addBarangRow(selectedItem, qty);
                            $('#barcode-search').removeData('selected-item');
                            $(this).val(1);
                        } else {
                            focusToBarcode();
                        }
                    }
                });

                // Handle Enter untuk barcode
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
                                    if (response.stok <= 0) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "warning",
                                            title: "Stok habis!",
                                            text: `Produk "${response.text}" tidak tersedia`,
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                        clearBarcodeSearch();
                                        focusToBarcode();
                                        loader(false);
                                        return;
                                    }
                                    
                                    $('#barcode-search').data('selected-item', response);
                                    focusToQtyInput();
                                    loader(false);
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
                                    focusToBarcode();
                                    loader(false);
                                }
                            });
                        }
                        
                        setTimeout(() => {
                            enterPressed = false;
                        }, 100);
                    }
                });

                // Tambah Jasa
                $('#tambahJasa').on('click', function() {
                    addJasaRow();
                });

                // Tambah Barang manual
                $('#tambahBarang').on('click', function() {
                    addBarangRow();
                });

                // Submit form
                $('#formTransaksi').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        $(this).addClass('was-validated');
                        return;
                    }
                    
                    if($('#tabelJasa tbody tr').length === 0 && $('#tabelBarang tbody tr').length === 0) {
                        Swal.fire({
                            icon: "warning",
                            title: "Tidak ada transaksi",
                            text: "Tambahkan minimal 1 jasa atau 1 barang"
                        });
                        return;
                    }
                    
                    // Validasi semua jasa sudah dipilih
                    let semuaJasaTerpilih = true;
                    $('#tabelJasa tbody tr').each(function() {
                        if (!$(this).find('.namajasa').val()) {
                            semuaJasaTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaJasaTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Jasa belum lengkap",
                            text: "Pastikan semua jasa sudah dipilih"
                        });
                        return;
                    }
                    
                    // Validasi semua barang sudah dipilih
                    let semuaBarangTerpilih = true;
                    $('#tabelBarang tbody tr').each(function() {
                        if (!$(this).find('.namabarang').val()) {
                            semuaBarangTerpilih = false;
                            return false;
                        }
                    });
                    
                    if (!semuaBarangTerpilih) {
                        Swal.fire({
                            icon: "warning",
                            title: "Barang belum lengkap",
                            text: "Pastikan semua barang sudah dipilih"
                        });
                        return;
                    }
                    
                    // Validasi untuk cicilan
                    if ($('#metodebayar').val() === 'cicilan') {
                        if (!$('#idcustomer').val()) {
                            Swal.fire({
                                icon: "warning",
                                title: "Anggota harus terisi",
                                text: "Untuk transaksi cicilan, pilih anggota terlebih dahulu"
                            });
                            return;
                        }
                        
                        if(!cekCicilan()) {
                            Swal.fire({
                                icon: "warning",
                                title: "Periksa jumlah cicilan",
                                text: "Ada jasa/barang yang hanya boleh dicicil 1x"
                            });
                            return;
                        }
                        
                        let jmlCicilan = parseInt($('#jmlcicilan').val()) || 0;
                        if (jmlCicilan <= 0) {
                            Swal.fire({
                                icon: "warning",
                                title: "Jumlah cicilan tidak valid"
                            });
                            return;
                        }
                    } else {
                        let dibayar = parseFloat($('#dibayar').val()) || 0;
                        let grandtotal = parseFloat($('#grandtotal').val()) || 0;
                        
                        if (dibayar < grandtotal) {
                            Swal.fire({
                                icon: "warning",
                                title: "Pembayaran kurang",
                                text: "Jumlah dibayar kurang dari total pembayaran"
                            });
                            return;
                        }
                    }
                    
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
                    let formData = new FormData($('#formTransaksi')[0]);
                    
                    // Tambahkan data jasa
                    let jasaIds = [];
                    let jasaHargas = [];
                    $('#tabelJasa tbody tr').each(function() {
                        jasaIds.push($(this).find('.idjasa').val());
                        jasaHargas.push($(this).find('.harga-jasa').val());
                    });
                    formData.append('jasa_ids', JSON.stringify(jasaIds));
                    formData.append('jasa_hargas', JSON.stringify(jasaHargas));
                    
                    $.ajax({
                        url: "{{ route('bengkel.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() { loader(true); },
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
                            }
                            Swal.fire({
                                title: "Error!",
                                text: errorMessage,
                                icon: "error"
                            });
                        }
                    });
                }

                function invoice() {
                    $.ajax({
                        url: '{{ route('bengkel.getinv') }}',
                        method: 'GET',
                        beforeSend: function() { loader(true); },
                        success: function(response) {
                            $('.txtinv').text(response);
                            loader(false);
                        },
                        error: function() { loader(false); }
                    });
                }

                // Initialize
                activateTypeahead();
                kalkulasi();
                invoice();
            });
        </script>
    </x-slot>
</x-app-layout>