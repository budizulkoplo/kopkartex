<x-app-layout>
    <x-slot name="pagetitle">Users</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">User Management</h3>
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
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row row-cols-auto">
                                    <div class="col">
                                        <div class="input-group input-group-sm"> 
                                            <span class="input-group-text" id="basic-addon1">HasRole</span> 
                                            <select class="form-select form-select-sm" id="frole">
                                                <option value="all">ALL</option>
                                                @foreach ($roles as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalForm" id="btnadd"><i class="bi bi-file-earmark-plus"></i></button>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="tbusers" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>UserName</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th>Roles</th>
                                        <th></th><th></th><th></th><th></th><th></th>
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
            <form action="{{ route('users.updatepassword') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="userid" id="tuserid" required>
                    <div class="row">
                        <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">New Password</span>
                        <input type="password" name="new_password" id="tpassword" class="form-control" placeholder="" aria-label="Password" aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saverole">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    <div class="modal fade" id="exampleModalForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmusers" class="needs-validation" novalidate enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="fidusers" id="fidusers">
                    <div class="row">
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput1" class="form-label">No.Anggota</label>
                            <input type="text" class="form-control form-control-sm" id="fnomor_anggota" name="nomor_anggota" disabled>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">UserName</label>
                            <input type="text" class="form-control form-control-sm" id="fusername" name="username" disabled required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Nama</label>
                            <input type="text" class="form-control form-control-sm" id="fname" name="name" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">NIK</label>
                            <input type="number" class="form-control form-control-sm" id="fnik" name="nik" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="femail" name="email" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Jabatan</label>
                            <input type="text" class="form-control form-control-sm" id="fjabatan" name="jabatan" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput4" class="form-label">Unit Kerja</label>
                            <select class="form-select form-select-sm" name="unit_kerja" id="funit_kerja">
                                @foreach ($unit as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Tgl.Masuk</label>
                            <input type="date" class="form-control form-control-sm" id="ftanggal_masuk" name="tanggal_masuk" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="flexCheckChecked" name="status" checked>
                            <label class="form-check-label" for="flexCheckChecked">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saverole">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    <x-slot name="csscustom">
        <link href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-xkQqWcEusZ1bIXoKJoItkNbJJ1LG5QwR5InghOwFLsCoEkGcNLYjE0O83wWruaK9" crossorigin="anonymous">
    </x-slot>
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>\
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js" integrity="sha384-ehaRe3xJ0fffAlDr3p72vNw3wWV01C1/Z19X6s//i6hiF8hee+c+rabqObq8YlOk" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js" integrity="sha384-G85lmdZCo2WkHaZ8U1ZceHekzKcg37sFrs4St2+u/r2UtfvSDQmQrkMsEx4Cgv/W" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/07f649c76a.js" crossorigin="anonymous"></script>
        <script>
            
            var table = $('#tbusers').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('users.getdata') }}",
                    "data":{loc : function() { return $('#floc').val()},role : function() { return $('#frole').val()},},
                    "async": true,
                    "type": "GET"
                },
                "columns": 
                [
                    { "data": "nomor_anggota","orderable": false },
                    { "data": "username"},
                    { "data": "nik","orderable": false},
                    { "data": "name","orderable": false },
                    { "data": "email","orderable": false },
                    { "data": "nama_unit"},
                    { "data": "status"},
                    { "data": null,"orderable": false},
                    { "data": null,"orderable": false},
                    { "data": "idusers","visible": false},
                    { "data": "jabatan","visible": false},
                    { "data": "tanggal_masuk","visible": false},
                    { "data": "username","visible": false},
                    { "data": "unit_kerja","visible": false},
                ],
                "columnDefs": [
                    { targets: [ 7 ],
                        render: function (data, type, row, meta) {
                            let rls='',roles =JSON.parse(row.allrole);
                            @if (auth()->user()->hasRole('superadmin'))
                            rls +=`<div class="form-check float-start pe-2">
                            <input class="form-check-input chkrole" data-id="`+row.id+`" type="checkbox" name="chkrole[]" value="superadmin" `+(row.r1 == 'superadmin'? 'checked':'')+`>
                            <label class="form-check-label" for="flexCheckDefault">Super Admin</label>
                            </div>`
                            @endif
                            rls +=`<div class="form-check float-start pe-2">
                            <input class="form-check-input chkrole" data-id="`+row.id+`" type="checkbox" name="chkrole[]" value="admin" `+(row.r2 == 'admin'? 'checked':'')+`>
                            <label class="form-check-label" for="flexCheckDefault">Admin</label>
                            </div>`
                            rls +=`<div class="form-check float-start pe-2">
                            <input class="form-check-input chkrole" data-id="`+row.id+`" type="checkbox" name="chkrole[]" value="pengurus" `+(row.r3 == 'pengurus'? 'checked':'')+`>
                            <label class="form-check-label" for="flexCheckDefault">Pengurus</label>
                            </div>`
                            rls +=`<div class="form-check float-start pe-2">
                            <input class="form-check-input chkrole" data-id="`+row.id+`" type="checkbox" name="chkrole[]" value="bendahara" `+(row.r4 == 'bendahara'? 'checked':'')+`>
                            <label class="form-check-label" for="flexCheckDefault">Bendahara</label>
                            </div>`
                            rls +=`<div class="form-check float-start pe-2">
                            <input class="form-check-input chkrole" data-id="`+row.id+`" type="checkbox" name="chkrole[]" value="anggota" `+(row.r5 == 'anggota'? 'checked':'')+`>
                            <label class="form-check-label" for="flexCheckDefault">Anggota</label>
                            </div>`
                            
                            return rls;
                        } 
                    },
                    { targets: [ 8 ], className: 'dt-right',
                        render: function (data, type, row, meta) {
                            let str= `
                            <span class="badge rounded-pill bg-info formcell" data-bs-toggle="modal" data-bs-target="#exampleModalForm"><i class="bi bi-pencil-square"></i></span>
                            <span class="badge rounded-pill bg-warning" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="$('#tuserid').val('`+row.id+`')">
                                <i class="bi bi-key"></i></span>
                                    <span class="badge rounded-pill bg-danger"><i class="fa-solid fa-trash-can"></i></span>`
                            // return `<div class="btn-group" role="group" aria-label="Small button group">
                            //     <button type="button" class="btn btn-warning btn-sm"><i class="fa-solid fa-user-pen"></i></button>
                            //     <button type="button" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                            //     </div>`;
                            return str
                        } 
                    },
                ],
            });
            table.on( 'draw', function () {
                $('.chkrole').on('click',function(){
                    //if ($(this).is(":checked")) {
                        var selectedID = $(this).data('id');
                        let checkedValues = $(this).parent().parent().find('.chkrole:checked').map(function() {
                            return $(this).val();
                        }).get();
                        $.ajax({
                            url: "{{ route('users.assignRole') }}",
                            method:"GET",data: { iduser:selectedID,name:checkedValues },
                            success: function(response) {
                                table.ajax.reload();
                            }
                        });
                    //}
                });
                // $('.chrole').on('change',function(){
                //     if($(this).val() !=''){
                //         var selectedID = $(this).data('id'); 
                //         var selectedVal = $(this).val(); 
                //         console.log(selectedID);
                //         $.ajax({
                //             url: "{{ route('users.assignRole') }}",
                //             method:"GET",data: { iduser:selectedID,name : function() { return selectedVal}, },
                //             success: function(response) {
                //                 table.ajax.reload();
                //             }
                //         });
                //     }
                // });
            });
            function clearfrm(){
                $('#fidusers').val('');
                $('input[name="nomor_anggota"]').val('');
                $('input[name="name"]').val('');
                $('input[name="username"]').val('');
                $('input[name="username"]').prop('disabled', false);
                $('input[name="nik"]').val('');
                $('input[name="jabatan"]').val('');
                $('select[name="unit_kerja"]').val('');
                $('input[name="tanggal_masuk"]').val('');
                $('input[name="email"]').val('');
                $('#flexCheckChecked').prop('checked', true);
            }
            $('#frmusers').on('submit', function(e) {
                e.preventDefault(); // prevent default form submission
                const form = this;
                const disabled = form.querySelectorAll(':disabled');

                // Enable temporarily
                disabled.forEach(el => el.disabled = false);
                const formData = new FormData(this);
                disabled.forEach(el => el.disabled = true);
                $.ajax({
                    url: "{{ route('users.store') }}",
                    method: "POST",
                    data: formData,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                    },
                    success: function(response) {
                        table.ajax.reload();
                        $('#exampleModalForm').modal('hide');
                        clearfrm();
                    },
                    error: function(xhr, status) {
                    if (status === 'abort') {

                    } else {
                        
                    }
                    }
                });
            });
            $( document ).ready(function() {
                $('#btnadd').on('click',function(){
                    clearfrm();
                    $.ajax({
                    url: "{{ route('users.getcode') }}",method: "GET",
                    success: function(response) {
                        $('input[name="nomor_anggota"]').val(response);
                    }});
                });
                $('#tbusers tbody').on('click', '.formcell', function () {
                    var row = table.row($(this).closest('tr')).data();
                    $('#fidusers').val(row.idusers);
                    $('input[name="nomor_anggota"]').val(row.nomor_anggota);
                    $('input[name="name"]').val(row.name);
                    $('input[name="username"]').val(row.username);
                    $('input[name="username"]').prop('disabled', true);
                    $('input[name="nik"]').val(row.nik);
                    $('input[name="jabatan"]').val(row.jabatan);
                    $('select[name="unit_kerja"]').val(row.unit_kerja);
                    $('input[name="tanggal_masuk"]').val(row.tanggal_masuk);
                    $('input[name="email"]').val(row.email);
                    console.log(row.status)
                    if(row.status=='aktif'){
                        $('#flexCheckChecked').prop('checked', true);
                    }else{
                        $('#flexCheckChecked').prop('checked', false);
                    }   
                });
                $('#frole').on('change',function(){table.ajax.reload();})
                $('#floc').on('change',function(){table.ajax.reload();})
            });
        </script>
    </x-slot>
</x-app-layout>