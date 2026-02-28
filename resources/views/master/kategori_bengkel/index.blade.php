<x-app-layout>
    <x-slot name="pagetitle">Kategori Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Kategori Produk Bengkel</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <div class="card card-info card-outline mb-4">
                        <div class="card-body">

                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Cicilan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            @if($item->cicilan == '1')
                                                <span class="badge bg-success">Ya</span>
                                            @else
                                                <span class="badge bg-danger">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('kategori.bengkel.edit', $item->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil-square"></i>
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
        </div>
    </div>

</x-app-layout>