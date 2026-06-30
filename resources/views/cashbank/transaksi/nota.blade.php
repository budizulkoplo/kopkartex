<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $transaction->nomor_transaksi }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body { font-size: 12px; color: #111; }
        .nota { max-width: 820px; margin: 24px auto; }
        @media print {
            .no-print { display: none; }
            .nota { margin: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="nota">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3">
            <div>
                <h4 class="mb-0">NOTA CASH BANK</h4>
                <div>{{ $transaction->jenis === 'pembayaran_hutang' ? 'Pembayaran Supplier' : 'Pembayaran Umum' }}</div>
            </div>
            <div class="text-end">
                <strong>{{ $transaction->nomor_transaksi }}</strong><br>
                Periode {{ $transaction->periode ?? optional($transaction->tgl_transaksi)->format('Ym') }}<br>
                {{ optional($transaction->tgl_transaksi)->format('d-m-Y') }}
            </div>
        </div>

        <table class="table table-sm table-borderless mb-3">
            <tr>
                <td style="width: 150px">Unit Usaha</td>
                <td>: {{ $unitUsahaName ?? '-' }}</td>
                <td style="width: 130px">Kode Dokumen</td>
                <td>: {{ $transaction->documentCode->kode ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibayar Kepada</td>
                <td>: {{ $transaction->dibayar_kepada }}</td>
                <td>Kode Akun</td>
                <td>: {{ $transaction->coa->kode_akun ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dibayar Dengan</td>
                <td>: {{ ucfirst($transaction->dibayar_dengan) }}</td>
                <td>Bank</td>
                <td>: {{ $transaction->bank->nama_bank ?? '-' }}</td>
            </tr>
            <tr>
                <td>No Ref / No Nota</td>
                <td>: {{ $transaction->no_ref_nota ?? '-' }}</td>
                <td>Jenis Dokumen</td>
                <td>: {{ ($transaction->documentCode->transaction_type ?? 'payment') === 'receipt' ? 'Receipt - Debet' : 'Payment - Kredit' }}</td>
            </tr>
            <tr>
                <td>Guna Membayar</td>
                <td colspan="3">: {{ $transaction->guna_membayar ?? '-' }}</td>
            </tr>
        </table>

        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Kode Akun</th>
                    <th>Invoice</th>
                    <th class="text-end">Nilai Invoice</th>
                    <th class="text-end">Sudah Dibayar</th>
                    <th class="text-end">Jumlah Bayar</th>
                    <th class="text-end">Sisa</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaction->details as $detail)
                    <tr>
                        <td>{{ $detail->coa->kode_akun ?? '-' }} {{ $detail->coa ? '- '.$detail->coa->nama_akun : '' }}</td>
                        <td>{{ $detail->nomor_invoice ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $detail->nilai_invoice, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $detail->sudah_dibayar, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $detail->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $detail->sisa, 0, ',', '.') }}</td>
                        <td>{{ $detail->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>{{ $transaction->coa->kode_akun ?? '-' }} {{ $transaction->coa ? '- '.$transaction->coa->nama_akun : '' }}</td>
                        <td colspan="3">{{ $transaction->guna_membayar ?? 'Transaksi umum' }}</td>
                        <td class="text-end">{{ number_format((float) $transaction->sejumlah, 0, ',', '.') }}</td>
                        <td class="text-end">0</td>
                        <td>-</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th class="text-end">{{ number_format((float) $transaction->sejumlah, 0, ',', '.') }}</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5 text-center">
            <div class="col-4">
                Dibuat Oleh
                <div style="height: 70px"></div>
                <strong>{{ $transaction->creator->name ?? '-' }}</strong>
            </div>
            <div class="col-4">
                Diperiksa
                <div style="height: 70px"></div>
                <strong>........................</strong>
            </div>
            <div class="col-4">
                Diterima
                <div style="height: 70px"></div>
                <strong>........................</strong>
            </div>
        </div>

        <div class="mt-4 no-print">
            <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button>
            <button class="btn btn-secondary btn-sm" onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>
