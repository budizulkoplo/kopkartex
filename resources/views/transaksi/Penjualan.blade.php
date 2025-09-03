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
                        <div class="alert alert-warning ps-2 p-0 mb-0" role="alert" id="detailcus" style="display: none"></div>
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
                                    <input type="text" class="form-control" id="customer" name="customer" required autocomplete="off">
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
                                    <input type="text" class="form-control typeahead" id="barcode-search">
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
        </style>
    </x-slot>
    <x-slot name="jscustom">
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script> --}}
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script>
            var globtot=0;
            function loader(onoff){
                if(onoff)
                $('.app-wrapper').waitMe({effect : 'bounce',text : '',bg : 'rgba(255,255,255,0.7)',color : '#000',waitTime : -1,textPos : 'vertical',});
                else
                $('.app-wrapper').waitMe('hide');
            }
            function formatRupiahWithDecimal(angka) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(angka);
            }
            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }
            function kalkulasi(obj){
                let subtotal=0,grand=0,barangtmp = [];
                $('#tbterima tbody tr').each(function(index, element) {
                    var row = $(this);
                    var barangqty = row.find('.barangqty').val();
                    var hargabeli = row.find('.hargabeli').val();
                    var hargajual = row.find('.hargajual').val();
                    var idbarang = row.find('.idbarang').val();
                    var stok = row.find('.stok').val();
                    $(this).find('.totalitm').html(hargajual*barangqty);
                    barangtmp.push({'barangqty':barangqty,'stok':parseInt(stok),'idbarang':parseInt(idbarang),'hargabeli':parseInt(hargabeli),'hargajual':parseInt(hargajual)});
                });
                let grouped = Object.values(barangtmp.reduce((acc, curr) => {
                    let key = `${curr.idbarang}_${curr.stok}_${curr.hargabeli}_${curr.hargajual}`;

                    if (!acc[key]) {
                        acc[key] = {
                            idbarang: curr.idbarang,
                            stok: curr.stok,
                            hargabeli: curr.hargabeli,
                            hargajual: curr.hargajual,
                            barangqty: 0
                        };
                    }

                    acc[key].barangqty += parseInt(curr.barangqty);
                    return acc;
                }, {}));
                $.each(grouped, function(index, item) {
                    subtotal += (item.hargajual*item.barangqty);
                });
                if(obj){
                    var cekbarang = grouped.find(item => item.idbarang === parseInt($(obj).data('id')));
                    
                    if(cekbarang.barangqty > cekbarang.stok){
                        $(obj).val(0);
                        kalkulasi();
                        Swal.fire({
                        position: 'top-end', // kanan atas
                        icon: 'warning',
                        title: 'Melebihi stok!',
                        showConfirmButton: false,
                        timer: 1500 // auto close dalam 1.5 detik
                        });
                        return;
                    }else{
                        let qty=$(obj).parent().find('.barangqty').val();
                        let harga=$(obj).parent().find('.hargajual').val();
                        $(obj).parent().find('.totalitm').html(harga*qty);
                    }
                }
                window.globtot = subtotal * (1 - ($('#diskon').val() / 100));
                $('#subtotal').val(subtotal);
                $('#grandtotal').val(subtotal * (1 - ($('#diskon').val() / 100)));
                $('.topgrandtotal').text(formatRupiahWithDecimal($('#grandtotal').val()));
                $('#dibayar').prop('min',  $('#grandtotal').val());
                $('#kembali').val($('#dibayar').val()-$('#grandtotal').val());
                
            }
            function invoice(){
                $.ajax({
                    url: '{{ route('jual.getinv') }}',method: 'GET',dataType: 'json',
                    beforeSend: function(xhr) {loader(true);},
                    success: function(response) {$('.txtinv').text(response);loader(false);},
                    error: function(xhr, status, error) {loader(false);}
                });
            }
            // Tambah row manual
            function addRow(datarow = null){
                datarow = datarow || {id:0, code:'', text:'', harga_jual:0, stok:0};
                let boleh=true;
                // $('#tbterima tbody tr').each(function(index, element) {
                //     if(datarow.id == $(this).data('id'))
                //     {boleh=false;return false;}
                // });
                 
                if(boleh){
                    let newRow = $(`<tr data-id="`+datarow.id+`" class="align-middle"><td></td><td class="kodebarangtext">`+datarow.code+`</td>
                        <td>
                            <select class="form-select form-select-sm namabarang" style="width:100%" name="idbarang[]">
                            ${datarow.id ? `<option value="${datarow.id}" selected>${datarow.text}</option>` : ''}
                            </select>
                        </td>
                        <td class="hargajualtext">`+datarow.harga_jual+`</td>
                        <td><span class="stoktext">`+datarow.stok+`</span><input type="hidden" name="stok[]" class="stok" value="`+datarow.stok+`"></td>
                        <td>
                            <input type="number" class="form-control form-control-sm w-auto barangqty" onfocus="this.select()" min="1" max="`+datarow.stok+`" name="qty[]" onkeyup="kalkulasi(this)" value="1" data-id="`+datarow.id+`" required>
                            <input type="hidden" name="harga_beli[]" class="hargabeli" value="`+datarow.harga_beli+`">
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="`+datarow.harga_jual+`">
                        </td>
                        <td class="totalitm"></td>
                        <td><span class="badge btn bg-danger dellist" onclick="$(this).parent().parent().remove();kalkulasi();numbering();"><i class="bi bi-trash3-fill"></i></span></td></tr>`);
                    $('#tbterima tbody').append(newRow);
                    numbering();
                    kalkulasi();
                    // inisialisasi select2 untuk row baru
                    newRow.find('.namabarang').select2({
                        placeholder: "Pilih barang",
                        ajax: {
                            url: '{{ route('jual.getbarang') }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) { return { q: params.term }; },
                            processResults: function(data) {
                                return {
                                    results: data.map(b => ({id: b.id, code: b.code,text: b.text, harga_beli: b.harga_beli, harga_jual: b.harga_jual, stok: b.stok}))
                                };
                            },
                            cache: true
                        }
                    }).on('select2:select', function(e){
                        let data = e.params.data;
                        console.log(data);
                        let row = $(this).closest('tr');
                        row.attr("data-id", data.id);
                        row.find('.kodebarangtext').text(data.code); // bisa juga data.code kalau ada
                        row.find('.hargajual').val(data.harga_jual);
                        row.find('.hargajualtext').text(data.harga_jual);
                        row.find('.hargabeli').val(data.harga_beli);
                        row.find('.stoktext').text(data.stok);
                        row.find('.stok').val(data.stok);
                        row.find('.barangqty').val(1);
                        row.find('.barangqty').attr("max", data.stok);
                        row.find('.barangqty').attr("data-id", data.id);
                        //row.find('.idbarang').val(data.id);
                        kalkulasi();
                    });

                    // tombol hapus
                    newRow.find('.dellist').on('click', function(){
                        $(this).closest('tr').remove();
                        numbering();
                        kalkulasi();
                    });
                }
                $('#barcode-search').val('');
            }
            function clearform(){
                $('input[name="supplier"]').val('');
                $('textarea[name="note"]').val('');
                $('#idcustomer').val('');
                $('#barcode-id').val('');
                $('#customer').val('');
                $('.topgrandtotal').text('Rp.0');
                $('#subtotal').val(0);
                $('#diskon').val(0);
                $('#grandtotal').val(0);
                $('#dibayar').val(0);
                $('#kembali').val(0);
                $('#tbterima tbody tr').remove();
                $('#metodebayar').val('tunai').trigger('change');
            }
            let users = [];
                let selectedFromList = false;
            let typeaheadEnabled = true;

            function activateTypeahead() {
                $('#detailcus').html('');
                $('#customer').typeahead({
                    minLength: 2,
                    displayText: function(item) {
                        return item.name;
                    },
                    source: function(query, process) {
                        return $.get('{{ route('jual.getanggota') }}', { query: query }, function(data) {
                            return process(data);
                        });
                    },
                    afterSelect: function(item) {
                        // tampilkan detail setelah pilih
                        $('#detailcus').html(`<table>
                            <tr><td>Nomor Anggota</td><td>: ${item.nomor_anggota}</td></tr>
                            <tr><td>Sisa Limit Hutang</td><td>: ${formatRupiahWithDecimal(item.limit_hutang)}</td></tr>
                            </table>
                        `);
                        $('#idcustomer').val(item.id);
                        $('#detailcus').show();
                    }
                });
                // $('#customer').typeahead({
                //     source: function (query, process) {
                //         return $.ajax({
                //         url: '{{ route('jual.getanggota') }}',       // Your backend endpoint
                //         type: 'GET',
                //         data: { query: query },
                //         dataType: 'json',
                //         success: function (data) {
                //             users = data; // Save for lookup later
                //             return process(data.map(user => user.name));
                //         }
                //         });
                //     },
                //     afterSelect: function (name) {
                //         const selected = users.find(user => user.name === name);
                //         if (selected) {
                //         $('#idcustomer').val(selected.id);
                //         selectedFromList = true;
                //         }
                //     }
                // });
            }
            function destroyTypeahead() {
                $('#customer').typeahead('destroy');
                $('#detailcus').html('').hide();
                typeaheadEnabled = false;
            }
            $('.fieldcicilan').hide();
            $(document).ready(function () {
                // Tambah row button
                $('#tambahBarang').on('click', function(){
                    addRow({id:0, code:'', text:'', harga_jual:0, stok:0});
                });
                $('#metodebayar').on('change',function(){
                    if($(this).val() == 'cicilan'){
                        let str = '';
                        $('.fieldcicilan').show();
                        let maxcicil=0;
                        if(window.globtot <= 1000000) {maxcicil=3;}
                        else if(window.globtot > 1000000 && window.globtot <= 2000000){maxcicil=5;}
                        else if(window.globtot > 2000000 && window.globtot <= 3000000){maxcicil=10;}
                        else if(window.globtot > 3000000 && window.globtot <= 4000000){maxcicil=15;}
                        else if(window.globtot > 4000000 && window.globtot <= 5000000){maxcicil=20;}
                        else if(window.globtot > 5000000){maxcicil=25;}

                        for (let index = 1; index <= maxcicil; index++) {
                            str += `<option value='${index}'>${index}x</option>`;
                        }
                        $('#jmlcicilan').html(str);

                        // Sembunyikan & nonaktifkan input dibayar/kembali
                        $('.clmetode').hide().find('input, select').prop('required', false).val('');
                        $('#flexCheckDefault')
                        .prop('checked', true)
                        .off('click.prevent') // hapus event lama kalau ada
                        .on('click.prevent', function(e) {
                            e.preventDefault(); // kunci
                        }).change();
                        // if($('#customer').val() === '' || $('#idcustomer').val() === '' ){
                        //     $('#flexCheckDefault')
                        // }
                    } else {
                        $('.fieldcicilan').hide();
                        $('#jmlcicilan').html('');

                        // Tampilkan & aktifkan kembali input dibayar/kembali
                        $('.clmetode').show().find('input, select').prop('required', true);
                        $('#flexCheckDefault').off('click.prevent').change();
                    }
                });

                activateTypeahead();
                $('#flexCheckDefault').on('change', function () {
                    if ($(this).is(':checked')) {
                        activateTypeahead();
                        
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                        //$('#customer').attr('required', true);
                    } else {
                        destroyTypeahead();
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                        //$('#customer').removeAttr('required');
                    }
                });
                $(window).keydown(function (event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        return false;
                    }
                });
                
                
                $('#customer').on('input', function () {
                    selectedFromList = false;
                    $('#idcustomer').val('');
                });
                let currentRequest = null;
                $('#barcode-search').typeahead({
                    source: function (query, process) {
                        if (currentRequest !== null) {
                            currentRequest.abort();
                        }
                        currentRequest = $.ajax({
                        url: '{{ route('jual.getbarang') }}',       // Your backend endpoint
                        type: 'GET',
                        data: { q: query },
                        dataType: 'json',
                        success: function (data) {
                            barang = data; // Save for lookup later
                            return process(data.map(barang => barang.text));
                        }
                        });
                        return currentRequest;
                    },
                    afterSelect: function (text) {
                        const selected = barang.find(barang => barang.text === text);
                        if (selected) {
                            addRow(selected);
                        }
                    }
                });
                
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        $.ajax({
                            url: '{{ route('jual.getbarangbycode') }}',
                            method: 'GET',
                            data: {
                                kode: $(this).val(),
                            },
                            dataType: 'json',
                            beforeSend: function(xhr) {loader(true);},
                            success: function(response) {
                                addRow(response);
                                loader(false);
                                $('#barcode-search').val('');
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
                                loader(false);
                            }
                        });
                    }
                });
                $('#frmterima').on('submit', function(e) {
                    e.preventDefault(); // Prevent default form submit
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                    } else {
                        let checked = $('#flexCheckDefault').is(':checked');
                        if ($('#metodebayar').val() === 'cicilan' && $('#idcustomer').val() == '') {
                            Swal.fire({
                                position: "top-end",
                                icon: "warning",
                                title: "Anggota harus terisi",
                                showConfirmButton: false,
                                timer: 2500
                                });
                            e.stopPropagation();
                        } else {
                            Swal.fire({
                            title: "Transaksi sekarang?",
                            text: "Pastikan data sudah benar",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Ya, lanjutkan!"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var form = $(this)[0];
                                    var formData = new FormData(form);

                                    // Manually append disabled inputs
                                    $(form).find(':input:disabled').each(function() {
                                        formData.append(this.name, $(this).val());
                                    });

                                    $.ajax({
                                    type: 'POST',
                                    url: '{{ route('jual.store') }}', // Your endpoint
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    beforeSend: function(xhr) {loader(true);},
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
                                        Swal.fire({
                                            title: "Error!",text: xhr.responseText,icon: "error"
                                        });
                                    }
                                    });
                                }else{
                                    e.stopPropagation();
                                }
                            });
                        }
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>