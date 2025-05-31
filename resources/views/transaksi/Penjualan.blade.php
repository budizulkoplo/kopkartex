<x-app-layout>
    <x-slot name="pagetitle">Penjualan</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Penjualan - {{ $unit->nama_unit }}</h3>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
            <form class="row g-3 needs-validation" novalidate id="frmterima">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card card-success card-outline">
                        <div class="card-body p-1">
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Date</span>
                                <input type="text" class="form-control datepicker" name="tanggal" required>
                                <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                            </div>
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text" id="basic-addon2">Customer</span>
                                <input type="text" class="form-control" placeholder="Customer/Anggota" aria-describedby="basic-addon2" id="customer" name="customer"> 
                                <input type="hidden" id="idcustomer" name="idcustomer">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-success card-outline mb-4">
                        <div class="card-body p-1">
                            <div class="row">
                                <div class="input-group input-group-sm mb-1"> 
                                    <span class="input-group-text">Kasir</span>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" name="kasir" disabled>
                                </div>
                                <div class="input-group input-group-sm mb-1"> 
                                    <span class="input-group-text">Barang</span>
                                    <input type="text" class="form-control typeahead" id="barcode-search">
                                    <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
                                    <input type="hidden" id="barcode-id">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body p-1">
                            <p class="fs-6 text-end">Invoice <span class="fw-bold txtinv">{{ $invoice }}</span></p>
                           <p class="fs-1 fw-bold text-end mb-0 topgrandtotal"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-body p-1">
                            <table id="tbterima" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>@Harga</th>
                                        <th>Stok</th>
                                        <th>Qty</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-cols-auto">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row"> 
                                <label for="subtotal" class="col-sm-5 col-form-label">Sub Total</label>
                                <div class="col-sm-7"> 
                                    <div class="input-group input-group-sm mb-1"> 
                                        <span class="input-group-text">Rp.</span>
                                        <input type="number" class="form-control" value="0" name="subtotal" id="subtotal" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <label for="diskon" class="col-sm-5 col-form-label">Diskon</label>
                                <div class="col-sm-7">
                                <div class="input-group input-group-sm mb-1"> 
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" onkeyup="kalkulasi()" onchange="" name="diskon" id="diskon">
                                    <span class="input-group-text">%</span>
                                </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <label for="grandtotal" class="col-sm-5 col-form-label">Grand Total</label>
                                <div class="col-sm-7"> 
                                    <div class="input-group input-group-sm mb-1"> 
                                        <span class="input-group-text">Rp.</span>
                                        <input type="number" class="form-control" value="0" name="grandtotal" id="grandtotal" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="row"> 
                                <label for="metodebayar" class="col-sm-4 col-form-label">Metode</label>
                                <div class="col-sm-8"> 
                                    <select class="form-select form-select-sm" id="metodebayar" name="metodebayar" required="">
                                        <option selected="" value="tunai">Tunai</option>
                                        <option value="potong_gaji">Potong Gaji</option>
                                        <option value="cicilan">Cicilan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row"> 
                                <label for="dibayar" class="col-sm-4 col-form-label">Dibayar</label>
                                <div class="col-sm-8">
                                <div class="input-group input-group-sm mb-1"> 
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" value="0" onfocus="this.select()" name="dibayar" onkeyup="kalkulasi()" id="dibayar" required>
                                </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <label for="kembali" class="col-sm-4 col-form-label">Kembali</label>
                                <div class="col-sm-8"> 
                                    <div class="input-group input-group-sm mb-1"> 
                                        <span class="input-group-text">Rp.</span>
                                        <input type="number" class="form-control" value="0" name="kembali" id="kembali" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="input-group mb-3"> 
                        <span class="input-group-text">Catatan</span> 
                        <textarea class="form-control" aria-label="With textarea" name="note" id="note"></textarea> 
                    </div>
                    <button type="button" class="btn btn-warning" onclick="clearform();"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-floppy-fill"></i> Simpan</button>
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
            function kalkulasi(){
                let subtotal=0,grand=0;
                $('#tbterima tbody tr').each(function(index, element) {
                    var row = $(this);
                    var barangqty = row.find('.barangqty').val();
                    var hargabeli = row.find('.hargabeli').val();
                    var hargajual = row.find('.hargajual').val();
                    subtotal += (hargajual*barangqty);
                });
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
                $('#tbterima tbody tr').each(function(index, element) {
                    if(datarow.id == $(this).data('id'))
                    {boleh=false;return false;}
                });
                if(boleh){
                    str +=`<tr data-id="`+datarow.id+`" class="align-middle"><td></td><td>`+datarow.code+`</td><td>`+datarow.text+`</td>
                        <td>`+datarow.harga_jual+`
                        </td>
                        <td>`+datarow.stok+`<input type="hidden" name="stok[]" value="`+datarow.stok+`"></td>
                        <td>
                            <input type="number" value="0" class="form-control form-control-sm w-auto barangqty" onfocus="this.select()" min="1" max="`+datarow.stok+`" name="qty[]" onkeyup="kalkulasi()" required>
                            <input type="hidden" name="idbarang[]" class="idbarang" value="`+datarow.id+`">
                            <input type="hidden" name="harga_beli[]" class="hargabeli" value="`+datarow.harga_beli+`">
                            <input type="hidden" name="harga_jual[]" class="hargajual" value="`+datarow.harga_jual+`">
                        </td>
                        <td><span class="badge btn bg-danger dellist" onclick="$(this).parent().parent().remove();kalkulasi();numbering();"><i class="bi bi-trash3-fill"></i></span></td></tr>`;
                    $('#tbterima tbody').append(str);
                }
                numbering();
                $('#barcode-search').typeahead('val', '');
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
            $(document).ready(function () {
                $(window).keydown(function (event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                        return false;
                    }
                });
                // Set up the Bloodhound suggestion engine
                // var fruits = new Bloodhound({
                //     datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
                //     queryTokenizer: Bloodhound.tokenizers.whitespace,
                //     remote: {
                //     url: '{{ route('jual.getanggota') }}?q=%QUERY',
                //     wildcard: '%QUERY'
                //     }
                // });
                let users = [];
                let selectedFromList = false;
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
                $('#customer').on('input', function () {
                    selectedFromList = false;
                    $('#idcustomer').val('');
                });
                // $('#barcode-search').typeahead(
                //     {
                //     hint: true,
                //     highlight: true,
                //     minLength: 1
                //     },
                //     {
                //     name: 'fruits',
                //     display: 'code', // what to show in input box
                //     source: fruits,
                //     templates: {
                //         suggestion: function (data) {
                //         return `<div>${data.text}</div>`;
                //         }
                //     }
                //     }
                // ).bind('typeahead:select', function (ev, suggestion) {
                //     $('#barcode-id').val(suggestion.code); // save the ID in a hidden field
                //     $.ajax({
                //             url: '{{ route('jual.getbarangbycode') }}',
                //             method: 'GET',
                //             data: {
                //                 kode: suggestion.code,
                //             },
                //             dataType: 'json',
                //             success: function(response) {
                //                 addRow(response);
                //             },
                //             error: function(xhr, status, error) {
                //                 Swal.fire({
                //                 title: "Barang tidak ditemukan!",
                //                 icon: "error",
                //                 draggable: true
                //                 });
                //             }
                //         });
                    
                // });
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