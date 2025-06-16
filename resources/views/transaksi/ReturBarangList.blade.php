<x-app-layout>
    <x-slot name="pagetitle">Retur List</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Data Retur</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('retur.form') }}">Form</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4"> <!--begin::Header-->
                        <div class="card-body">
                            <table id="tbbarang" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Unit</th>
                                        <th>Supplier</th>
                                        <th>Catatan</th>
                                        <th>UserInput</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmbarang" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="savebarang">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    <x-slot name="csscustom">
        <link href="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    </x-slot>
    <x-slot name="jscustom">
        <script src="{{ asset('plugins/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.js') }}"></script>
        <script>
            var table = $('#tbbarang').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('retur.datatable') }}",
                    "data":{kategori : function() { return $('#fkategori').val()}},
                    "type": "GET"
                },
                "columns": 
                [
                    { "data": "id","visible": false },
                    { "data": "tgl_retur","orderable": false},
                    { "data": "nama_unit","orderable": false },
                    { "data": "nama_supplier","orderable": false},
                    { "data": "note","orderable": false},
                    { "data": "userinput","orderable": false},
                    { "data": null,"orderable": false,
                        render: function (data, type, row, meta) {
                            let str= `<span class="badge rounded-pill bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bi bi-pencil-square"></i></span>`;
                            return str;
                        }
                    }
                ],
            });

            $(document).on('keydown', 'form', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    return false;
                }
            });
            $( document ).ready(function() {
                // $('input[name="kode_barang"]').on('change keydown', function(e) {
                //     if (e.type === 'change' || (e.type === 'keydown' && e.key === 'Enter')) {
                //         // Saat nilai input berubah
                //         cekKodeBarang($(this).val());
                //     }
                // });
                

            });
        </script>
    </x-slot>
</x-app-layout>