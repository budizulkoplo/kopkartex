@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="{{ route('mobile.belanja.history') }}" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">DETAIL INVOICE</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">

    {{-- Info Invoice --}}
    <div class="card mb-3">
        <div class="card-body">
            <div>
                <div><strong>Invoice:</strong> {{ $penjualan->nomor_invoice }}</div>
                <div><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d-m-Y H:i') }}</div>
                <div><strong>Customer:</strong> {{ $penjualan->customer }}</div>
                <div><strong>Status Bayar:</strong> {{ ucfirst($penjualan->status) }}</div>
                <div><strong>Status Pesanan:</strong> 
                <span id="status-ambil">{{ ucfirst($penjualan->status_ambil == 'finish' ? 'Sukses' : $penjualan->status_ambil) }}</span>
                </div>
                
                {{-- Barcode di bawah Status Ambil, responsif --}}
                <div class="mt-2 text-center">
                    <svg id="barcode" style="max-width: 100%; height: 50px;"></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- List barang digabung --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body p-2">
            @foreach($detail as $item)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                    <div>
                        <h6 class="mb-0">{{ $item->barang->nama_barang ?? 'Produk' }}</h6>
                        <small class="text-muted">Rp {{ number_format($item->harga,0,',','.') }} x {{ $item->qty }}</small>
                    </div>
                    <div class="text-end fw-bold">
                        Rp {{ number_format($item->harga * $item->qty,0,',','.') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Total --}}
    <div class="card mt-3">
        <div class="card-body d-flex justify-content-between">
            <strong>Total</strong>
            <strong>Rp {{ number_format($penjualan->grandtotal,0,',','.') }}</strong>
        </div>
    </div>
    @if(strtolower($penjualan->status_ambil) === 'pesan')
        <form action="{{ route('mobile.belanja.cancel', $penjualan->id) }}" method="POST" class="mt-3">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger w-100">Batalkan Pesanan</button>
        </form>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode("#barcode", "{{ $penjualan->nomor_invoice }}", {
        format: "CODE128",
        width: 1,      // lebih tipis
        height: 50,
        displayValue: true,
        margin: 0
    });

    // Ganti finish menjadi Sukses
    let statusElem = document.getElementById('status-ambil');
    if(statusElem.innerText.toLowerCase() === 'finish') {
        statusElem.innerText = 'Sukses';
    }
</script>
@endsection
