<x-app-layout>
    <x-slot name="pagetitle">Penjualan</x-slot>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Form Penjualan - {{ $unit->nama_unit }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Penjualan</li>
                        <li class="breadcrumb-item active" aria-current="page">Form</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima" autocomplete="off">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header p-2">
                        <div class="alert alert-success ps-2 p-0 mb-0" role="alert" id="detailcus" style="display: none"></div>
                    </div>
                    <div class="card-body p-3">
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
                                    <input type="text" class="form-control" id="nonamecustomer" name="nonamecustomer" required autocomplete="off" placeholder="Cari anggota...">
                                    <input type="hidden" id="customer" name="customer">
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
                                    <input type="hidden" id="barcode-id">
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

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>@Harga</th>
                                            <th>Stok</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div class="mb-2 text-end">
                                    <button type="button" class="btn btn-primary btn-sm" id="tambahBarang">
                                        <i class="bi bi-plus"></i> Tambah Barang
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Subtotal</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="subtotal" id="subtotal" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Diskon</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="diskon" id="diskon">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Grand Total</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="grandtotal" id="grandtotal" disabled>
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
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" required>
                                </div>
                                <div class="input-group input-group-sm mb-2 clmetode">
                                    <span class="input-group-text label-fixed-width">Kembali</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" name="kembali" id="kembali" disabled>
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
        
        .tt-suggestion .badge {
            font-size: 0.65em;
            padding: 0.2em 0.4em;
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
            height: 38px;
            border: 1px solid #ced4da;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .select2-container {
                width: 100% !important;
            }
            
            #tbterima th, #tbterima td {
                padding: 4px;
                font-size: 12px;
            }
            
            .label-fixed-width {
                min-width: 100px;
            }
        }
        </style>
    </x-slot>
    
    <x-slot name="jscustom">
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script>
            var globtot = 0;
            var barang = [];
            
            function loader(onoff) {
                if (onoff)
                    $('.app-wrapper').waitMe({
                        effect: 'bounce',
                        text: '',
                        bg: 'rgba(255,255,255,0.7)',
                        color: '#000',
                        waitTime: -1,
                        textPos: 'vertical'
                    });
                else
                    $('.app-wrapper').waitMe('hide');
            }
            
            function formatRupiahWithDecimal(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }
            
            function numbering() {
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }
            
            function kalkulasi(obj) {
                let subtotal = 0, barangtmp = [];
                
                // Hitung subtotal dari semua barang
                $('#tbterima tbody tr').each(function(index, element) {
                    var row = $(this);
                    var barangqty = parseInt(row.find('.barangqty').val()) || 0;
                    var hargajual = parseInt(row.find('.hargajual').val()) || 0;
                    var idbarang = row.find('.idbarang').val();
                    var stok = parseInt(row.find('.stok').val()) || 0;
                    
                    // Update total per item
                    var totalItem = barangqty * hargajual;
                    row.find('.totalitm').html(formatRupiahWithDecimal(totalItem));
                    
                    barangtmp.push({
                        'barangqty': barangqty,
                        'stok': stok,
                        'idbarang': parseInt(idbarang),
                        'hargajual': hargajual
                    });
                });
                
                // Hitung subtotal
                $.each(barangtmp, function(index, item) {
                    subtotal += (item.hargajual * item.barangqty);
                });
                
                // Cek stok jika ada perubahan qty
                if (obj) {
                    let idbarangObj = $(obj).closest('tr').find('.idbarang').val();
                    var cekbarang = barangtmp.find(item => item.idbarang === parseInt(idbarangObj));
                    let qty = parseInt($(obj).val() || 0);

                    if (cekbarang && qty > cekbarang.stok) {
                        $(obj).val(cekbarang.stok);
                        qty = cekbarang.stok;

                        Swal.fire({
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Melebihi stok!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        return;
                    }
                }
                
                // Hitung grand total dengan diskon
                window.globtot = subtotal * (1 - ($('#diskon').val() / 100));
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(window.globtot);
                $('.topgrandtotal').text(formatRupiahWithDecimal(window.globtot));
                $('#dibayar').prop('min', window.globtot);
                
                // Hitung kembali
                let dibayar = parseFloat($('#dibayar').val()) || 0;
                $('#kembali').val(dibayar - window.globtot);
            }
            
            function invoice() {
                $.ajax({
                    url: '{{ route('jual.getinv') }}',
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function(xhr) { loader(true); },
                    success: function(response) {
                        $('.txtinv').text(response);
                        loader(false);
                    },
                    error: function(xhr, status, error) { loader(false); }
                });
            }
            
            // Fungsi untuk cek cicilan
            function cekCicilan() {
                let jmlCicilan = parseInt($('#jmlcicilan').val()) || 1;
                
                // Loop melalui semua item untuk cek kategori cicilan
                let hasCicilan0 = false;
                $('#tbterima tbody tr').each(function() {
                    let selectElement = $(this).find('.namabarang');
                    let selectedOption = selectElement.find('option:selected');
                    let kategoriCicilan = selectedOption.data('cicilan') || 1;
                    
                    if(kategoriCicilan == 0) {
                        hasCicilan0 = true;
                    }
                });
                
                // Jika ada barang kategori cicilan 0, maksimal cicilan adalah 1
                if(hasCicilan0 && jmlCicilan > 1) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Ada barang dengan cicilan 1x, cicilan diubah menjadi 1',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#jmlcicilan').val(1);
                    return false;
                }
                return true;
            }
            
            function addRow(datarow = null) {
                datarow = datarow || {id: 0, code: '', text: '', harga_jual: 0, stok: 0, type: '', kategori_cicilan: 1};
                
                // Tambah hidden input untuk idbarang
                let newRow = $(`
                    <tr data-id="${datarow.id}" class="align-middle">
                        <td></td>
                        <td class="kodebarangtext">${datarow.code}</td>
                        <td>
                            <select class="form-select form-select-sm namabarang" style="width:100%" data-cicilan="${datarow.kategori_cicilan || 1}">
                                ${datarow.id ? `<option value="${datarow.id}" selected data-cicilan="${datarow.kategori_cicilan || 1}">${datarow.text}</option>` : ''}
                            </select>
                            <input type="hidden" class="idbarang" name="idbarang[]" value="${datarow.id}">
                        </td>
                        <td class="hargajualtext">${datarow.harga_jual ? formatRupiahWithDecimal(datarow.harga_jual) : ''}</td>
                        <td>
                            <span class="stoktext">${datarow.stok}</span>
                            <input type="hidden" class="stok" name="stok[]" value="${datarow.stok}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm w-auto barangqty" 
                                   onfocus="this.select()" 
                                   min="1" 
                                   max="${datarow.stok}" 
                                   name="qty[]" 
                                   onkeyup="kalkulasi(this)" 
                                   value="1" 
                                   data-id="${datarow.id}" 
                                   required>
                            <input type="hidden" name="harga_beli[]" class="hargabeli" value="${datarow.harga_beli || 0}">
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="${datarow.harga_jual || 0}">
                        </td>
                        <td class="totalitm"></td>
                        <td>
                            <span class="badge btn bg-danger dellist" onclick="$(this).closest('tr').remove(); kalkulasi(); numbering();">
                                <i class="bi bi-trash3-fill"></i>
                            </span>
                        </td>
                    </tr>
                `);
                
                $('#tbterima tbody').append(newRow);
                numbering();
                kalkulasi();
                
                // Inisialisasi select2 untuk row baru dengan template detail
                newRow.find('.namabarang').select2({
                    placeholder: "Pilih barang",
                    width: '100%',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('jual.getbarang') }}',
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
                                    harga_beli: b.harga_beli, 
                                    harga_jual: b.harga_jual, 
                                    stok: b.stok,
                                    type: b.type,
                                    kategori_cicilan: b.kategori_cicilan
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
                                    <div class="text-muted small">${data.type || ''}</div>
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
                    
                    row.attr("data-id", data.id);
                    row.find('.kodebarangtext').text(data.code);
                    row.find('.hargajual').val(data.harga_jual);
                    row.find('.hargajualtext').text(formatRupiahWithDecimal(data.harga_jual));
                    row.find('.hargabeli').val(data.harga_beli || 0);
                    row.find('.stoktext').text(data.stok);
                    row.find('.stok').val(data.stok);
                    row.find('.barangqty').val(1);
                    row.find('.barangqty').attr("max", data.stok);
                    row.find('.barangqty').attr("data-id", data.id);
                    row.find('.idbarang').val(data.id);
                    
                    // Update atribut data-cicilan
                    $(this).attr('data-cicilan', data.kategori_cicilan);
                    $(this).find('option:selected').attr('data-cicilan', data.kategori_cicilan);
                    
                    kalkulasi();
                    
                    // Cek cicilan jika metode cicilan dipilih
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                    }
                }).on('select2:clear', function() {
                    let row = $(this).closest('tr');
                    row.attr("data-id", 0);
                    row.find('.kodebarangtext').text('');
                    row.find('.hargajual').val('');
                    row.find('.hargajualtext').text('');
                    row.find('.stoktext').text('');
                    row.find('.stok').val('');
                    row.find('.barangqty').val('');
                    row.find('.idbarang').val('');
                    row.find('.hargabeli').val('');
                    kalkulasi();
                    
                    // Cek cicilan jika metode cicilan dipilih
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                    }
                });

                // Tombol hapus
                newRow.find('.dellist').on('click', function() {
                    $(this).closest('tr').remove();
                    numbering();
                    kalkulasi();
                    
                    // Cek cicilan jika metode cicilan dipilih
                    if($('#metodebayar').val() == 'cicilan') {
                        cekCicilan();
                    }
                });
                
                $('#barcode-search').val('');
                $('#barcode-search').typeahead('val', '');
            }
            
            function clearform() {
                $('#nonamecustomer').val('');
                $('#idcustomer').val('');
                $('#customer').val('');
                $('#detailcus').html('').hide();
                $('textarea[name="note"]').val('');
                $('.topgrandtotal').text('Rp. 0');
                $('#subtotal').val(0);
                $('#diskon').val(0);
                $('#grandtotal').val(0);
                $('#dibayar').val(0);
                $('#kembali').val(0);
                $('#tbterima tbody').empty();
                $('#metodebayar').val('tunai').trigger('change');
                
                // Reset tanggal ke hari ini
                $('.datepicker').datepicker('setDate', new Date());
                
                // Refresh invoice
                invoice();
            }
            
            let users = [];
            let selectedFromList = false;
            let typeaheadEnabled = true;

            function activateTypeahead() {
                $('#detailcus').html('');
                $('#nonamecustomer').typeahead({
                    minLength: 2,
                    displayText: function(item) {
                        return item.nomor_anggota + ' - ' + item.name;
                    },
                    source: function(query, process) {
                        return $.get('{{ route('jual.getanggota') }}', { query: query }, function(data) {
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
                        $('#customer').val(item.name);
                    }
                });
            }
            
            function destroyTypeahead() {
                $('#nonamecustomer').typeahead('destroy');
                $('#detailcus').html('').hide();
                typeaheadEnabled = false;
            }
            
            $(document).ready(function() {
                // Initialize datepicker
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());
                
                // Tombol tambah barang manual
                $('#tambahBarang').on('click', function() {
                    addRow({id: 0, code: '', text: '', harga_jual: 0, stok: 0});
                });
                
                // Handle metode bayar change
                $('#metodebayar').on('change', function() {
                    if($(this).val() == 'cicilan'){
                        $('.fieldcicilan').show();
                        
                        // Cek apakah ada barang dengan kategori cicilan 0
                        if(!cekCicilan()) {
                            $('#jmlcicilan').val(1);
                        }
                        
                        // Sembunyikan & nonaktifkan input dibayar/kembali
                        $('.clmetode').hide().find('input, select').prop('required', false).val('');
                        $('#flexCheckDefault')
                            .prop('checked', true)
                            .off('click.prevent')
                            .on('click.prevent', function(e) {
                                e.preventDefault();
                            });
                    } else {
                        $('.fieldcicilan').hide();
                        $('#jmlcicilan').val('');

                        // Tampilkan & aktifkan kembali input dibayar/kembali
                        $('.clmetode').show().find('input, select').prop('required', true);
                        $('#flexCheckDefault').off('click.prevent');
                    }
                });
                
                // Update juga ketika cicilan diubah
                $('#jmlcicilan').on('change keyup', function() {
                    cekCicilan();
                });

                activateTypeahead();
                
                $('#flexCheckDefault').on('change', function () {
                    if ($(this).is(':checked')) {
                        activateTypeahead();
                        if($('#idcustomer').val() === '') {
                            $('#nonamecustomer').val('').prop('readonly', false);
                        }
                    } else {
                        destroyTypeahead();
                        if($('#idcustomer').val() === '') {
                            $('#nonamecustomer').val('').prop('readonly', false);
                        }
                    }
                });

                $(window).keydown(function (event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        return false;
                    }
                });
                
                $('#nonamecustomer').on('input', function () {
                    selectedFromList = false;
                    $('#idcustomer').val('');
                    $('#customer').val('');
                    $('#detailcus').html('').hide();
                });
                
                let currentRequest = null;
                
                // Typeahead untuk pencarian barcode/nama barang
                $('#barcode-search').typeahead({
                    minLength: 1,
                    highlight: true,
                    source: function(query, process) {
                        if (currentRequest !== null) {
                            currentRequest.abort();
                        }
                        
                        currentRequest = $.ajax({
                            url: '{{ route('jual.getbarang') }}',
                            type: 'GET',
                            data: { q: query },
                            dataType: 'json',
                            success: function(data) {
                                barang = data;
                                // Format data untuk typeahead
                                let suggestions = data.map(function(item) {
                                    return {
                                        id: item.id,
                                        code: item.code,
                                        text: item.text,
                                        harga_jual: item.harga_jual,
                                        stok: item.stok,
                                        type: item.type,
                                        kategori_cicilan: item.kategori_cicilan,
                                        display: `${item.code} - ${item.text}`
                                    };
                                });
                                process(suggestions);
                            }
                        });
                    },
                    displayText: function(item) {
                        return item.display || item.text;
                    },
                    updater: function(item) {
                        // Ketika item dipilih, tambahkan ke tabel
                        addRow({
                            id: item.id,
                            code: item.code,
                            text: item.text,
                            harga_jual: item.harga_jual,
                            stok: item.stok,
                            type: item.type,
                            kategori_cicilan: item.kategori_cicilan
                        });
                        return item.display;
                    }
                });
                
                // Enter untuk barcode search
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        let barcode = $(this).val().trim();
                        
                        if(barcode) {
                            $.ajax({
                                url: '{{ route('jual.getbarangbycode') }}',
                                method: 'GET',
                                data: { kode: barcode },
                                dataType: 'json',
                                beforeSend: function(xhr) { loader(true); },
                                success: function(response) {
                                    addRow(response);
                                    loader(false);
                                    $('#barcode-search').val('');
                                    $('#barcode-search').typeahead('val', '');
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        position: "top-end",
                                        icon: "error",
                                        title: "Barang tidak ditemukan!",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    $('#barcode-search').val('');
                                    $('#barcode-search').typeahead('val', '');
                                    loader(false);
                                }
                            });
                        }
                    }
                });
                
                // Submit form
                $('#frmterima').on('submit', function(e) {
                    e.preventDefault(); 
                    
                    // Validasi form
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        $(this).addClass('was-validated');
                        return;
                    }
                    
                    // Validasi ada barang
                    let barangCount = $('#tbterima tbody tr').length;
                    if (barangCount === 0) {
                        Swal.fire({
                            icon: "warning",
                            title: "Tidak ada barang",
                            text: "Tambahkan minimal 1 barang terlebih dahulu",
                            showConfirmButton: true
                        });
                        return;
                    }
                    
                    // Validasi semua barang sudah dipilih
                    let semuaBarangTerpilih = true;
                    $('#tbterima tbody tr').each(function() {
                        let idbarang = $(this).find('.idbarang').val();
                        if (!idbarang || idbarang == '0') {
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
                                text: "Ada barang yang hanya boleh dicicil 1x",
                                showConfirmButton: true
                            });
                            return;
                        }
                        
                        // Validasi pembayaran khusus cicilan
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
                    var form = $('#frmterima')[0];
                    var formData = new FormData(form);

                    // Tambahkan input disabled
                    $(form).find(':input:disabled').each(function() {
                        formData.append(this.name, $(this).val());
                    });

                    $.ajax({
                        type: 'POST',
                        url: '{{ route('jual.store') }}',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function(xhr) { loader(true); },
                        success: function(response) {
                            clearform();
                            loader(false);
                            invoice();
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Nota penjualan berhasil dibuat',
                                icon: 'success',
                                confirmButtonText: 'Lihat Nota'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const url = `{{ url('/penjualan/nota') }}/${response.invoice}`;
                                    window.open(url, '_blank');
                                }
                            });
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
                
                // Hitung kembalian otomatis
                $('#dibayar').on('keyup change', function() {
                    kalkulasi();
                });
                
                // Inisialisasi invoice
                invoice();
            });
        </script>
    </x-slot>
</x-app-layout>