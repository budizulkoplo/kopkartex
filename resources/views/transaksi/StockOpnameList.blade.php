<x-app-layout>
    <x-slot name="pagetitle">Stock Opname</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Daftar Barang - Stock Opname</h3>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <div class="row">
                <div class="col-12">
                    <div class="card card-info card-outline">
                        <div class="card-header pt-2 pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Unit:</strong> {{ auth()->user()->unit->nama_unit ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
    <form action="{{ route('stockopname.mulai') }}" method="POST" onsubmit="return confirm('Mulai stock opname baru?')">
        @csrf
        <button type="submit" class="btn btn-success">
            <i class="bi bi-play-circle"></i> Mulai Stock Opname
        </button>
    </form>
</div>


                        <div class="card-body">
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
                                        <tr class="{{ $item->opname_id ? 'table-warning' : '' }}">
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
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(document).ready(function () {
                $('#tbbarang').DataTable({
                    pageLength: 50,
                    responsive: true
                });
            });
        </script>
    </x-slot>
</x-app-layout>
