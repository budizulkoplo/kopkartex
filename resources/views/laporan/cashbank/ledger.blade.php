<x-app-layout>
    <x-slot name="pagetitle">Laporan Ledger Cashbank</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Laporan Ledger Cashbank</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.cashbank.ledger') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Unit Usaha</label>
                            <select name="unit_usaha" class="form-control form-control-sm" required>
                                <option value="">Pilih Unit Usaha</option>
                                @foreach($unitUsahaOptions as $unit)
                                    <option value="{{ $unit->unit_usaha }}" @selected((string) $filters['unit_usaha'] === (string) $unit->unit_usaha)>
                                        {{ $unit->nama_unit_usaha }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Akun Kas/Bank</label>
                            <select name="bank_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($bankOptions as $bank)
                                    <option value="{{ $bank->id }}" @selected((string) $filters['bank_id'] === (string) $bank->id)>
                                        {{ $bank->kode_akun }} - {{ $bank->nama_akun ?: $bank->nama_bank }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">COA</label>
                            <select name="coa_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($coaOptions as $coa)
                                    <option value="{{ $coa->id }}" @selected((string) $filters['coa_id'] === (string) $coa->id)>
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($supplierOptions as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((string) $filters['supplier_id'] === (string) $supplier->id)>
                                        {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Anggota</label>
                            <select name="anggota_id" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($memberOptions as $member)
                                    <option value="{{ $member->id }}" @selected((string) $filters['anggota_id'] === (string) $member->id)>
                                        {{ $member->nomor_anggota }} - {{ $member->name }}
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
                                    <th>No Transaksi</th>
                                    <th>Unit Usaha</th>
                                    <th>Dokumen</th>
                                    <th>Kas/Bank</th>
                                    <th>COA</th>
                                    <th>Supplier/Anggota</th>
                                    <th>Ref</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-secondary">
                                    <td colspan="11" class="text-end fw-semibold">Saldo Awal</td>
                                    <td class="text-end fw-semibold">{{ number_format($openingBalance, 0, ',', '.') }}</td>
                                </tr>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->tgl_transaksi)->format('d-m-Y') }}</td>
                                        <td>{{ $row->nomor_transaksi }}</td>
                                        <td>{{ $row->nama_unit_usaha }}</td>
                                        <td>{{ $row->kode_dokumen }} - {{ $row->nama_dokumen }}</td>
                                        <td>{{ $row->bank_kode }} - {{ $row->bank_nama ?: $row->nama_bank }}</td>
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
                                        <td colspan="12" class="text-center text-muted">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="9" class="text-end">Total</th>
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
</x-app-layout>
