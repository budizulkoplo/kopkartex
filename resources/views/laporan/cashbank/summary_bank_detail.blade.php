<x-app-layout>
    <x-slot name="pagetitle">Laporan Summary Bank Detail</x-slot>

    @php
        $selectedUnit = $unitUsahaOptions->firstWhere('unit_usaha', $filters['unit_usaha']);
        $bankTitle = $selectedBank
            ? ($selectedBank->nama_akun ?: $selectedBank->nama_bank)
            : 'Pilih Akun Kas/Bank';
        $bankCode = $selectedBank->kode_akun ?? '';
    @endphp

    <style>
        .summary-bank-page .report-filter {
            border-top: 3px solid #0d6efd;
        }

        .summary-bank-page .form-label {
            font-weight: 700;
            font-size: .82rem;
            color: #14213d;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .summary-bank-page .filter-row {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr) 150px;
            gap: 6px;
            align-items: center;
            margin-bottom: 6px;
        }

        .summary-bank-page .period-row {
            display: grid;
            grid-template-columns: 150px 160px 28px 160px auto;
            gap: 6px;
            align-items: center;
        }

        .summary-bank-page .report-sheet {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
        }

        .summary-bank-page .report-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .summary-bank-page .report-title h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-bank-page .report-title p {
            margin: 1px 0 0;
            font-size: .82rem;
        }

        .summary-bank-page .summary-table {
            font-size: .84rem;
        }

        .summary-bank-page .summary-table th,
        .summary-bank-page .summary-table td {
            padding: .25rem .35rem;
            vertical-align: top;
        }

        .summary-bank-page .summary-total th {
            font-size: 1rem;
            padding-top: .45rem;
            padding-bottom: .45rem;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                background: #fff !important;
            }

            .app-header,
            .app-sidebar,
            .app-content-header,
            .report-filter,
            .btn,
            .select2-container,
            .main-footer {
                display: none !important;
            }

            .app-wrapper,
            .app-main,
            .app-content,
            .container-fluid,
            .card,
            .card-body {
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
            }

            .summary-bank-page .report-sheet {
                max-width: none;
            }

            .summary-bank-page .summary-table {
                font-size: 9px;
            }

            .summary-bank-page .summary-table th,
            .summary-bank-page .summary-table td {
                border: 1px solid #000 !important;
                padding: 2px 4px;
            }

            .summary-bank-page .summary-total th {
                font-size: 11px;
            }
        }
    </style>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Laporan Summary Bank Detail</h3>
        </div>
    </div>

    <div class="app-content summary-bank-page">
        <div class="container-fluid">
            <div class="card card-info card-outline mb-3 report-filter">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.cashbank.summary-bank-detail') }}">
                        <div class="filter-row">
                            <label class="form-label">UNIT</label>
                            <select name="unit_usaha" class="form-control form-control-sm cashbank-filter-select" data-placeholder="Semua Unit">
                                <option value="">Semua Unit</option>
                                @foreach($unitUsahaOptions as $unit)
                                    <option value="{{ $unit->unit_usaha }}" @selected((string) $filters['unit_usaha'] === (string) $unit->unit_usaha)>
                                        {{ $unit->unit_usaha }} - {{ $unit->nama_unit_usaha }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control form-control-sm" value="{{ $selectedUnit->nama_unit_usaha ?? 'Semua Unit' }}" readonly>
                        </div>

                        <div class="filter-row">
                            <label class="form-label">BUKU CASH / BANK</label>
                            <select name="bank_id" class="form-control form-control-sm cashbank-filter-select" data-placeholder="Pilih Akun Kas/Bank" required>
                                <option value="">Pilih Akun Kas/Bank</option>
                                @foreach($bankOptions as $bank)
                                    <option value="{{ $bank->id }}" @selected((string) $filters['bank_id'] === (string) $bank->id)>
                                        {{ $bank->kode_akun }} - {{ $bank->nama_akun ?: $bank->nama_bank }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control form-control-sm" value="{{ $bankCode }}" readonly>
                        </div>

                        <div class="period-row">
                            <label class="form-label">Transaksi Tanggal</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $filters['tanggal_awal'] }}" required>
                            <span class="text-center">s/d</span>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $filters['tanggal_akhir'] }}" required>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> View Print</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="report-sheet">
                        <div class="report-title">
                            <h4>{{ $bankTitle }}</h4>
                            <p>{{ \Carbon\Carbon::parse($filters['tanggal_awal'])->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($filters['tanggal_akhir'])->format('d-m-Y') }}</p>
                        </div>

                        <table class="table table-sm table-bordered summary-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 6%;" class="text-center">No.</th>
                                    <th style="width: 16%;">Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $row->coa_kode }}</td>
                                        <td>{{ $row->coa_nama }}</td>
                                        <td class="text-end">{{ number_format($row->debit, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row->kredit, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="summary-total">
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end">{{ number_format($totals['debit'], 0, ',', '.') }}</th>
                                    <th class="text-end">{{ number_format($totals['kredit'], 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script>
            $(function () {
                $('.cashbank-filter-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: function () {
                        return $(this).data('placeholder') || 'Pilih';
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
