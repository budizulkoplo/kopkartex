<x-app-layout>
    <x-slot name="pagetitle">Mutasi Stok</x-slot>
    
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
        <form class="needs-validation" novalidate id="frmterima">
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Transfer Form</h5>
                </div>
                <div class="card-body">

                    <div class="row mb-0">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text w-25">Tanggal</span>
                                <input type="text" class="form-control datepicker" name="date" required>
                                <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text w-25">Petugas</span>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text w-25">Dari Unit</span>
                                <select class="form-select" id="unit1" name="unit1" required>
                                    <option value=""></option>
                                    @foreach ($unit as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text w-25">Ke Unit</span>
                                <select class="form-select" id="unit2" name="unit2" required>
                                    <option value=""></option>
                                    @foreach ($unit as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3" id="scnbarcode" style="display: none">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text w-25">Barcode</span>
                                <input type="text" class="form-control typeahead" id="barcode-search">
                                <input type="hidden" id="barcode-id">
                                <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                        <table id="tbterima" class="table table-sm table-striped table-bordered" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Stok</th>
                                        <th>Qty</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="input-group"> 
                                <span class="input-group-text w-25">Catatan</span> 
                                <textarea class="form-control" name="note" rows="2"></textarea> 
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-warning" onclick="clearform();"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                            <button type="submit" class="btn btn-success" id="btnsimpan" style="display: none"><i class="bi bi-floppy-fill"></i> Simpan</button>
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
        <style>
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
        </style>
    </x-slot>
    <x-slot name="jscustom">
        <script src="{{ asset('plugins/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('plugins/BootstrapDatePicker/bootstrap-datepicker.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
        <script>
            function validasi(){
                if($('#unit1').val() == '' || $('#unit2').val() == ''){
                    $('#scnbarcode, #btnsimpan').hide();
                }else{
                    if(($('#unit1').val() == $('#unit2').val())){
                        $('#scnbarcode, #btnsimpan').hide();
                    }else{
                        $('#scnbarcode, #btnsimpan').show();
                    }
                }
            }
            function numbering(){
                $('#tbterima tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
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
                        <td>
                            <input type="number" readonly value="${datarow.stok}" class="form-control form-control-sm w-auto" min="1" name="stok[]">
                        </td>
                        <td>
                            <input type="number" value="0" class="form-control form-control-sm w-auto" onfocus="this.select()" min="1" name="qty[]" max="${datarow.stok}" required>
                            <input type="hidden" class="idbarang" name="id[]" value="`+datarow.id+`">
                        </td>
                        <td><span class="badge bg-danger dellist" onclick="$(this).parent().parent().remove();numbering();"><i class="bi bi-trash3-fill"></i></span></td></tr>`;
                    $('#tbterima tbody').append(str);
                }
                numbering();
                $('#barcode-search').val('');
            }
            function clearform(){
                $('input[name="invoice"]').val('');
                $('input[name="supplier"]').val('');
                $('textarea[name="note"]').val('');
                $('#tbterima tbody tr').remove();
            }
            $(document).on('keydown', 'form', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    return false;
                }
            });
            $(document).ready(function () {
                $('#unit1, #unit2').on('change',function(){
                    validasi();
                });
                let currentRequest = null;
                $('#barcode-search').typeahead({
                    source: function (query, process) {
                        if (currentRequest !== null) {
                            currentRequest.abort();
                        }
                        currentRequest = $.ajax({
                        url: '{{ route('mutasi.getbarang') }}',       // Your backend endpoint
                        type: 'GET',
                        data: { q: query,unit: $('#unit1').val() },
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
                            $('#barcode-id').val(selected.code); // save the ID in a hidden field
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
                            url: '{{ route('mutasi.getbarangbycode') }}',
                            method: 'GET',
                            data: {
                                kode: $(this).val(),
                                unit: $('#unit1').val(),
                            },
                            dataType: 'json',
                            success: function(response) {
                                addRow(response);
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                title: "Barang tidak ditemukan!",
                                icon: "error",
                                draggable: true
                                });
                            }
                        });
                    }
                });
                $('#frmterima').on('submit', function(e) {
                    e.preventDefault(); // Prevent default form submit
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                    } else {
                        $.ajax({
                        type: 'POST',
                        url: '{{ route('mutasi.store') }}', // Your endpoint
                        data: $(this).serialize(), // Serialize form data
                        success: function(response) {
                            Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Your work has been saved",
                            showConfirmButton: false,
                            timer: 2500
                            });
                            clearform();
                        },
                        error: function(xhr) {
                            alert('Something went wrong');
                        }
                        });
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>