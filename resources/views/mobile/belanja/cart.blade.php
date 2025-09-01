@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="{{ route('mobile.belanja.toko') }}" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">KERANJANG BELANJA</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px; margin-bottom: 70px">
    @if(empty($cart))
        <div class="text-center mt-4">
            <p class="text-muted">Keranjang masih kosong</p>
        </div>
    @else
        @php $total = 0; @endphp
        @foreach($cart as $item)
            @php 
                $subtotal = $item['harga'] * $item['qty']; 
                $total += $subtotal; 
            @endphp

            <div class="card mb-2 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    {{-- Kiri: Nama & harga --}}
                    <div>
                        <h6 class="mb-0">{{ $item['nama'] }}</h6>
                        <small class="text-muted">Rp {{ number_format($item['harga'],0,',','.') }}</small>
                    </div>

                    {{-- Tengah: qty control --}}
                    <form action="{{ route('mobile.belanja.cart.update') }}" method="POST" class="d-flex align-items-center qty-form">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-minus">−</button>
                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" class="form-control form-control-sm text-center mx-1" style="width: 50px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-plus">+</button>
                    </form>

                    {{-- Kanan: subtotal & hapus --}}
                    <div class="d-flex flex-column align-items-end">
                        <span class="fw-bold">Rp {{ number_format($subtotal,0,',','.') }}</span>
                        <form action="{{ route('mobile.belanja.cart.remove') }}" method="POST" class="mt-1">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}">
                            <button class="btn btn-sm btn-danger">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Total --}}
        <div class="card mt-3">
            <div class="card-body d-flex justify-content-between">
                <strong>Total</strong>
                <strong>Rp {{ number_format($total,0,',','.') }}</strong>
            </div>
        </div>

        {{-- Checkout Form --}}
        <form action="{{ route('mobile.belanja.checkout.process') }}" method="POST" class="mt-4">
            @csrf
            <div class="mb-3">
                <label>Nama Customer</label>
                <input type="hidden" name="anggota_id" value="{{ $user->id }}">
                <input type="text" name="customer" class="form-control" value="{{ $user->name }}" readonly>
            </div>

            {{-- metode bayar fix --}}
            <!-- <input type="hidden" name="metode_bayar" value="tunai">
            <div class="mb-3">
                <label>Metode Bayar</label>
                <input type="text" class="form-control" value="Bayar di Toko" readonly>
            </div> -->

            <button type="submit" class="btn btn-success w-100">Simpan Pesanan</button>
        </form>
    @endif
</div>

{{-- JS qty --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.qty-form').forEach(function(form) {
        const input = form.querySelector('input[name="qty"]');

        form.querySelector('.btn-minus').addEventListener('click', function() {
            let val = parseInt(input.value) || 1;
            input.value = Math.max(1, val - 1);
            form.submit();
        });

        form.querySelector('.btn-plus').addEventListener('click', function() {
            let val = parseInt(input.value) || 1;
            input.value = val + 1;
            form.submit();
        });

        input.addEventListener('change', function() {
            if(input.value < 1) input.value = 1;
            form.submit();
        });
    });
});
</script>
@endsection
