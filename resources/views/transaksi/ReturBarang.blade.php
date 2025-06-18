<x-app-layout>
    <x-slot name="pagetitle">Retur Barang</x-slot>
    <div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-sm-6">
                <h3 class="mb-0">Retur Barang</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('retur.list') }}">List</a></li>
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
                    <h5 class="card-title mb-0">Form Retur Barang</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Date</span>
                                <input type="text" class="form-control datepicker" name="tgl_retur" required>
                                <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                            </div>
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Petugas</span>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Supplier</span>
                                <input type="text" class="form-control" name="nama_supplier" required>
                            </div>
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Barcode</span>
                                <input type="text" class="form-control typeahead" id="barcode-search">
                                <input type="hidden" id="barcode-id">
                                <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                            </div>
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
                                        <th>Qty</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row align-items-start">
                        <div class="col-md-8">
                            <div class="input-group input-group-sm mb-3"> 
                                <span class="input-group-text label-fixed-width">Catatan</span> 
                                <textarea class="form-control" name="note" rows="2"></textarea> 
                            </div>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
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
            $(document).on('keydown', function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });
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
                            <input type="number" value="0" class="form-control form-control-sm w-auto qty" onfocus="this.select()" min="1"  max="`+datarow.stok+`" name="qty[]" required>
                            <input type="hidden" name="id[]" value="`+datarow.id+`">
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
            $(document).ready(function () {
                let currentRequest = null;
                $('#barcode-search').typeahead({
                    source: function (query, process) {
                        if (currentRequest !== null) {
                            currentRequest.abort();
                        }
                        currentRequest = $.ajax({
                        url: '{{ route('retur.getbarang') }}',       // Your backend endpoint
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
                            url: '{{ route('retur.getbarangbycode') }}',
                            method: 'GET',
                            data: {
                                kode: $(this).val(),
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
                    if (!this.checkValidity() || $('input[type="number"].qty').length === 0) {
                        e.stopPropagation();
                    } else {
                        Swal.fire({
                            title: "Retur barang?",
                            text: "data akan disimpan!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Ya, lanjutkan!"
                            }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: '{{ route('retur.store') }}', // Your endpoint
                                    data: $(this).serialize(), // Serialize form data
                                    success: function(response) {
                                        Swal.fire({
                                        position: "top-end",
                                        icon: "success",
                                        title: "berhasil tersimpan",
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
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>