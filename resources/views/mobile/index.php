@extends('layouts.mobile')

@section('content')

<link rel="stylesheet" href="assets/css/home.css">

<div id="user-section">
    <div id="user-detail">
        <div class="avatar">
            @if(!empty(Auth::guard('karyawan')->user()->foto))
                @php
                    $path = Storage::url('uploads/karyawan/' . Auth::guard('karyawan')->user()->foto);
                @endphp
                <img src="{{ url($path) }}" alt="avatar">
            @else
                <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}" alt="avatar" loading="lazy">
            @endif
        </div>
        
        <div id="user-info">
            <div id="user-role">Assalamu'alaikum..</div>
            <h3>{{ Auth::guard('karyawan')->user()->nama_lengkap }}</h3>
            <div id="user-role">Jabatan: <strong>{{ Auth::guard('karyawan')->user()->jabatan }}</strong></div>
        </div>
    </div>
</div>
<div class="performance-card">
    <div class="title">Bulan ini:</div>

    <div class="performance-grid">
        {{-- Kajian --}}
        <a href="/presensi/recordkajian" class="perf-item perf-kajian {{ request()->routeIs('presensi.recordkajian') ? 'active' : '' }}">
            <ion-icon name="book-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Kajian</div>
                <div class="value">{{ $totalKajian }}x Hadir</div>
            </div>
        </a>

        {{-- Ahad Pagi --}}
        <a href="/ahadpagi" class="perf-item perf-ahad {{ request()->is('ahadpagi') ? 'active' : '' }}">
            <ion-icon name="sunny-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Ahad Pagi</div>
                @if(is_numeric($ahadPagi))
                    <div class="value">{{ $ahadPagi }} dari {{ $totalMinggu }}</div>
                    <div class="progress mt-1" style="height: 6px;">
                        @php
                            $persen = $totalMinggu > 0 ? round(($ahadPagi / $totalMinggu) * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar"
                            style="width: {{ $persen }}%;"
                            aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                @else
                    <small style="font-size:11px;color:#999;">{{ $ahadPagi }}</small>
                @endif
            </div>
        </a>

        {{-- Cuti --}}
        <div class="perf-item perf-cuti">
            <ion-icon name="airplane-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Sisa Cuti</div>
                <div class="value">{{ $sisaCuti }} dari 12</div>
            </div>
        </div>

        {{-- Saldo Voucher --}}
        <a href="/sale" class="perf-item perf-saldo text-decoration-none">
            <ion-icon name="wallet-outline"></ion-icon>
            <div class="perf-text">
                <div class="label">Saldo Voucher</div>
                <div class="value">Rp {{ number_format(Auth::guard('karyawan')->user()->saldo, 0, ',', '.') }}</div>
            </div>
        </a>

    </div>

    <div class="text-center mt-2" style="font-size: 11px; color: #777;">
        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" width="16" class="me-1 align-middle">
        <strong>HRIS Mobile v3.0</strong> – RS PKU Muhammadiyah Boja
    </div>
</div>

<div class="scrollable-content">
    <div class="rekappresensi">
        <h4 class="text-center">Akses Cepat:</h4>

        <button onclick="location.href='{{ route('form.scan.camera') }}'" class="btn btn-success w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="qr-code-outline"></ion-icon>
            <span>| Scan QR Kehadiran Kajian</span>
        </button>

        <!-- Tombol Absen Ahad Pagi - buka di tab/browser baru -->
        <button onclick="window.open('https://kajian.pcmboja.com', '_blank')" class="btn btn-success w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="calendar-outline"></ion-icon>
            <span>| Absen Ahad Pagi</span>
        </button>

        <button onclick="location.href='{{ route('presensi.recordkajian') }}'" class="btn btn-primary w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="school-outline"></ion-icon>
            <span>| Record Kehadiran Kajian</span>
        </button>

        @php $idUser = Auth::guard('karyawan')->user()->jabatan ?? ''; @endphp

        @if(in_array($idUser, ['Security', 'Pemegang Saham', 'SDI','IT']))
        <button onclick="location.href='{{ route('inventaris.index') }}'" class="btn btn-warning w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="briefcase-outline"></ion-icon>
            <span>| Inventaris Security</span>
        </button>
        @endif

        @if(in_array($idUser, ['Security','Pemegang Saham', 'IT', 'SDI']))
        <button onclick="location.href='{{ route('kegiatan.index') }}'" class="btn btn-warning w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="clipboard-outline"></ion-icon>
            <span>| Kegiatan Harian Security</span>
        </button>
        @endif

        @if(in_array($idUser, ['SDI', 'Kabid Pelayanan', 'IT Network', 'IT','Direktur', 'Binroh']))
        <button onclick="location.href='{{ route('presensi.agenda') }}'" class="btn btn-info w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="document-text-outline"></ion-icon>
            <span>| Input Agenda</span>
        </button>
        @endif

        {{-- Website Rumah Sakit --}}
        <button onclick="openWebsite()" class="btn btn-info w-100 d-flex align-items-center justify-content-start gap-3 mb-2">
            <ion-icon name="medkit-outline"></ion-icon>
            <span>| Website Rumah Sakit</span>
            <span id="spinner-rs" class="spinner-border spinner-border-sm ms-auto d-none" role="status"></span>
        </button>

        <button onclick="loadPasien()" class="btn btn-info w-100 d-flex align-items-center justify-content-start gap-3">
            <ion-icon name="medkit-outline"></ion-icon>
            <span>| Update Pasien</span>
            <span id="spinner" class="spinner-border spinner-border-sm ms-auto d-none" role="status"></span>
        </button>

    </div>
</div>

<script>
    function openWebsite() {
        const spinner = document.getElementById('spinner-rs');
        spinner.classList.remove('d-none');

        setTimeout(() => {
            window.open("https://rspkuboja.com", "_blank");
            spinner.classList.add('d-none');
        }, 500); // waktu loading sebelum membuka tab baru
    }

    function loadPasien() {
        const spinner = document.getElementById('spinner');
        spinner.classList.remove('d-none');
        setTimeout(() => {
            window.location.href = "{{ route('presensi.pasien') }}";
        }, 300);
    }
</script>
@endsection
