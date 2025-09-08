<x-app-layout>
    <x-slot name="pagetitle">Stock Opname</x-slot>

    <div class="app-content-header mb-3">
        <div class="container-fluid">
            <h3 class="mb-0">Daftar Barang - Stock Opname</h3>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <span class="fw-bold">Unit: {{ auth()->user()->unit->nama_unit ?? '-' }}</span>
                </div>

                <div class="card-body">
                    {{-- Filter Bulan & Mulai Opname --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <form method="GET" action="{{ route('stockopname.index') }}" class="d-flex">
                            <div class="input-group input-group-sm">
                                <input type="month" name="bulan" value="{{ $bulan }}" class="form-control">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </form>

                        <form id="formMulaiOpname" method="POST" action="{{ route('stockopname.mulai') }}" class="d-flex">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="date" name="tgl_opname" value="{{ now()->format('Y-m-d') }}" class="form-control">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-play-circle"></i> Start Opname
                                </button>
                            </div>
                        </form>

                        <a href="{{ route('mobile.stokopname.index') }}" class="btn btn-primary btn-sm d-flex align-items-center">
                            <i class="bi bi-upc-scan me-1"></i> Scan Opname (Mobile)
                        </a>
                    </div>

                    <div class="card p-2 mb-3 border border-info" style="background-color:#e9f7ff;">
                        <form id="formScan" class="d-flex gap-2">
                            <input type="text" id="kodeScan" name="kode" 
                                class="form-control form-control-sm"
                                placeholder="🔍 Scan / Input Kode Barang"
                                style="font-size:0.9rem;">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="bi bi-upc-scan me-1"></i> Scan
                            </button>
                        </form>
                    </div>

                    {{-- Tabel Barang --}}
                    <table class="table table-sm table-bordered table-striped text-center" id="tbbarang" style="width: 100%; font-size: small;">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    {{-- Script Custom --}}
    <x-slot name="jscustom">
        <script>
            $(document).ready(function () {
                $('#tbbarang').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('stockopname.barangajax') }}",
                        data: { bulan: "{{ $bulan }}" }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'kode_barang', name: 'kode_barang' },
                        { data: 'nama_barang', name: 'nama_barang' },
                        { data: 'stock_sistem', name: 'stock_sistem' },
                        { data: 'stock_fisik', name: 'stock_fisik' },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
                    ],
                    pageLength: 25,
                    responsive: true,
                    createdRow: function(row, data, dataIndex) {
                        if (data.status === 'sukses') {
                            $(row).addClass('table-warning'); // tambahin class kuning
                        }
                    }
                });

                // Verifikasi password sebelum mulai opname
                $('#formMulaiOpname').on('submit', function(e) {
                    e.preventDefault();
                    let form = this;

                    Swal.fire({
                        title: 'Mulai Stock Opname?',
                        text: "Jika bulan ini sudah ada data, data lama akan dihapus!",
                        icon: 'warning',
                        input: 'password',
                        inputLabel: 'Masukkan password Anda',
                        inputPlaceholder: 'Password',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal',
                        inputAttributes: {
                            autocapitalize: 'off',
                            autocorrect: 'off'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let password = result.value;
                            if (!password) {
                                Swal.fire('Error', 'Password wajib diisi', 'error');
                                return;
                            }

                            $.post("{{ route('stockopname.verifyPassword') }}", { _token: "{{ csrf_token() }}", password: password })
                            .done(function(res) {
                                if(res.valid){
                                    form.submit();
                                } else {
                                    Swal.fire('Error', 'Password salah', 'error');
                                }
                            })
                            .fail(function(){
                                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                            });
                        }
                    });
                });

                // Scan barang
                $('#formScan').on('submit', function(e) {
                    e.preventDefault();
                    let kode = $('#kodeScan').val();
                    if (!kode) return;

                    $.post("{{ route('stockopname.scan') }}", { kode: kode, _token: "{{ csrf_token() }}" })
                    .done(function(res) {
                        if (res.status === 'found') {
                            window.location.href = "{{ url('/stock/form') }}" + "?barang_id=" + res.data.id;
                        } else if (res.status === 'old') {
                            Swal.fire({
                                title: 'Barang tidak ada di master!',
                                text: "Barang ditemukan di master lama. Apakah mau ditambahkan ke master baru?",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Tambahkan',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.post("{{ route('stockopname.insertOld') }}", { kode: kode, _token: "{{ csrf_token() }}" })
                                    .done(function(res2) {
                                        window.location.href = "{{ url('/stock/form') }}" + "?barang_id=" + res2.data.id;
                                    });
                                }
                            });
                        }
                    })
                    .fail(function(err) {
                        Swal.fire('Error', err.responseJSON?.message ?? 'Barang tidak ditemukan', 'error');
                    });
                });

                // Fokus input scan
                document.getElementById("kodeScan")?.focus();
            });
        </script>
    </x-slot>
</x-app-layout>
