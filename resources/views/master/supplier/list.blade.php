<x-app-layout>
    <x-slot name="pagetitle">Supplier</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3>Master Data Supplier</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4">
                        <div class="card-header pt-1 pb-1">
                            <div class="card-tools">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal" id="btnadd">
                                    <i class="bi bi-file-earmark-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tbsupplier" class="table table-sm table-striped" style="width:100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>NPWP</th>
                                        <th>Alamat</th>
                                        <th>Telp</th>
                                        <th>Kontak</th>
                                        <th>Email</th>
                                        <th>Rekening</th>
                                        <th>Bank</th>
                                        <th>Aksi</th>
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

    {{-- Modal Form --}}
    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="frmsupplier" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">Form Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idsupplier" id="idsupplier">
                        <div class="row">
                            <div class="col-md-6 mb-2"><label>Kode</label><input type="text" class="form-control form-control-sm" name="kode_supplier" required></div>
                            <div class="col-md-6 mb-2"><label>Nama</label><input type="text" class="form-control form-control-sm" name="nama_supplier" required></div>
                            <div class="col-md-6 mb-2"><label>NPWP</label><input type="text" class="form-control form-control-sm" name="npwp"></div>
                            <div class="col-md-6 mb-2"><label>Telp</label><input type="text" class="form-control form-control-sm" name="telp"></div>
                            <div class="col-md-12 mb-2"><label>Alamat</label><input type="text" class="form-control form-control-sm" name="alamat"></div>
                            <div class="col-md-6 mb-2"><label>Kontak Person</label><input type="text" class="form-control form-control-sm" name="kontak_person"></div>
                            <div class="col-md-6 mb-2"><label>Email</label><input type="email" class="form-control form-control-sm" name="email"></div>
                            <div class="col-md-6 mb-2"><label>Rekening</label><input type="text" class="form-control form-control-sm" name="rekening"></div>
                            <div class="col-md-6 mb-2"><label>Bank</label><input type="text" class="form-control form-control-sm" name="bank"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            var table = $('#tbsupplier').DataTable({
                ordering: false, responsive: true, processing: true, serverSide: true,
                ajax: "{{ route('supplier.getdata') }}",
                columns: [
                    { data: "id", visible: false },
                    { data: "kode_supplier" },
                    { data: "nama_supplier" },
                    { data: "npwp" },
                    { data: "alamat" },
                    { data: "telp" },
                    { data: "kontak_person" },
                    { data: "email" },
                    { data: "rekening" },
                    { data: "bank" },
                    { 
                        data: null, orderable: false, searchable: false,
                        render: function(row){
                            return `
                                <span class="badge rounded-pill bg-warning editcel" data-bs-toggle="modal" data-bs-target="#supplierModal"><i class="bi bi-pencil-square"></i></span>
                                <span class="badge rounded-pill bg-danger delcell"><i class="bi bi-trash3-fill"></i></span>`;
                        }
                    }
                ],
            });

            $('#frmsupplier').on('submit', function(e){
                e.preventDefault();
                $.ajax({
                    url: "{{ route('supplier.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(){
                        table.ajax.reload();
                        $('#supplierModal').modal('hide');
                        $('#frmsupplier')[0].reset();
                        Swal.fire({icon:"success", title:"Berhasil tersimpan", timer:1500, showConfirmButton:false});
                    },
                    error: function(xhr){ Swal.fire("Error!", xhr.responseText, "error"); }
                });
            });

            $('#btnadd').on('click', function(){ $('#frmsupplier')[0].reset(); $('#idsupplier').val(''); $('input[name="kode_supplier"]').prop('readonly', false); });

            $('#tbsupplier tbody').on('click', '.editcel', function(){
                var row = table.row($(this).closest('tr')).data();
                $('#idsupplier').val(row.id);
                $('input[name="kode_supplier"]').val(row.kode_supplier).prop('readonly', true);
                $('input[name="nama_supplier"]').val(row.nama_supplier);
                $('input[name="npwp"]').val(row.npwp);
                $('input[name="alamat"]').val(row.alamat);
                $('input[name="telp"]').val(row.telp);
                $('input[name="kontak_person"]').val(row.kontak_person);
                $('input[name="email"]').val(row.email);
                $('input[name="rekening"]').val(row.rekening);
                $('input[name="bank"]').val(row.bank);
            });

            $('#tbsupplier tbody').on('click', '.delcell', function(){
                var row = table.row($(this).closest('tr')).data();
                Swal.fire({
                    title: "Yakin hapus?",
                    text: row.nama_supplier,
                    icon: "warning",
                    showCancelButton: true
                }).then((result)=>{
                    if(result.isConfirmed){
                        $.ajax({
                            url: "{{ route('supplier.hapus') }}",
                            method: "DELETE",
                            data:{ id: row.id },
                            success: function(){ table.ajax.reload(); Swal.fire("Deleted!", "Supplier dihapus.", "success"); }
                        });
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
