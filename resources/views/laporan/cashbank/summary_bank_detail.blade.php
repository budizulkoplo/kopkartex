<x-app-layout>
    <x-slot name="pagetitle">Laporan Summary Bank Detail</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Laporan Summary Bank Detail</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.cashbank.summary-bank-detail') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Akun Kas/Bank</label>
                            <select name="bank_id" class="form-control form-control-sm cashbank-filter-select" data-placeholder="Pilih Akun Kas/Bank" required>
                                <option value="">Pilih Akun Kas/Bank</option>
                                @foreach($bankOptions as $bank)
                                    <option value="{{ $bank->id }}" @selected((string) $filters['bank_id'] === (string) $bank->id)>
                                        {{ $bank->kode_akun }} - {{ $bank->nama_akun ?: $bank->nama_bank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $filters['tanggal_awal'] }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $filters['tanggal_akhir'] }}" required>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i> Tampilkan</button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped" style="font-size: small;">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Akun Kas/Bank</th>
                                    <th>No Transaksi</th>
                                    <th>Dokumen</th>
                                    <th>COA Detail</th>
                                    <th>Penerima</th>
                                    <th>Ref</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-secondary">
                                    <td colspan="10" class="text-end fw-semibold">Saldo Awal</td>
                                    <td class="text-end fw-semibold">{{ number_format($openingBalance, 0, ',', '.') }}</td>
                                </tr>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->tgl_transaksi)->format('d-m-Y') }}</td>
                                        <td>{{ $row->bank_kode }} - {{ $row->bank_nama ?: $row->nama_bank }}</td>
                                        <td>{{ $row->nomor_transaksi }}</td>
                                        <td>{{ $row->kode_dokumen }} - {{ $row->nama_dokumen }}</td>
                                        <td>{{ $row->coa_kode }} - {{ $row->coa_nama }}</td>
                                        <td>{{ $row->nama_supplier ?: $row->dibayar_kepada }}</td>
                                        <td>{{ $row->nomor_ref ?: '-' }}</td>
                                        <td>{{ $row->keterangan_detail ?: '-' }}</td>
                                        <td class="text-end">{{ number_format($row->debit, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row->kredit, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row->saldo, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="8" class="text-end">Total</th>
                                    <th class="text-end">{{ number_format($totals['debit'], 0, ',', '.') }}</th>
                                    <th class="text-end">{{ number_format($totals['kredit'], 0, ',', '.') }}</th>
                                    <th class="text-end">{{ number_format($totals['saldo'], 0, ',', '.') }}</th>
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
