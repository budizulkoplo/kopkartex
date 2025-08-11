<x-app-layout>
    <x-slot name="pagetitle">Master Jasa Bengkel</x-slot>

    <div class="app-content-header mb-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Master Jasa Bengkel</h3>
            <button id="btnAdd" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Jasa
            </button>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbjasa" class="table table-bordered table-striped table-sm" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Jasa</th>
                                <th>Nama Jasa</th>
                                <th>Harga</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    <div class="modal fade" id="modalJasa" tabindex="-1">
        <div class="modal-dialog">
            <form id="formJasa" class="modal-content">
                @csrf
                <input type="hidden" name="idjasa" id="idjasa">
                <div class="modal-header">
                    <h5 class="modal-title">Form Jasa Bengkel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Kode Jasa</label>
                        <input type="text" name="kode_jasa" id="kode_jasa" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label>Nama Jasa</label>
                        <input type="text" name="nama_jasa" id="nama_jasa" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Harga</label>
                        <input type="number" name="harga" id="harga" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(function () {
                let table = $('#tbjasa').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('master.jasabengkel.getdata') }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'kode_jasa', name: 'kode_jasa' },
                        { data: 'nama_jasa', name: 'nama_jasa' },
                        { data: 'harga', name: 'harga', render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
                        { data: 'deskripsi', name: 'deskripsi' },
                        {
                            data: 'id',
                            render: function (data, type, row) {
                                return `
                                    <button class="btn btn-warning btn-sm btnEdit" data-id="${data}" data-kode="${row.kode_jasa}" data-nama="${row.nama_jasa}" data-harga="${row.harga}" data-deskripsi="${row.deskripsi}"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${data}"><i class="bi bi-trash"></i></button>
                                `;
                            },
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                $('#btnAdd').click(function () {
                    $('#formJasa')[0].reset();
                    $('#idjasa').val('');
                    $.get('{{ route('master.jasabengkel.getcode') }}', function (res) {
                        $('#kode_jasa').val(res);
                        $('#modalJasa').modal('show');
                    });
                });

                $('#formJasa').submit(function (e) {
                    e.preventDefault();
                    $.post('{{ route('master.jasabengkel.store') }}', $(this).serialize(), function (res) {
                        $('#modalJasa').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Sukses', 'Data berhasil disimpan', 'success');
                    }).fail(function (err) {
                        Swal.fire('Error', err.responseText, 'error');
                    });
                });

                $('#tbjasa').on('click', '.btnEdit', function () {
                    $('#idjasa').val($(this).data('id'));
                    $('#kode_jasa').val($(this).data('kode'));
                    $('#nama_jasa').val($(this).data('nama'));
                    $('#harga').val($(this).data('harga'));
                    $('#deskripsi').val($(this).data('deskripsi'));
                    $('#modalJasa').modal('show');
                });

                $('#tbjasa').on('click', '.btnHapus', function () {
                    let id = $(this).data('id');
                    Swal.fire({
                        title: 'Hapus data?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('{{ route('master.jasabengkel.hapus') }}', {_token: '{{ csrf_token() }}', id: id}, function (res) {
                                table.ajax.reload();
                                Swal.fire('Sukses', 'Data terhapus', 'success');
                            });
                        }
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>
