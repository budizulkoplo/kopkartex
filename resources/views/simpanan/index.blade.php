<x-app-layout>
    <x-slot name="pagetitle">Simpanan</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Simpanan Anggota</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate" id="btnadd">
                        <i class="bi bi-plus-circle"></i> Buka Rekening
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tbsimpanan" class="table table-sm table-striped" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No. Anggota</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>No. Rekening</th>
                                <th>Jenis</th>
                                <th>Saldo</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Simpanan --}}
    <div class="modal fade" id="modalCreate" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form id="formCreate" method="POST" action="{{ route('simpanan.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title">Tambah Simpanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-2">
                            <label class="form-label">Anggota</label>
                            <select name="id_anggota" class="form-select form-select-sm" required>
                                @foreach($anggota as $a)
                                    <option value="{{ $a->id }}">{{ $a->nomor_anggota }} - {{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Nama Pemilik</label>
                            <input type="text" class="form-control form-control-sm" name="nama_pemilik" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Jenis Simpanan</label>
                            <select name="jenis_simpanan" class="form-select form-select-sm" required>
                                <option value="Simpanan Pokok">Simpanan Pokok</option>
                                <option value="Simpanan Wajib">Simpanan Wajib</option>
                                <option value="Simpanan Sukarela">Simpanan Sukarela</option>
                                <option value="Simpanan Kelompok">Simpanan Kelompok</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Nominal Awal</label>
                            <input type="number" class="form-control form-control-sm" name="saldo" required>
                        </div>

                    </div>
                    <div class="modal-footer py-2">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- JS Custom --}}
    <x-slot name="jscustom">
        <script>
            var table = $('#tbsimpanan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('simpanan.getdata') }}",
                    type: "GET",
                    dataSrc: "" 
                },
                columns: [
                    { data: "anggota.nomor_anggota" },
                    { data: "anggota.nik" },
                    { data: "anggota.name" },
                    { data: "norek" },
                    { data: "jenis_simpanan" },
                    { data: "saldo", render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
                    { data: "created_at", render: function(data){ return new Date(data).toLocaleDateString('id-ID'); }},
                    { 
                        data: "idsimpanan",
                        className: "text-center",
                        render: function(id){
                            return `
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-success" onclick="setoran(${id})" title="Setoran">
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
                                    <button class="btn btn-info" onclick="ringkasan(${id})" title="Ringkasan">
                                        <i class="bi bi-card-list"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="tutupRekening(${id})" title="Tutup Rekening">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // submit form via ajax
            $('#formCreate').on('submit', function(e){
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res){
                        $('#modalCreate').modal('hide');
                        table.ajax.reload();
                        alert(res.message);
                    },
                    error: function(xhr){
                        alert("Gagal: " + xhr.responseJSON.message);
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
