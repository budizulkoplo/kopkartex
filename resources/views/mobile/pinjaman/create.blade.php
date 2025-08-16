@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="{{ route('mobile.home') }}" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Pengajuan Pinjaman</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('mobile.pinjaman.store') }}" method="POST">
        @csrf

        <div class="mb-2">
            <label>Nomor Anggota</label>
            <input type="text" class="form-control" value="{{ $user->nomor_anggota }}" readonly>
        </div>

        <div class="mb-2">
            <label>Gaji</label>
            <input type="number" class="form-control" value="{{ $user->gaji }}" readonly>
        </div>

        <div class="mb-2">
            <label>Nominal Pengajuan</label>
            <input type="number" name="nominal_pengajuan" class="form-control" value="{{ old('nominal_pengajuan') }}">
        </div>

        <div class="mb-2">
            <label>Tenor (bulan)</label>
            <input type="number" name="tenor" class="form-control" value="{{ old('tenor') }}">
        </div>

        <div class="mb-2">
            <label>Jaminan</label>
            <input type="text" name="jaminan" class="form-control" value="{{ old('jaminan') }}">
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Ajukan Pinjaman</button>
    </form>
</div>
@endsection
