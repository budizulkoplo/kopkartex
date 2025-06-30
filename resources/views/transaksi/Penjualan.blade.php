<x-app-layout>
    <x-slot name="pagetitle">Penjualan</x-slot>
    <div class="app-content">
        <div class="container">
            <form class="needs-validation" novalidate id="frmterima" autocomplete="off">
                <div class="card card-success card-outline mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Penjualan - {{ $unit->nama_unit }}</h5>
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
                                    <input type="text" class="form-control" id="customer" name="customer">
                                    <input type="hidden" id="idcustomer" name="idcustomer">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group input-group-sm mb-2"> 
                                    <span class="input-group-text label-fixed-width">Kasir</span>
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
                                        <option value="potong_gaji">Potong Gaji</option>
                                        <option value="cicilan">Cicilan</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text label-fixed-width">Dibayar</span>
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" name="dibayar" id="dibayar" required>
                                </div>
                                <div class="input-group input-group-sm mb-2">
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
        <link href="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('plugins/BootstrapDatePicker/bootstrap-datepicker.min.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('plugins/loader/waitMe.css') }}">
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
        <script src="{{ asset('plugins/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('plugins/BootstrapDatePicker/bootstrap-datepicker.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script src="{{ asset('plugins/loader/waitMe.js') }}"></script>
        <script>
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
                    //console.log(cekbarang ,$(obj).data('id'))
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
            function addRow(datarow){
                let str = '',boleh=true;
                // $('#tbterima tbody tr').each(function(index, element) {
                //     if(datarow.id == $(this).data('id'))
                //     {boleh=false;return false;}
                // });
                if(boleh){
                    str +=`<tr data-id="`+datarow.id+`" class="align-middle"><td></td><td>`+datarow.code+`</td><td>`+datarow.text+`</td>
                        <td>`+datarow.harga_jual+`
                        </td>
                        <td>`+datarow.stok+`<input type="hidden" name="stok[]" class="stok" value="`+datarow.stok+`"></td>
                        <td>
                            <input type="number" class="form-control form-control-sm w-auto barangqty" onfocus="this.select()" min="1" max="`+datarow.stok+`" name="qty[]" onkeyup="kalkulasi(this)" value="1" data-id="`+datarow.id+`" required>
                            <input type="hidden" name="idbarang[]" class="idbarang" value="`+datarow.id+`">
                            <input type="hidden" name="harga_beli[]" class="hargabeli" value="`+datarow.harga_beli+`">
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="`+datarow.harga_jual+`">
                        </td>
                        <td class="totalitm"></td>
                        <td><span class="badge btn bg-danger dellist" onclick="$(this).parent().parent().remove();kalkulasi();numbering();"><i class="bi bi-trash3-fill"></i></span></td></tr>`;
                    $('#tbterima tbody').append(str);
                    kalkulasi();
                }
                numbering();
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
            }
            let users = [];
                let selectedFromList = false;
            let typeaheadEnabled = true;

            function activateTypeahead() {
                $('#customer').typeahead({
                    source: function (query, process) {
                        return $.ajax({
                        url: '{{ route('jual.getanggota') }}',       // Your backend endpoint
                        type: 'GET',
                        data: { query: query },
                        dataType: 'json',
                        success: function (data) {
                            users = data; // Save for lookup later
                            return process(data.map(user => user.name));
                        }
                        });
                    },
                    afterSelect: function (name) {
                        const selected = users.find(user => user.name === name);
                        if (selected) {
                        $('#idcustomer').val(selected.id);
                        selectedFromList = true;
                        }
                    }
                });
            }
            function destroyTypeahead() {
                $('#customer').typeahead('destroy');
                typeaheadEnabled = false;
            }
            $(document).ready(function () {
                activateTypeahead();
                $('#flexCheckDefault').on('change', function () {
                    if ($(this).is(':checked')) {
                        activateTypeahead();
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
                    } else {
                        destroyTypeahead();
                        $('#customer').val('').prop('readonly', false);
                        $('#idcustomer').val('');
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
                            Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Your work has been saved",
                            showConfirmButton: false,
                            timer: 2500
                            });
                            clearform();
                            loader(false);
                            invoice();
                            const url = `{{ url('/penjualan/nota') }}/${response.invoice}`;
                            window.open(url, '_blank');
                        },
                        error: function(xhr) {
                            alert('Something went wrong');loader(false);
                        }
                        });
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>