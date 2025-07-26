<x-app-layout>
    <x-slot name="pagetitle">Barang</x-slot>
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Barang</h3>
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
                                            <span class="input-group-text" id="basic-addon1">Kategori</span> 
                                            <select class="form-select form-select-sm" id="fkategori">
                                                <option value="all">ALL</option>
                                                @foreach ($kategori as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-tools"> 
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="btnadd"><i class="bi bi-file-earmark-plus"></i></button>
                            </div>
                        </div> <!--end::Header--> <!--begin::Body-->
                        <div class="card-body">
                            <table id="tbbarang" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Satuan</th>
                                        <th>HargaBeli</th>
                                        <th>HargaJual</th>
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
                    <input type="hidden" name="idbarang" id="idbarang">
                    <div class="row">
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput1" class="form-label">Kode</label>
                            <input type="text" class="form-control form-control-sm" id="exampleFormControlInput1" name="kode_barang" required>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput2" class="form-label">Nama</label>
                            <input type="text" class="form-control form-control-sm" id="exampleFormControlInput2" name="nama_barang">
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput3" class="form-label">Kategori</label>
                            <select class="form-select form-select-sm" name="kategori" id="exampleFormControlInput3">
                                @foreach ($kategori as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="exampleFormControlInput4" class="form-label">Satuan</label>
                            <select class="form-select form-select-sm" name="satuan" id="exampleFormControlInput4">
                                @foreach ($satuan as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="harga_beli" class="col-sm-5 col-form-label">Harga Beli</label>
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Rp.</span>
                                <input type="number" class="form-control" onfocus="this.select()" value="" name="harga_beli" id="harga_beli">
                            </div>
                        </div>
                        <div class="col col-lg-6 mb-1">
                            <label for="harga_jual" class="col-sm-5 col-form-label">Harga Jual</label>
                            <div class="input-group input-group-sm mb-1"> 
                                <span class="input-group-text">Rp.</span>
                                <input type="number" class="form-control" onfocus="this.select()" value="" name="harga_jual" id="harga_jual">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="savebarang">Save changes</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    <x-slot name="csscustom">
    </x-slot>
    <x-slot name="jscustom">
        <script>
            var table = $('#tbbarang').DataTable({
                ordering: false,"responsive": true,"processing": true,"serverSide": true,
                "ajax": {
                    "url": "{{ route('barang.getdata') }}",
                    "data":{kategori : function() { return $('#fkategori').val()}},
                    "type": "GET"
                },
                "columns": 
                [
                    { "data": "id","visible": false },
                    { "data": "kode_barang","orderable": false },
                    { "data": "nama_barang","orderable": false},
                    { "data": "kategori","orderable": false},
                    { "data": "satuan","orderable": false},
                    { "data": "harga_beli","orderable": false},
                    { "data": "harga_jual","orderable": false},
                    { "data": null,"orderable": false,
                        render: function (data, type, row, meta) {
                            let str= `<span class="badge rounded-pill bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bi bi-pencil-square"></i></span>
                                    <span class="badge rounded-pill bg-danger delcell"><i class="bi bi-trash3-fill"></i></span>`;
                            return str;
                        }
                    }
                ],
            });
            $('#frmbarang').on('submit', function(e) {
                e.preventDefault(); // prevent default form submission

                // Abort previous request if exists

                const formData = new FormData(this);
                $.ajax({
                    url: "{{ route('barang.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                    },
                    success: function(response) {
                        table.ajax.reload();
                        $('#exampleModal').modal('hide');
                        $('#idbarang').val('');
                        $('input[name="harga_beli"]').val(0);
                        $('input[name="harga_jual"]').val(0);
                        $('input[name="kode_barang"]').val('');
                        $('input[name="kode_barang"]').prop('disabled', false);
                        $('input[name="nama_barang"]').val('');
                        $('select[name="kategori"]').val('');
                        $('select[name="satuan"]').val('');
                        Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Berhasil tersimpan",
                        showConfirmButton: false,
                        timer: 1500
                        });
                    },
                    error: function(xhr, status, error) {
                        // If the response is JSON
                        try {
                            var json = JSON.parse(xhr.responseText);
                            Swal.fire({
                            title: "Error!",
                            text: json,
                            icon: "error"
                            });
                            $('input[name="kode_barang"]').val('');
                        } catch (e) {
                            console.warn('Response is not JSON.');
                        }
                    }
                });
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
                $('#fkategori').on('change',function(){
                    table.ajax.reload();
                });
                $('#btnadd').on('click',function(){
                    $('#idbarang').val('');
                    $('input[name="harga_beli"]').val(0);
                    $('input[name="harga_jual"]').val(0);
                    $('input[name="kode_barang"]').val('');
                    $('input[name="kode_barang"]').prop('disabled', false);
                    $('input[name="nama_barang"]').val('');
                    $('select[name="kategori"]').val('');
                    $('select[name="satuan"]').val('');
                    // $.ajax({
                    // url: "{{ route('barang.getcode') }}",method: "GET",
                    // success: function(response) {
                    //     $('input[name="kode_barang"]').val(response);
                    // }});
                });
                $('#tbbarang tbody').on('click', '.editcel', function () {
                    var row = table.row($(this).closest('tr')).data();
                    console.log(row)
                    $('#idbarang').val(row.id);
                    $('input[name="harga_beli"]').val(row.harga_beli);
                    $('input[name="harga_jual"]').val(row.harga_jual);
                    $('input[name="kode_barang"]').val(row.kode_barang);
                    $('input[name="kode_barang"]').prop('disabled', true);
                    $('input[name="nama_barang"]').val(row.nama_barang);
                    $('select[name="kategori"]').val(row.kategori);
                    $('select[name="satuan"]').val(row.satuan);
                });
                $('#tbbarang tbody').on('click', '.delcell',function() {
                    var row = table.row($(this).closest('tr')).data();
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                            url: "{{ route('barang.hapus') }}",method: "DELETE",
                            data:{id : row.id},
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                title: "Deleted!",
                                text: "Your file has been deleted.",
                                icon: "success"
                                });
                            }});
                        }
                    });
                });

            });
        </script>
    </x-slot>
</x-app-layout>