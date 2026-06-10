<x-app-layout>
    <x-slot name="pagetitle">Bank Cash Bank</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Bank</h3>
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
                                <th>Kode</th>
                                <th>Nama Bank</th>
                                <th>No Rekening</th>
                                <th>Nama Rekening</th>
                                <th>COA</th>
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
                        <h5 class="modal-title">Form Bank</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="mb-2">
                            <label class="form-label">Kode Bank</label>
                            <input type="text" name="kode_bank" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" name="nama_bank" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nama Rekening</label>
                            <input type="text" name="nama_rekening" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">COA Bank</label>
                            <select name="coa_id" class="form-control form-control-sm">
                                <option value="">Pilih COA</option>
                                @foreach($coas as $coa)
                                    <option value="{{ $coa->id }}">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
                                @endforeach
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
                ajax: "{{ route('cashbank.banks.data') }}",
                columns: [
                    { data: 'id', visible: false },
                    { data: 'kode_bank' },
                    { data: 'nama_bank' },
                    { data: 'nomor_rekening' },
                    { data: 'nama_rekening' },
                    { data: 'coa_label' },
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
                $.post("{{ route('cashbank.banks.store') }}", $(this).serialize())
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
                $('[name=kode_bank]').val(row.kode_bank);
                $('[name=nama_bank]').val(row.nama_bank);
                $('[name=nomor_rekening]').val(row.nomor_rekening);
                $('[name=nama_rekening]').val(row.nama_rekening);
                $('[name=coa_id]').val(row.coa_id);
                $('[name=keterangan]').val(row.keterangan);
                $('#isActive').prop('checked', !!row.is_active);
                $('#formModal').modal('show');
            });

            $('#tableData tbody').on('click', '.delcell', function () {
                const row = table.row($(this).closest('tr')).data();
                Swal.fire({ title: 'Yakin hapus?', text: row.nama_bank, icon: 'warning', showCancelButton: true })
                    .then(result => {
                        if (!result.isConfirmed) return;
                        $.ajax({ url: "{{ route('cashbank.banks.delete') }}", method: 'DELETE', data: { id: row.id } })
                            .done(() => { table.ajax.reload(); Swal.fire('Terhapus', 'Bank dihapus.', 'success'); });
                    });
            });
        </script>
    </x-slot>
</x-app-layout>
