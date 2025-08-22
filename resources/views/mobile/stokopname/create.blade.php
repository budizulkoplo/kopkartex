@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-success text-light">
    <div class="pageTitle">Input Opname</div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">
    <form id="frmOpname">
        @csrf
        <input type="hidden" name="tgl_opname" id="tgl_opname">

        <div class="alert alert-info mb-3">
            <strong>Kode:</strong> {{ $barang->kode_barang }} <br>
            <strong>Nama:</strong> {{ $barang->nama_barang }}
        </div>

        <!-- container row -->
        <div id="rows">
            <div class="row g-2 mb-2 align-items-center row-item">
                <!-- simpan id_barang dan kode di setiap row -->
                <input type="hidden" name="id[]" value="{{ $barang->id }}">
                <input type="hidden" name="code[]" value="{{ $barang->kode_barang }}">

                <div class="col-6">
                    <input type="number" class="form-control form-control-sm" name="qty[]" placeholder="Qty" min="1" required>
                </div>
                <div class="col-5">
                    <input type="date" class="form-control form-control-sm" name="exp[]" required>
                </div>
                <div class="col-1 text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button>
                </div>
            </div>
        </div>

        <button type="button" id="btnTambah" class="btn btn-outline-primary btn-sm w-100 mb-3">+ Tambah Baris</button>

        <button type="submit" class="btn btn-success w-100">Simpan Opname</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // set tgl opname otomatis
    let today = new Date().toISOString().split('T')[0];
    document.getElementById('tgl_opname').value = today;

    // Tambah baris
    document.getElementById('btnTambah').addEventListener('click', function () {
        let row = `
        <div class="row g-2 mb-2 align-items-center row-item">
            <input type="hidden" name="id[]" value="{{ $barang->id }}">
            <input type="hidden" name="code[]" value="{{ $barang->kode_barang }}">
            <div class="col-6">
                <input type="number" class="form-control form-control-sm" name="qty[]" placeholder="Qty" min="1" required>
            </div>
            <div class="col-5">
                <input type="date" class="form-control form-control-sm" name="exp[]" required>
            </div>
            <div class="col-1 text-center">
                <button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button>
            </div>
        </div>`;
        document.getElementById('rows').insertAdjacentHTML('beforeend', row);
    });

    // Hapus baris
    document.getElementById('rows').addEventListener('click', function(e){
        if (e.target.classList.contains('btn-remove')) {
            e.target.closest('.row-item').remove();
        }
    });

    // Submit form pakai fetch
    document.getElementById('frmOpname').addEventListener('submit', function(e) {
        e.preventDefault();

        fetch("{{ route('mobile.stokopname.store') }}", {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                alert('Berhasil disimpan');
                window.location.href = resp.redirect;
            } else {
                alert(resp.error || 'Gagal simpan');
            }
        })
        .catch(err => {
            console.error("Error fetch:", err);
            alert("Terjadi kesalahan!");
        });
    });
});
</script>
@endsection
