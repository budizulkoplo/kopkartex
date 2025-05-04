<x-app-layout>
    <x-slot name="pagetitle">Unit</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Unit</h3>
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
                            </div>
                            <div class="card-tools"> 
                                <a href="{{ route('unit.add') }}" class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-plus"></i></a>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="example" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>Nama.</th>
                                        <th>Jenis</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($units as $item)
                                        <tr>
                                            <td>{{ $item->nama_unit }}</td>
                                            <td>{{ $item->jenis }}</td>
                                            <td>
                                                <a href="{{ route('unit.edit',['id' => Crypt::encryptString($item->id)]) }}"><i class="fa-solid fa-user-pen"></i></a>
                                                {{-- <span class="badge rounded-pill bg-danger"><i class="fa-solid fa-trash-can"></i></span> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
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
    <x-slot name="csscustom">
        <link href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-xkQqWcEusZ1bIXoKJoItkNbJJ1LG5QwR5InghOwFLsCoEkGcNLYjE0O83wWruaK9" crossorigin="anonymous">
    </x-slot>
    <x-slot name="jscustom">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js" integrity="sha384-ehaRe3xJ0fffAlDr3p72vNw3wWV01C1/Z19X6s//i6hiF8hee+c+rabqObq8YlOk" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js" integrity="sha384-G85lmdZCo2WkHaZ8U1ZceHekzKcg37sFrs4St2+u/r2UtfvSDQmQrkMsEx4Cgv/W" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/07f649c76a.js" crossorigin="anonymous"></script>
        <script>
            var table = $('#example').DataTable({
                ordering: false,"responsive": true
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
            $( document ).ready(function() {
                $('#frole').on('change',function(){table.ajax.reload();})
                $('#floc').on('change',function(){table.ajax.reload();})
            });
        </script>
    </x-slot>
</x-app-layout>