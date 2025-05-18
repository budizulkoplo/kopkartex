<x-app-layout>
    <x-slot name="pagetitle">Penerimaan</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Penerimaan</h3>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-success card-outline">
                        <div class="card-body p-1">
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Date</span>
                                <input type="text" class="form-control datepicker">
                                <span class="input-group-text bg-primary"><i class="bi bi-calendar2-week-fill text-white"></i></span>
                            </div>
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Petugas</span>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-success card-outline mb-4">
                        <div class="card-body p-1">
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Invoice</span>
                                <input type="text" class="form-control">
                            </div>
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Supplier</span>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-success card-outline mb-4">
                        <div class="card-body p-1">
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Barcode</span>
                                <input type="text" class="form-control typeahead" id="barcode-search">
                                <input type="hidden" id="barcode-id">
                                <span class="input-group-text bg-primary"><i class="bi bi-search text-white"></i></span>
                            </div>
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
                                        <th>ID</th>
                                        <th>Nama Barang</th>
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
                    <div class="input-group"> 
                        <span class="input-group-text">Catatan</span> 
                        <textarea class="form-control" aria-label="With textarea"></textarea> 
                    </div>
                </div>
                <div class="col">
                    <button type="button" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-floppy-fill"></i> Simpan</button></div>
            </div>
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
                    display: 'text', // what to show in input box
                    source: fruits,
                    templates: {
                        suggestion: function (data) {
                        return `<div>${data.text}</div>`;
                        }
                    }
                    }
                ).bind('typeahead:select', function (ev, suggestion) {
                    $('#barcode-id').val(suggestion.id); // save the ID in a hidden field
                    console.log('Selected:', suggestion);
                });
                $('.datepicker').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true
                }).datepicker('setDate', new Date());
            });
        </script>
    </x-slot>
</x-app-layout>