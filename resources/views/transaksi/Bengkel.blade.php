<x-app-layout>
    <x-slot name="pagetitle">Transaksi Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Bengkel</h3>
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
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Transaksi Bengkel</h5>
                    </div>
                    <div class="card-body p-3">

                        {{-- HEADER --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Tanggal</span>
                                    <input type="text" class="form-control datepicker" name="tanggal" required>
                                    <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                                </div>
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Kasir</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" name="kasir" readonly>
                                </div>
                                <div class="input-group input-group-sm mb-2 align-items-center">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" value="" id="flexCheckDefault" checked>
                                        <label for="flexCheckDefault" class="mb-0">Anggota</label>
                                    </div>
                                    <input type="text" class="form-control" id="customer" name="customer" required>
                                    <input type="hidden" id="idcustomer" name="idcustomer">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Invoice</label>
                                    <div class="fs-6 fw-bold txtinv">{{ $invoice }}</div>
                                </div>
                                <div class="fs-3 fw-bold text-success topgrandtotal">Rp. 0</div>
                            </div>
                        </div>

                        {{-- TWO COLUMN LAYOUT --}}
                        <div class="row">
                            {{-- JASA COLUMN --}}
                            <div class="col-md-6 pe-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Jasa Bengkel</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped table-bordered" id="tabelJasa" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>Nama Jasa</th>
                                                    <th>Harga</th>
                                                    <th width="80px"></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        <button type="button" id="tambahJasa" class="btn btn-sm btn-primary mb-3">
                                            <i class="bi bi-plus"></i> Tambah Jasa
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- BARANG COLUMN --}}
                            <div class="col-md-6 ps-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="fw-bold mb-0">Sparepart</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="input-group input-group-sm mb-2"> 
                                            <input type="text" class="form-control typeahead" id="barcode-search" placeholder="Scan barcode atau cari barang">
                                            <span class="input-group-text bg-primary"><i class="fa-solid fa-barcode text-white"></i></span>
                                        </div>
                                        <table class="table table-sm table-striped table-bordered" id="tabelBarang" style="font-size: small;">
                                            <thead>
                                                <tr>
                                                    <th>Barcode</th>
                                                    <th>Barang</th>
                                                    <th width="80px">Qty</th>
                                                    <th>Harga Jual</th>
                                                    <th>Total</th>
                                                    <th width="80px"></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        <button type="button" id="tambahBarang" class="btn btn-sm btn-success mb-3">
                                            <i class="bi bi-plus"></i> Tambah Barang
                                        </button>
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
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="diskon" id="diskon">
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
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" required>
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
                                    <textarea class="form-control" name="note" rows="3"></textarea> 
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-auto d-flex gap-2">
                                <button type="button" class="btn btn-warning" onclick="clearform();"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-floppy-fill"></i> Simpan</button>
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
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        }

        /* Each suggestion */
        .tt-suggestion {
        padding: 0.5rem 1rem;
        cursor: pointer;
        }

        .tt-suggestion:hover {
        background-color: #f8f9fa; /* Bootstrap's hover color */
        }
        .form-control.no-border {
            border: none;
            box-shadow: none; /* Removes focus shadow */
        }

        .form-control.no-border:focus {
            border: none;
            box-shadow: none;
        }
        .label-fixed-width {
            width: 100px;
        }
        </style>
    </x-slot>

    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script>
            var globtot = 0;
            var barang = []; // For barcode search
            
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
                
                // Calculate services total
                $('#tabelJasa .harga').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                
                // Calculate parts total
                $('#tabelBarang .total').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                
                window.globtot = subtotal * (1 - ($('#diskon').val() / 100));
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(subtotal * (1 - ($('#diskon').val() / 100)));
                $('.topgrandtotal').text(formatRupiahWithDecimal($('#grandtotal').val()));
                $('#dibayar').prop('min', $('#grandtotal').val());
                $('#kembali').val($('#dibayar').val() - $('#grandtotal').val());
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
                $('.topgrandtotal').text('Rp.0');
                $('#subtotal').val(0);
                $('#diskon').val(0);
                $('#grandtotal').val(0);
                $('#dibayar').val(0);
                $('#kembali').val(0);
                $('#tabelJasa tbody tr').remove();
                $('#tabelBarang tbody tr').remove();
                $('#metodebayar').val('tunai').trigger('change');
                $('textarea[name="note"]').val('');
            }
            let users = [];
                let selectedFromList = false;
            let typeaheadEnabled = true;

            // === INIT SELECT2 JASA ===
            function initSelect2Jasa(context) {
                $(context).find('.select2-jasa').select2({
                    placeholder: 'Pilih Jasa',
                    width: '100%',
                    ajax: {
                        url: "{{ route('bengkel.getjasa') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data })
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    $(this).closest('tr').find('.harga').val(data.harga || 0);
                    kalkulasi();
                });
            }

            // === INIT SELECT2 BARANG ===
            function initSelect2Barang(context) {
                $(context).find('.select2-barang').select2({
                    placeholder: 'Pilih Barang',
                    width: '100%',
                    ajax: {
                        url: "{{ route('bengkel.getbarang') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data })
                    }
                }).on('select2:select', function(e) {
                    let data = e.params.data;
                    let tr = $(this).closest('tr');
                    tr.find('.harga').val(data.harga_jual || 0);
                    tr.find('.qty').val(1).trigger('input');
                    kalkulasi();
                });
            }

            // Add barang row from barcode search
            function addBarangRow(datarow) {
                let row = $(`
                    <tr>
                        <td>
                            <select name="idbarang[]" class="form-control select2-barang" required>
                                <option value="${datarow.id}" selected>${datarow.text}</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="qty[]" class="form-control form-control-sm qty" value="1" min="1" max="${datarow.stok || 999}">
                            <input type="hidden" name="stok[]" class="stok" value="${datarow.stok || 0}">
                        </td>
                        <td>
                            <input type="number" name="harga_jual[]" class="form-control form-control-sm harga" value="${datarow.harga_jual || 0}" readonly>
                        </td>
                        <td>
                            <input type="number" name="total[]" class="form-control form-control-sm total" value="${datarow.harga_jual || 0}" readonly>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm hapus-baris"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `);
                $('#tabelBarang tbody').append(row);
                initSelect2Barang(row);
                kalkulasi();
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
                    if($(this).val() == 'cicilan') {
                        $('.fieldcicilan').show();
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
                        $('.clmetode').hide().find('input, select').prop('required', false).val('');
                    } else {
                        $('.fieldcicilan').hide();
                        $('#jmlcicilan').html('');
                        $('.clmetode').show().find('input, select').prop('required', true);
                    }
                });

                // Member checkbox handler
                $('#flexCheckDefault').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                    } else {
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                    }
                });

                // Customer typeahead
                $('#customer').typeahead({
                    source: function(query, process) {
                        return $.ajax({
                            url: '{{ route('bengkel.getanggota') }}',
                            type: 'GET',
                            data: { query: query },
                            dataType: 'json',
                            success: function(data) {
                                return process(data.map(user => user.name));
                            }
                        });
                    },
                    afterSelect: function(name) {
                        const selected = users.find(user => user.name === name);
                        if (selected) {
                            $('#idcustomer').val(selected.id);
                        }
                    }
                });

                // Barcode search
                $('#barcode-search').typeahead({
                    source: function(query, process) {
                        return $.ajax({
                            url: '{{ route('bengkel.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function(data) {
                                barang = data;
                                return process(data.map(item => item.text));
                            }
                        });
                    },
                    afterSelect: function(text) {
                        const selected = barang.find(item => item.text === text);
                        if (selected) {
                            addBarangRow(selected);
                            $('#barcode-search').val('');
                        }
                    }
                });

                // Barcode search enter key
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $.ajax({
                            url: '{{ route('bengkel.getbarangbycode') }}',
                            method: 'GET',
                            data: { kode: $(this).val() },
                            dataType: 'json',
                            beforeSend: function() { loader(true); },
                            success: function(response) {
                                addBarangRow(response);
                                $('#barcode-search').val('');
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
                                $('#barcode-search').val('');
                                loader(false);
                            }
                        });
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
                            <td>
                                <input type="number" name="qty[]" class="form-control form-control-sm qty" value="1" min="1">
                                <input type="hidden" name="stok[]" class="stok" value="0">
                            </td>
                            <td>
                                <input type="number" name="harga_jual[]" class="form-control form-control-sm harga" readonly>
                            </td>
                            <td>
                                <input type="number" name="total[]" class="form-control form-control-sm total" readonly>
                            </td>
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
                    
                    tr.find('.total').val(harga * qty);
                    kalkulasi();
                });

                // Delete row handler
                $(document).on('click', '.hapus-baris', function() {
                    $(this).closest('tr').remove();
                    kalkulasi();
                });

                // Form submission
                $('#formTransaksi').on('submit', function(e) {
                    e.preventDefault();
                    
                    if($('#tabelJasa tbody tr').length === 0 && $('#tabelBarang tbody tr').length === 0) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Tambahkan minimal 1 jasa atau 1 barang',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        return;
                    }
                    
                    if(parseFloat($('#dibayar').val()) < parseFloat($('#grandtotal').val()) && $('#metodebayar').val() === 'tunai') {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Pembayaran kurang dari total',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        return;
                    }
                    
                    loader(true);
                    $.ajax({
                        url: "{{ route('bengkel.store') }}",
                        type: "POST",
                        data: $(this).serialize(),
                        success: function(res) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Transaksi berhasil disimpan',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            const url = `{{ url('/bengkel/nota') }}/${res.invoice}`;
                            window.open(url, '_blank');
                            clearform();
                            invoice();
                            loader(false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Gagal menyimpan transaksi',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            loader(false);
                        }
                    });
                });
                
                // Initialize components
                initSelect2Barang(document);
                initSelect2Jasa(document);
                kalkulasi();
            });
        </script>
    </x-slot>
</x-app-layout>