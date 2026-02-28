<x-app-layout>
    <x-slot name="pagetitle">Edit Kategori Bengkel</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Edit Kategori Produk Bengkel</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="{{ route('kategori.bengkel.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <div class="card card-info card-outline">
                        <div class="card-body">

                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <form action="{{ route('kategori.bengkel.update', $kategori->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">Nama Kategori</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $kategori->name }}" 
                                           disabled>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch"
                                           id="cicilanSwitch"
                                           name="cicilan"
                                           {{ $kategori->cicilan == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cicilanSwitch">
                                        Aktifkan cicilan untuk kategori ini
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>

                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>