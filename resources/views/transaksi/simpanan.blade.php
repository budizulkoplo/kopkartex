<x-app-layout>
    <x-slot name="pagetitle">Simpanan</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Simpanan</h3>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4"> <!--begin::Header-->
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row row-cols-auto">
                                    <div class="col">
                                        <div class="input-group input-group-sm"> 
                                            <span class="input-group-text" id="basic-addon1">Periode</span> 
                                            <input type="text" class="form-control form-control-sm" id="fperiod">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="btnadd"><i class="bi bi-file-earmark-plus"></i></button>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="tbsimpanan" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>No.Anggota</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
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
        <link href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-xkQqWcEusZ1bIXoKJoItkNbJJ1LG5QwR5InghOwFLsCoEkGcNLYjE0O83wWruaK9" crossorigin="anonymous">
    </x-slot>
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js" integrity="sha384-ehaRe3xJ0fffAlDr3p72vNw3wWV01C1/Z19X6s//i6hiF8hee+c+rabqObq8YlOk" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js" integrity="sha384-G85lmdZCo2WkHaZ8U1ZceHekzKcg37sFrs4St2+u/r2UtfvSDQmQrkMsEx4Cgv/W" crossorigin="anonymous"></script>
        <script>
            var table = $('#tbsimpanan').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('simpanan.getdata') }}",
                    //"data":{loc : function() { return $('#floc').val()},role : function() { return $('#frole').val()},},
                    "type": "GET"
                },
                "columns": 
                [
                    { "data": "nomor_anggota"},
                    { "data": "nik"},
                    { "data": "name"},
                    { "data": "jenis"},
                    { "data": "jumlah"},
                    { "data": "tanggal"},
                    { "data": "id","visible": false},
                ]
            });
        </script>
    </x-slot>
</x-app-layout>
