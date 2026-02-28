@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Kategori</h4>

    <form action="{{ route('kategori.bengkel.update', $kategori->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nama Kategori</label>
            <input type="text" class="form-control" value="{{ $kategori->name }}" disabled>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="cicilan"
                {{ $kategori->cicilan == '1' ? 'checked' : '' }}>
            <label class="form-check-label">
                Produk ini bisa cicilan
            </label>
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan</button>
    </form>
</div>
@endsection