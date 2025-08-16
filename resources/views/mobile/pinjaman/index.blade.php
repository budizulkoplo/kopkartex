@extends('layouts.mobile')

@section('header')
<div class="appHeader bg-warning text-light">
    <div class="left">
        <a href="{{ route('mobile.home') }}" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Daftar Pinjaman</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="p-3" style="margin-top: 40px">
    <a href="{{ route('mobile.pinjaman.create') }}" class="btn btn-primary w-100 mb-3">Ajukan Pinjaman Baru</a>

    @if($pinjaman->count() > 0)
        @foreach($pinjaman as $p)
            <div class="card mb-2">
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $p->id_pinjaman }}</p>
                    <p><strong>Tanggal:</strong> {{ $p->tgl_pengajuan }}</p>
                    <p><strong>Nominal:</strong> {{ number_format($p->nominal_pengajuan) }}</p>
                    <p><strong>Tenor:</strong> {{ $p->tenor }} bulan</p>
                    <p><strong>Status:</strong> {{ ucfirst($p->status) }}</p>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-center text-muted">Belum ada pengajuan pinjaman.</p>
    @endif
</div>
@endsection
