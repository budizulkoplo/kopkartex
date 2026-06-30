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
                                <th>Kelompok</th>
                                <th>Jenis</th>
                                <th>Kas/Bank</th>
                                <th>Subledger</th>
                                <th>Level</th>
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
                            <label class="form-label">Kelompok Laporan</label>
                            <input type="text" name="tipe" class="form-control form-control-sm" list="coaTipeList" placeholder="AKTIVA LANCAR" required>
                            <datalist id="coaTipeList">
                                <option value="AKTIVA">
                                <option value="AKTIVA LANCAR">
                                <option value="AKTIVA TETAP">
                                <option value="KEWAJIBAN LANCAR">
                                <option value="HUTANG">
                                <option value="PENDAPATAN">
                                <option value="BIAYA">
                            </datalist>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Jenis Akun</label>
                            <select name="att5" class="form-control form-control-sm">
                                <option value="D">Detail - bisa dipakai transaksi</option>
                                <option value="H">Header - kelompok/induk</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Level</label>
                                <input type="text" name="att3" class="form-control form-control-sm" placeholder="1">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Kas/Bank</label>
                                <select name="att4" class="form-control form-control-sm">
                                    <option value="">-</option>
                                    <option value="KAS">KAS</option>
                                    <option value="BANK">BANK</option>
                                    <option value="CASH">CASH</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Aktif</label>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                    <label class="form-check-label" for="isActive">Ya</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Kode Referensi</label>
                                <input type="text" name="att1" class="form-control form-control-sm" list="coaAtt1List" placeholder="no_agt / supcode">
                                <datalist id="coaAtt1List">
                                    <option value="no_agt">
                                    <option value="supcode">
                                </datalist>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Master Referensi</label>
                                <input type="text" name="att2" class="form-control form-control-sm" list="coaAtt2List" placeholder="manggota / msupplier">
                                <datalist id="coaAtt2List">
                                    <option value="manggota">
                                    <option value="msupplier">
                                </datalist>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="2"></textarea>
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
                    { data: 'jenis_akun', render: (data, type, row) => row.att5 === 'H' ? '<span class="badge bg-secondary">Header</span>' : (row.att5 === 'D' ? '<span class="badge bg-success">Detail</span>' : '-') },
                    { data: 'att4', render: data => data ? `<span class="badge bg-info text-dark">${data}</span>` : '-' },
                    { data: null, render: row => [row.att1, row.att2].filter(Boolean).join(' / ') || '-' },
                    { data: 'att3', render: data => data || '-' },
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
                $('[name=att1]').val(row.att1);
                $('[name=att2]').val(row.att2);
                $('[name=att3]').val(row.att3);
                $('[name=att4]').val(row.att4);
                $('[name=att5]').val(row.att5 || 'D');
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
                            .done(() => { table.ajax.reload(); Swal.fire('Terhapus', 'COA dihapus.', 'success'); })
                            .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message || xhr.responseText, 'error'));
                    });
            });
        </script>
    </x-slot>
</x-app-layout>
