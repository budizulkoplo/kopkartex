<x-app-layout>
    <x-slot name="pagetitle">Penerimaan</x-slot>
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
        <form class="needs-validation" novalidate id="frmterima">
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Penerimaan</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Date</span>
                                <input type="text" class="form-control datepicker" name="date" required>
                                <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                            </div>
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Petugas</span>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Invoice</span>
                                <input type="text" class="form-control" name="invoice" required>
                            </div>
                            <div class="input-group input-group-sm mb-2"> 
                                <span class="input-group-text label-fixed-width">Supplier</span>
                                <input type="text" class="form-control" name="supplier" required>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                            <table id="tbterima" class="table table-sm table-striped" style="width: 100%; font-size: small;">
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
        <script src="{{ asset('plugins/typeahead.bundle.js') }}"></script>
        <script>
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
                            <input type="number" value="0" class="form-control form-control-sm w-auto" onfocus="this.select()" min="1" name="qty[]" required>
                            <input type="hidden" name="id[]" value="`+datarow.id+`">
                        </td>
                        <td><span class="badge bg-danger dellist" onclick="$(this).parent().parent().remove();numbering();"><i class="bi bi-trash3-fill"></i></span></td></tr>`;
                    $('#tbterima tbody').append(str);
                }
                numbering();
                $('#barcode-search').typeahead('val', '');
            }
            function clearform(){
                $('input[name="invoice"]').val('');
                $('input[name="supplier"]').val('');
                $('textarea[name="note"]').val('');
                $('#tbterima tbody tr').remove();
            }
            $(document).ready(function () {
                // Set up the Bloodhound suggestion engine
                var fruits = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: {
                    url: '{{ route('penerimaan.getbarang') }}?q=%QUERY',
                    wildcard: '%QUERY'
                    }
                });

                $('#barcode-search').typeahead(
                    {
                    hint: true,
                    highlight: true,
                    minLength: 1
                    },
                    {
                    name: 'fruits',
                    display: 'code', // what to show in input box
                    source: fruits,
                    templates: {
                        suggestion: function (data) {
                        return `<div>${data.text}</div>`;
                        }
                    }
                    }
                ).bind('typeahead:select', function (ev, suggestion) {
                    $('#barcode-id').val(suggestion.code); // save the ID in a hidden field
                    addRow(suggestion);
                    
                });
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());
                $('#barcode-search').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        $.ajax({
                            url: '{{ route('penerimaan.getbarangbycode') }}',
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
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                    } else {
                        $.ajax({
                        type: 'POST',
                        url: '{{ route('penerimaan.store') }}', // Your endpoint
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