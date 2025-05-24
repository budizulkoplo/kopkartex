<x-app-layout>
    <x-slot name="pagetitle">Mutasi Stok</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Mutasi Stok</h3>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4"> <!--begin::Header-->
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row">
                                <div class="col-md-auto pe-1">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">Periode</span>
                                        <input type="text" id="txtperiod" class="form-control">
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="btnadd"><i class="bi bi-file-earmark-plus"></i></button>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="tbdatatable" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>From Unit</th>
                                        <th>To Unit</th>
                                        <th>Tanggal</th>
                                        <th>Petugas</th>
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
    <x-slot name="csscustom">
        <link href="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('plugins/daterangepicker-master/daterangepicker.css') }}">
    </x-slot>
    <x-slot name="jscustom">
        <script src="{{ asset('plugins/moment.min.js') }}"></script>
        <script src="{{ asset('plugins/moment-with-locales.js') }}"></script>
        <script src="{{ asset('plugins/sweetalert2@11.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTable/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('plugins/daterangepicker-master/daterangepicker.js') }}"></script>
        <script>
            let ds,de;
            var table = $('#tbdatatable').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('mutasi.getdata') }}",
                    "data":{kategori : function() { return $('#fkategori').val()}},
                    "type": "GET"
                },
                "columns": 
                [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { "data": "NamaUnit1","orderable": false },
                    { "data": "NamaUnit2","orderable": false},
                    { "data": "tanggal","orderable": false},
                    { "data": "petugas","orderable": false},
                    { "data": null,"orderable": false}
                ],
                "columnDefs": [
                    { targets: [ 5 ],
                        render: function (data, type, row, meta) {
                            let str= `<span class="badge rounded-pill bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bi bi-pencil-square"></i></span>
                                    <span class="badge rounded-pill bg-danger delcell"><i class="bi bi-trash3-fill"></i></span>`;
                            return str;
                        }
                    }]
            });
            
            $( document ).ready(function() {
                $('#fkategori').on('change',function(){
                    table.ajax.reload();
                });
                $('#txtperiod').daterangepicker({
                    opens: 'left', // Specify the position of the calendar
                    locale: {format: 'DD/MM/YYYY',},
                }, function (start, end, label) {
                    window.ds = start.format('YYYY-MM-DD');
                    window.de = end.format('YYYY-MM-DD');
                    inout();
                    lembur();
                });

            });
        </script>
    </x-slot>
</x-app-layout>