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
                                <a class="btn btn-sm btn-primary" href="{{ route('mutasi.form') }}" role="button"><i class="bi bi-file-earmark-plus"></i></a>
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
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="frmbarang" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idmutasi" id="idmutasi">
                    <table id="tbdtl" class="table table-sm table-bordered" style="width: 100%; font-size: small;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Mutasi.Qty</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </form>
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
            const currentDate = moment().format('YYYY-MM-DD');
            var ds=currentDate,de=currentDate;
            var table = $('#tbdatatable').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('mutasi.getdata') }}",
                    "data":{startdate : function() { return window.ds},enddate : function() { return window.de}},
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
                            let str= `<span class="badge rounded-pill btn bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="dtl('${row.id}')"><i class="fa-solid fa-circle-info"></i></span>`;
                            return str;
                        }
                    }]
            });
            function dtl(idmutasi){
                $.ajax({
                    type: 'GET',
                    url: '{{ route('mutasi.dtl') }}', // Your endpoint
                    data: {id:idmutasi}, // Serialize form data
                    success: function(response) {
                        let str='',cn=1;
                        $.each(response, function(index, value) {
                            str += `<tr class="align-middle"><td>${cn}</td><td>${value.kode_barang}</td><td>${value.nama_barang}</td><td>${value.qty}</td>`;
                                if(value.canceled == 0){
                                    str += `<td><span class="badge rounded-pill btn bg-warning" onclick="kembalikan(${value.id},${value.barang_id})"><i class="fa-solid fa-clock-rotate-left"></i></span></td>`;
                                }else{
                                    str += `<td><i class="fa-solid fa-ban" style="color: #f80d0d;"></i></td>`;
                                }
                            str +=`</tr>`
                            cn++;
                        });
                        $('#tbdtl tbody').html(str);
                    },
                    error: function(xhr) {
                        alert('Something went wrong');
                    }
                });
            }
            function kembalikan(idmutasi,idbarang){
                Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes!"
                }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('mutasi.kembalikan') }}', // Your endpoint
                        data: {idmutasi:idmutasi,idbarang:idbarang}, // Serialize form data
                        success: function(response) {
                            Swal.fire({
                            title: "Success!",
                            text: "Stok berhasil dikembalikan.",
                            icon: "success"
                            });
                            dtl(idmutasi);
                        },
                        error: function(xhr) {
                            alert('Something went wrong');
                        }
                    });
                }
                });
            }
            $( document ).ready(function() {
                $('#txtperiod').daterangepicker({
                    opens: 'left', // Specify the position of the calendar
                    locale: {format: 'DD/MM/YYYY',},
                }, function (start, end, label) {
                    window.ds = start.format('YYYY-MM-DD');
                    window.de = end.format('YYYY-MM-DD');
                    inout();
                    lembur();
                });
                $('#txtperiod').on('change',function(){
                    table.ajax.reload();
                });
            });
        </script>
    </x-slot>
</x-app-layout>