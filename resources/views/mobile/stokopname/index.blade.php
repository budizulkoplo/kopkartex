@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-primary text-light">
    <div class="pageTitle">Scan Produk</div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">

    {{-- Tampilkan pesan error jika ada --}}
    @if(session('error'))
        <div class="alert alert-danger text-center" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div id="reader" style="width: 100%; max-width: 400px; margin:auto; border:1px solid #ccc;"></div>

    {{-- Tombol kembali ke halaman stok opname --}}
    <div class="text-center mt-3">
        <a href="{{ route('mobile.stokopname.index') }}" class="btn btn-secondary btn-sm">
            ⬅️ Kembali ke Stok Opname
        </a>
    </div>

    <form id="scanForm" action="{{ route('mobile.stokopname.scan') }}" method="POST">
        @csrf
        <input type="hidden" name="barcode" id="barcode">
    </form>

    <p class="text-center mt-3">Arahkan kamera ke barcode produk.</p>
</div>

{{-- Script langsung di sini, jangan di section --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");
    let scanning = false;

    function onScanSuccess(decodedText, decodedResult) {
        if (scanning) return;
        scanning = true;

        document.getElementById("barcode").value = decodedText;
        document.getElementById("scanForm").submit();
    }

    Html5Qrcode.getCameras().then(devices => {
        if (devices.length > 0) {
            let backCam = devices.find(d =>
                d.label.toLowerCase().includes("back") ||
                d.label.toLowerCase().includes("environment")
            ) || devices[0];

            html5QrCode.start(
                backCam.id,
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess
            );
        } else {
            alert("❌ Tidak ada kamera tersedia.");
        }
    }).catch(err => {
        console.error("getCameras error:", err);
        alert("❌ Gagal akses kamera. Pastikan izin sudah diberikan & akses via HTTPS.");
    });
</script>
@endsection
