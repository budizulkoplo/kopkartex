@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle text-uppercase fw-bold flex-grow-1 text-center">
        DAFTAR PRODUK {{ $toko->nama_unit }}
    </div>

    <div class="right d-flex justify-content-end align-items-center">
        <a href="{{ route('mobile.belanja.cart') }}" 
           class="headerButton"
           style="width:42px; height:42px; background:#07b8b2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:6px;">
            <ion-icon name="cart-outline" style="font-size:1.5rem; color:#fff;"></ion-icon>
            @if($cartCount > 0)
                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle"
                      style="font-size:0.7rem; min-width:18px;">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">

    @if($produkList->isEmpty())
        <div class="alert alert-warning text-center mt-4">
            <ion-icon name="alert-circle-outline" class="mb-1" style="font-size:1.3rem;"></ion-icon><br>
            Belum ada produk di toko ini
        </div>
    @else
        <div class="row">
            @foreach($produkList as $p)
                <div class="col-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-between p-2">

                            <!-- Nama produk center -->
                            <h6 class="fw-bold text-dark mb-2 text-center text-truncate">
                                {{ $p->nama_barang }}
                            </h6>

                            <!-- Foto produk lebih besar -->
                            <div class="text-center mb-2">
                                @if(isset($p->img) && $p->img)
                                    <img src="{{ asset('storage/produk/' . $p->img) }}" 
                                         class="img-fluid" 
                                         style="max-height:150px; object-fit:contain;" 
                                         alt="{{ $p->nama_barang }}">
                                @else
                                    <img src="{{ asset('images/no-image.png') }}" 
                                         class="img-fluid" 
                                         style="max-height:150px; object-fit:contain;" 
                                         alt="No Image">
                                @endif
                            </div>

                            <!-- Stock kiri, harga kanan dalam 1 row -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="badge bg-secondary px-2 py-1">
                                    Stok: {{ $p->stok }}
                                </small>
                                <span class="fw-bold text-success">
                                    Rp {{ number_format($p->harga_jual,0,',','.') }}
                                </span>
                            </div>

                            <!-- Tombol tambah -->
                            <form action="{{ route('mobile.belanja.cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $p->id }}">
                                <input type="hidden" name="nama" value="{{ $p->nama_barang }}">
                                <input type="hidden" name="harga" value="{{ $p->harga_jual }}">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <ion-icon name="cart-outline"></ion-icon> Tambah
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
