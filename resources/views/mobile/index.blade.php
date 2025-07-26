@extends('layouts.mobile')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">

<div id="user-section">
    <div id="user-detail">
        <div class="avatar">
            @if($user->foto)
                <img src="{{ asset('storage/foto/' . $user->foto) }}" alt="avatar" loading="lazy">
            @else
                <img src="{{ asset('assets/img/avatar1.jpg') }}" alt="avatar" loading="lazy">
            @endif
        </div>
        
        <div id="user-info">
            <div id="user-role">KOPKARTEX MOBILE</div>
            <h3>{{ $user->name }}</h3>
            <div id="user-role">Jabatan: <strong>{{ $user->jabatan ?? '-' }}</strong></div>
            <div id="user-role">Unit Kerja: {{ $user->unit_kerja ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="performance-card">
    <div class="title">Data Keanggotaan Koperasi</div>

    <div class="performance-grid">

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="card-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">NIK</div>
                <div class="value">{{ $user->nik }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="id-card-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Nomor Anggota</div>
                <div class="value">{{ $user->nomor_anggota }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="person-circle-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Username</div>
                <div class="value">{{ $user->username }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="calendar-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Tanggal Masuk</div>
                <div class="value">{{ \Carbon\Carbon::parse($user->tanggal_masuk)->format('d-m-Y') }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="cash-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Gaji</div>
                <div class="value">Rp {{ number_format((float) str_replace(',', '.', $user->gaji), 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="wallet-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Limit Hutang</div>
                <div class="value">Rp {{ number_format((float) str_replace(',', '.', $user->limit_hutang), 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="cellular-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Limit PPOB</div>
                <div class="value">Rp {{ number_format((float) str_replace(',', '.', $user->limit_ppob), 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="checkmark-done-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($user->status) }}</div>
            </div>
        </div>

    </div>
</div>

<div class="scrollable-content">
    <div class="rekappresensi">
        <h4 class="text-center">Kontak & Informasi Lain</h4>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Email:</strong> {{ $user->email }}</li>
            <li class="list-group-item"><strong>No HP:</strong> {{ $user->nohp }}</li>
            <li class="list-group-item"><strong>Alamat:</strong> {{ $user->alamat }}</li>
        </ul>
    </div>
</div>

@endsection
