<x-app-layout>
    <x-slot name="pagetitle">PPOB</x-slot>
    <div class="app-content">
        <div class="container">

            {{-- Form Input Utama --}}
            <div class="row mb-4">
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" placeholder="ID Pelanggan">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm">
                        <option value="">Cek PLN/PASCA</option>
                        <option value="pln">PLN</option>
                        <option value="pasca">PASCA</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control form-control-sm" placeholder="Nominal">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm">
                        <option value="">Beli TOKEN</option>
                        <option value="20">20rb</option>
                        <option value="50">50rb</option>
                        <option value="100">100rb</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" placeholder="No HP Tujuan">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i> Pulsa</button>
                </div>
            </div>

            {{-- Grid Layanan PPOB --}}
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
                @php
                    $services = [
                        ['icon' => 'bi-currency-exchange', 'name' => 'Transfer Antar Bank'],
                        ['icon' => 'bi-lightning-charge', 'name' => 'Top Up Emoney'],
                        ['icon' => 'bi-train-front', 'name' => 'Reservasi Kereta Api'],
                        ['icon' => 'bi-phone', 'name' => 'Ponsel Pasca Bayar'],
                        ['icon' => 'bi-wifi', 'name' => 'IndiHome'],
                        ['icon' => 'bi-arrow-left-right', 'name' => 'Transfer Saldo'],
                        ['icon' => 'bi-battery-charging', 'name' => 'PLN'],
                        ['icon' => 'bi-droplet-half', 'name' => 'PDAM'],
                        ['icon' => 'bi-sim', 'name' => 'Pulsa & Data'],
                        ['icon' => 'bi-bus-front', 'name' => 'Bus AKAP & Travel'],
                        ['icon' => 'bi-wallet2', 'name' => 'Cicilan'],
                        ['icon' => 'bi-credit-card', 'name' => 'Kartu Kredit'],
                        ['icon' => 'bi-lightning-charge-fill', 'name' => 'Top Up Mitra'],
                        ['icon' => 'bi-bag-plus', 'name' => 'Ajak Bisnis'],
                        ['icon' => 'bi-truck', 'name' => 'Ekspedisi Lion Parcel'],
                        ['icon' => 'bi-box-seam', 'name' => 'Ekspedisi POS'],
                        ['icon' => 'bi-controller', 'name' => 'Game Online'],
                        ['icon' => 'bi-capslock', 'name' => 'TV & Internet'],
                        ['icon' => 'bi-cash', 'name' => 'Pajak'],
                        ['icon' => 'bi-cash-coin', 'name' => 'Bayar Pajak Kendaraan'],
                    ];
                @endphp

                @foreach ($services as $service)
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
