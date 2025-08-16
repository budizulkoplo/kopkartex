@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="{{ route('mobile.home') }}" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">RIWAYAT BELANJA</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">
    @if($riwayat->isEmpty())
        <div class="text-center mt-4">
            <p class="text-muted">Belum ada riwayat belanja</p>
        </div>
    @else
        @foreach($riwayat as $item)
            <div class="card mb-2 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $item->nomor_invoice }}</strong>
                        <div class="text-muted">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</div>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold">Rp {{ number_format($item->grandtotal,0,',','.') }}</span>
                        <div class="text-muted">{{ ucfirst($item->status) }}</div>
                        <a href="{{ route('mobile.belanja.history.detail', $item->id) }}" class="btn btn-sm btn-primary mt-1">Detail</a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
