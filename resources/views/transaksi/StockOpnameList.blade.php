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
                        <tbody>
                            @foreach ($barang as $index => $item)
                                <tr class="{{ $item->status === 'sukses' ? 'table-warning' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->kode_barang }}</td>
                                    <td class="text-start">{{ $item->nama_barang }}</td>
                                    <td>{{ $item->stock_sistem ?? $item->stok_unit ?? '-' }}</td>
                                    <td>{{ $item->stock_fisik ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('stockopname.form', ['barang_id' => $item->id]) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> Input Opname
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
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
                    pageLength: 50,
                    responsive: true
                });

                // SweetAlert konfirmasi mulai opname
                $('#formMulaiOpname').on('submit', function(e) {
                    e.preventDefault();
                    let form = this;

                    Swal.fire({
                        title: 'Mulai Stock Opname?',
                        text: "Jika bulan ini sudah ada data, data lama akan dihapus!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

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
                    Swal.fire('Error', err.responseJSON.message ?? 'Barang tidak ditemukan', 'error');
                });
            });

        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("kodeScan")?.focus();
            });
        </script>
    </x-slot>
</x-app-layout>
