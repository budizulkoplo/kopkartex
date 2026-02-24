<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Retur - {{ $hdr->nomor_retur }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            max-width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 2px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 3px 0;
            text-align: left;
        }
        th {
            border-bottom: 1px dashed #000;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px dashed #000;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .signature {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
        .btn-print {
            margin: 10px 0;
            text-align: center;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="btn-print no-print">
        <button onclick="window.print()">Cetak Nota</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="header">
        <h2>NOTA RETUR BARANG</h2>
        <p>{{ config('app.name', 'KOPERASI') }}</p>
    </div>

    <div class="info">
        <div class="info-row">
            <span class="info-label">No. Retur:</span>
            <span>{{ $hdr->nomor_retur }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal:</span>
            <span>{{ \Carbon\Carbon::parse($hdr->tgl_retur)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Supplier:</span>
            <span>{{ $hdr->kode_supplier }} - {{ $hdr->nama_supplier }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Petugas:</span>
            <span>{{ $hdr->petugas }}</span>
        </div>
        @if($hdr->note)
        <div class="info-row">
            <span class="info-label">Catatan:</span>
            <span>{{ $hdr->note }}</span>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dtl as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($hdr->grandtotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="info">
        <div class="info-row">
            <span class="info-label">Total Item:</span>
            <span>{{ $dtl->count() }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Qty:</span>
            <span>{{ $dtl->sum('qty') }}</span>
        </div>
    </div>

    <div class="signature">
        <div>
            <p>Petugas</p>
            <br><br>
            <p>( {{ $hdr->petugas }} )</p>
        </div>
        <div>
            <p>Supplier</p>
            <br><br>
            <p>( {{ $hdr->nama_supplier }} )</p>
        </div>
    </div>

    <div class="footer">
        <p>Terima Kasih</p>
        <p>Barang yang sudah diretur tidak dapat dikembalikan</p>
    </div>

    <script>
        window.onload = function() {
            // Auto print jika parameter ada
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        }
    </script>
</body>
</html>