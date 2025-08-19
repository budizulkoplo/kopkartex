<x-app-layout>
    <x-slot name="pagetitle">Barang</x-slot>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Master Data Barang</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
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

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline mb-4">
                        <div class="card-header pt-1 pb-1">
                            <div class="card-title">
                                <div class="row row-cols-auto">
                                    <div class="col">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Kategori</span>
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
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" id="btnadd">
                                    <i class="bi bi-file-earmark-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tbbarang" class="table table-sm table-striped" style="width:100%; font-size: small;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode</th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Satuan</th>
                                        <th>HargaBeli</th>
                                        <th>HargaJual</th>
                                        <th>Foto</th>
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
    <div class="modal fade" id="exampleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="frmbarang" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Form Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="idbarang" id="idbarang">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kode</label>
                                <input type="text" class="form-control form-control-sm" name="kode_barang" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control form-control-sm" name="nama_barang" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kategori</label>
                                <select class="form-select form-select-sm" name="kategori">
                                    @foreach ($kategori as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Satuan</label>
                                <select class="form-select form-select-sm" name="satuan">
                                    @foreach ($satuan as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_beli">
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Harga Jual</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="harga_jual">
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Foto Produk</label>
                                <input type="file" class="form-control form-control-sm" name="img" accept="image/*">
                                <div class="mt-2">
                                    <img id="previewImg" src="" style="max-height:120px;" class="img-thumbnail d-none">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="savebarang">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            var table = $('#tbbarang').DataTable({
                ordering: false, responsive: true, processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('barang.getdata') }}",
                    data: { kategori: function() { return $('#fkategori').val() }},
                    type: "GET"
                },
                columns: [
                    { data: "id", visible: false },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "kategori" },
                    { data: "satuan" },
                    { data: "harga_beli" },
                    { data: "harga_jual" },
                    { 
                        data: "img", orderable: false, searchable: false,
                        render: function(data) {
                            if (data) {
                                return `<img src="/storage/produk/${data}" class="img-thumbnail" style="max-height:60px;">`;
                            }
                            return `<span class="text-muted">-</span>`;
                        }
                    },
                    {
                        data: null,
                        render: function (row) {
                            return `
                                <span class="badge rounded-pill bg-warning editcel" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bi bi-pencil-square"></i></span>
                                <span class="badge rounded-pill bg-danger delcell"><i class="bi bi-trash3-fill"></i></span>`;
                        }
                    }
                ],
            });

            // Submit form
            $('#frmbarang').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: "{{ route('barang.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        table.ajax.reload();
                        $('#exampleModal').modal('hide');
                        $('#frmbarang')[0].reset();
                        $('#previewImg').attr('src','').addClass('d-none');
                        Swal.fire({ icon:"success", title:"Berhasil tersimpan", timer:1500, showConfirmButton:false });
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", xhr.responseText, "error");
                    }
                });
            });

            // Preview foto
            $('input[name="img"]').on('change', function(evt) {
                const [file] = this.files;
                if (file) {
                    $('#previewImg').removeClass('d-none').attr('src', URL.createObjectURL(file));
                }
            });

            // Reset saat tambah
            $('#btnadd').on('click', function(){
                $('#frmbarang')[0].reset();
                $('#idbarang').val('');
                $('#previewImg').attr('src','').addClass('d-none');
                $('input[name="kode_barang"]').prop('readonly', false);
            });

            // Edit
            $('#tbbarang tbody').on('click', '.editcel', function () {
                var row = table.row($(this).closest('tr')).data();
                $('#idbarang').val(row.id);
                $('input[name="kode_barang"]').val(row.kode_barang).prop('readonly', true);
                $('input[name="nama_barang"]').val(row.nama_barang);
                $('select[name="kategori"]').val(row.kategori);
                $('select[name="satuan"]').val(row.satuan);
                $('input[name="harga_beli"]').val(row.harga_beli);
                $('input[name="harga_jual"]').val(row.harga_jual);
                if(row.img){
                    $('#previewImg').removeClass('d-none').attr('src','/storage/produk/'+row.img);
                } else {
                    $('#previewImg').attr('src','').addClass('d-none');
                }
            });

            // Delete
            $('#tbbarang tbody').on('click', '.delcell', function() {
                var row = table.row($(this).closest('tr')).data();
                Swal.fire({
                    title: "Yakin hapus?",
                    text: row.nama_barang,
                    icon: "warning",
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('barang.hapus') }}",
                            method: "DELETE",
                            data:{ id: row.id },
                            success: function() {
                                table.ajax.reload();
                                Swal.fire("Deleted!", "Barang dihapus.", "success");
                            }
                        });
                    }
                });
            });

            $('#fkategori').on('change', function(){ table.ajax.reload(); });
        </script>
    </x-slot>
</x-app-layout>
