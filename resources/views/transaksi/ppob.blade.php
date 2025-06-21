<x-app-layout>
    <x-slot name="pagetitle">PPOB</x-slot>
    <div class="app-content">
        <div class="container">

            {{-- Kategori: Top Up --}}
            <h6 class="mb-3">Top Up</h6>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 mb-4">
                @php
                    $topups = [
                        ['icon' => 'bi-phone', 'name' => 'Pulsa'],
                        ['icon' => 'bi-sim', 'name' => 'Paket Data'],
                        ['icon' => 'bi-wallet2', 'name' => 'Uang Elektronik'],
                        ['icon' => 'bi-lightning-charge-fill', 'name' => 'Top Up Mitra'],
                        ['icon' => 'bi-arrow-left-right', 'name' => 'Transfer Saldo'],
                    ];
                @endphp

                @foreach ($topups as $service)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 text-center service-box">
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <i class="bi {{ $service['icon'] }} fs-2 text-primary"></i>
                                </div>
                                <div class="small">{{ $service['name'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Kategori: Tagihan --}}
            <h6 class="mb-3">Tagihan</h6>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 mb-4">
                @php
                    $bills = [
                        ['icon' => 'bi-phone', 'name' => 'Pasca Bayar'],
                        ['icon' => 'bi-battery-charging', 'name' => 'Listrik PLN'],
                        ['icon' => 'bi-droplet-half', 'name' => 'PDAM'],
                        ['icon' => 'bi-capslock', 'name' => 'TV & Internet'],
                        ['icon' => 'bi-wifi', 'name' => 'Telkom'],
                        ['icon' => 'bi-heart-pulse', 'name' => 'BPJS Kesehatan'],
                        ['icon' => 'bi-briefcase-fill', 'name' => 'BPJS Ketenagakerjaan'],
                    ];
                @endphp

                @foreach ($bills as $service)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 text-center service-box">
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <i class="bi {{ $service['icon'] }} fs-2 text-primary"></i>
                                </div>
                                <div class="small">{{ $service['name'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Kategori: Pembayaran --}}
            <h6 class="mb-3">Pembayaran</h6>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
                @php
                    $payments = [
                        ['icon' => 'bi-bag-check', 'name' => 'E-Commerce'],
                        ['icon' => 'bi-credit-card', 'name' => 'Kartu Kredit'],
                        ['icon' => 'bi-wallet2', 'name' => 'Cicilan'],
                    ];
                @endphp

                @foreach ($payments as $service)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 text-center service-box">
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <i class="bi {{ $service['icon'] }} fs-2 text-primary"></i>
                                </div>
                                <div class="small">{{ $service['name'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <x-slot name="csscustom">
        <style>
            .service-box {
                transition: transform 0.2s ease;
                cursor: pointer;
                border-radius: 10px;
            }
            .service-box:hover {
                transform: translateY(-3px);
                background-color: #f8f9fa;
            }
        </style>
    </x-slot>
</x-app-layout>
