@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle text-uppercase">PILIH TOKO</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">
    @if($tokoList->isEmpty())
        <div class="text-center mt-4">
            <p class="text-muted">Tidak ada toko tersedia</p>
        </div>
    @else
        <div class="listview">
            @foreach($tokoList as $toko)
                <a href="{{ route('mobile.belanja.produk', $toko->id) }}" class="card mb-2 shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <!-- Icon toko -->
                        <div class="text-warning d-flex align-items-center justify-content-center" style="font-size: 1.8rem; width: 40px;">
                            <ion-icon name="storefront-outline"></ion-icon>
                        </div>

                        <!-- Nama toko -->
                        <div class="flex-grow-1 ms-2">
                            <h6 class="mb-0 fw-bold text-uppercase">{{ $toko->nama_unit }}</h6>
                            <small class="text-muted">Klik untuk lihat produk</small>
                        </div>

                        <!-- Panah kanan -->
                        <ion-icon name="chevron-forward-outline" class="text-muted ms-2"></ion-icon>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
