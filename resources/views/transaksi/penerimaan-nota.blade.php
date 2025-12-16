<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penerimaan - {{ $hdr->nomor_invoice }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                font-size: 12px;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
            .card {
                border: none;
                box-shadow: none;
            }
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .invoice-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .invoice-title {
            font-weight: bold;
            font-size: 18px;
        }
        .invoice-detail {
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f8f9fa;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            font-weight: bold;
            font-size: 16px;
        }
        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #000;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <div class="card no-print">
            <div class="card-header">
                <h5 class="card-title mb-0">Preview Nota Penerimaan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 mb-3">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Cetak Nota
                    </button>
                    <button onclick="window.history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                </div>
            </div>
        </div>

        <div class="invoice-wrapper" id="printable-area">
            {{-- Header Nota --}}
            <div class="invoice-header text-center mb-4">
                <h2 class="invoice-title">NOTA PENERIMAAN BARANG</h2>
                <p class="invoice-detail">
                    {{ config('app.name', 'Laravel') }}<br>
                    Invoice: {{ $hdr->nomor_invoice }}
                </p>
            </div>

            {{-- Info Penerimaan --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Tanggal</strong></td>
                            <td>: {{ \Carbon\Carbon::parse($hdr->tgl_penerimaan)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Supplier</strong></td>
                            <td>: {{ $hdr->nama_supplier }}</td>
                        </tr>
                        <tr>
                            <td><strong>Metode Bayar</strong></td>
                            <td>: {{ strtoupper($hdr->metode_bayar) }}</td>
                        </tr>
                        @if($hdr->metode_bayar == 'tempo')
                        <tr>
                            <td><strong>Tanggal Tempo</strong></td>
                            <td>: {{ \Carbon\Carbon::parse($hdr->tgl_tempo)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Bayar</strong></td>
                            <td>: {{ strtoupper($hdr->status_bayar) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Petugas</strong></td>
                            <td>: {{ $hdr->petugas }}</td>
                        </tr>
                        <tr>
                            <td><strong>Catatan</strong></td>
                            <td>: {{ $hdr->note ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Detail Barang --}}
            <table class="table table-sm mb-4">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Harga Beli</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dtl as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="text-center">{{ number_format($item->jumlah, 0) }}</td>
                        <td class="text-right">{{ number_format($item->harga_beli, 2) }}</td>
                        <td class="text-right">{{ number_format($item->harga_jual, 2) }}</td>
                        <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-section">
                        <td colspan="6" class="text-right"><strong>TOTAL</strong></td>
                        <td class="text-right">{{ number_format($hdr->grandtotal, 2) }}</td>
                    </tr>

                </tfoot>
            </table>

            {{-- Footer --}}
            <div class="footer-note">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1">Catatan:</p>
                        <p>{{ $hdr->note ?? 'Tidak ada catatan' }}</p>
                    </div>
                    <div class="col-md-6 text-center">
                        <p>Hormat kami,</p>
                        <br><br><br>
                        <p><strong>{{ $hdr->petugas }}</strong></p>
                        <p>Petugas Penerimaan</p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <p class="mb-0">Nota ini dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengubah angka menjadi terbilang
        function terbilang(angka) {
            // Implementasi fungsi terbilang sesuai kebutuhan
            // Anda bisa menggunakan library atau fungsi terbilang yang sudah ada
            return '...';
        }

        // Auto print jika diperlukan
        window.onload = function() {
            // Optional: auto print setelah load
            // window.print();
        };
    </script>
</body>
</html>