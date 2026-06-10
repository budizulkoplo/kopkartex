<x-app-layout>
    <x-slot name="pagetitle">Kode Akun COA Cash Bank</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Kode Akun COA</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-header py-2">
                    <button class="btn btn-sm btn-primary" id="btnAdd" data-bs-toggle="modal" data-bs-target="#formModal">
                        <i class="bi bi-file-earmark-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body">
                    <table id="tableData" class="table table-sm table-striped" style="width:100%; font-size: small;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Tipe</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formData">
                    <div class="modal-header">
                        <h5 class="modal-title">Form COA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="mb-2">
                            <label class="form-label">Kode Akun</label>
                            <input type="text" name="kode_akun" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nama Akun</label>
                            <input type="text" name="nama_akun" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tipe</label>
                            <select name="tipe" class="form-control form-control-sm" required>
                                <option value="kas">Kas</option>
                                <option value="bank">Bank</option>
                                <option value="hutang">Hutang</option>
                                <option value="biaya">Biaya</option>
                                <option value="pendapatan">Pendapatan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            const table = $('#tableData').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: "{{ route('cashbank.coas.data') }}",
                columns: [
                    { data: 'id', visible: false },
                    { data: 'kode_akun' },
                    { data: 'nama_akun' },
                    { data: 'tipe' },
                    { data: 'keterangan' },
                    { data: 'is_active', render: data => data ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' },
                    { data: null, orderable: false, searchable: false, render: () => '<span class="badge rounded-pill bg-warning editcell"><i class="bi bi-pencil-square"></i></span> <span class="badge rounded-pill bg-danger delcell"><i class="bi bi-trash3-fill"></i></span>' }
                ]
            });

            $('#btnAdd').on('click', function () {
                $('#formData')[0].reset();
                $('[name=id]').val('');
                $('#isActive').prop('checked', true);
            });

            $('#formData').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('cashbank.coas.store') }}", $(this).serialize())
                    .done(function () {
                        $('#formModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1200, showConfirmButton: false });
                    })
                    .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
            });

            $('#tableData tbody').on('click', '.editcell', function () {
                const row = table.row($(this).closest('tr')).data();
                $('[name=id]').val(row.id);
                $('[name=kode_akun]').val(row.kode_akun);
                $('[name=nama_akun]').val(row.nama_akun);
                $('[name=tipe]').val(row.tipe);
                $('[name=keterangan]').val(row.keterangan);
                $('#isActive').prop('checked', !!row.is_active);
                $('#formModal').modal('show');
            });

            $('#tableData tbody').on('click', '.delcell', function () {
                const row = table.row($(this).closest('tr')).data();
                Swal.fire({ title: 'Yakin hapus?', text: row.kode_akun, icon: 'warning', showCancelButton: true })
                    .then(result => {
                        if (!result.isConfirmed) return;
                        $.ajax({ url: "{{ route('cashbank.coas.delete') }}", method: 'DELETE', data: { id: row.id } })
                            .done(() => { table.ajax.reload(); Swal.fire('Terhapus', 'COA dihapus.', 'success'); });
                    });
            });
        </script>
    </x-slot>
</x-app-layout>
