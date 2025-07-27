@extends('layouts.mobile')
@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">PPOB MENU</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">

<div class="container mt-3">

    <h4 class="mb-3">Top Up</h4>
    <div class="grid">
        @foreach(['Pulsa', 'Paket Data', 'Uang Elektronik', 'Top Up Mitra', 'Transfer Saldo'] as $item)
        <div class="menu-box">
            <div class="icon"><ion-icon name="phone-portrait-outline"></ion-icon></div>
            <div class="label">{{ $item }}</div>
        </div>
        @endforeach
    </div>

    <h4 class="mt-4 mb-3">Tagihan</h4>
    <div class="grid">
        @foreach(['Pasca Bayar', 'Listrik PLN', 'PDAM', 'TV & Internet', 'Telkom', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan'] as $item)
        <div class="menu-box">
            <div class="icon"><ion-icon name="document-text-outline"></ion-icon></div>
            <div class="label">{{ $item }}</div>
        </div>
        @endforeach
    </div>

    <h4 class="mt-4 mb-3">Pembayaran</h4>
    <div class="grid">
        @foreach(['E-Commerce', 'Kartu Kredit', 'Cicilan'] as $item)
        <div class="menu-box">
            <div class="icon"><ion-icon name="card-outline"></ion-icon></div>
            <div class="label">{{ $item }}</div>
        </div>
        @endforeach
    </div>
</div>

<style>
    .grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .menu-box {
        flex: 0 0 30%;
        background: #fff;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        transition: 0.3s;
    }

    .menu-box:hover {
        background-color: #f0f8ff;
    }

    .menu-box .icon {
        font-size: 24px;
        color: #007bff;
    }

    .menu-box .label {
        margin-top: 0.5rem;
        font-size: 14px;
    }
</style>

@endsection
