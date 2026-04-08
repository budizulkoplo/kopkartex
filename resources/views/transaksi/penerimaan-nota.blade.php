<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota {{ $hdr->nomor_invoice }}</title>
    <style>
        @page { margin: 0 }
        body { margin: 0; font-size:11pt; font-family: monospace; }
        td, th { font-size:11pt; padding: 2px; }
        .sheet {
            margin: 0;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
        }

        /* Lebar kertas 12cm (120mm) */
        body.struk .sheet { width: 120mm; padding: 5mm; }

        .print-area { width: 100%; margin: 0 auto; }

        .txt-left   { text-align: left; }
        .txt-center { text-align: center; }
        .txt-right  { text-align: right; }

        @media screen {
            body { background: #e0e0e0; }
            .sheet {
                background: white;
                box-shadow: 0 .5mm 2mm rgba(0,0,0,.3);
                margin: 5mm;
            }
        }

        @media print {
            body.struk { width: 120mm; }
            .no-print { display: none; }
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .barcode {
            font-family: 'Libre Barcode 128', monospace;
            font-size: 24px;
        }

        /* Style untuk preview mode */
        @media screen {
            .preview-controls {
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 1000;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
</head>
<body class="struk" onload="printOut()">

    {{-- Preview Controls --}}
    <div class="preview-controls no-print">
        <button onclick="window.print()" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">
            🖨️ Cetak
        </button>
        <button onclick="window.close()" style="padding: 5px 10px; background: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer; margin-left: 5px;">
            ✖️ Tutup
        </button>
    </div>

    <section class="sheet">
    <div class="print-area">

        {{-- Header --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="txt-center" colspan="3">
                    <b>NOTA PENERIMAAN BARANG <br>{{ auth()->user()->unit->nama_unit ?? '-' }}</b>
                </td>
            </tr>
            <tr>
                <td class="txt-center" colspan="3">
                    <div class="barcode">*{{ $hdr->nomor_invoice }}*</div>
                </td>
            </tr>
        </table>
        <hr>

        {{-- Info Penerimaan --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:30%">Nota</td>
                <td style="width:5%">:</td>
                <td>{{ $hdr->nomor_invoice }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($hdr->tgl_penerimaan)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>:</td>
                <td>{{ $hdr->petugas }}</td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td>:</td>
                <td>{{ $hdr->nama_supplier }}</td>
            </tr>
            <tr>
                <td>Kode Supplier</td>
                <td>:</td>
                <td>{{ $hdr->kode_supplier ?? '-' }}</td>
            </tr>
            <tr>
                <td>Metode Bayar</td>
                <td>:</td>
                <td>{{ strtoupper($hdr->metode_bayar) }}</td>
            </tr>
            @if($hdr->metode_bayar == 'tempo')
            <tr>
                <td>Tgl Tempo</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($hdr->tgl_tempo)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Status Bayar</td>
                <td>:</td>
                <td>{{ strtoupper($hdr->status_bayar) }}</td>
            </tr>
            @endif
            <tr>
                <td>Catatan</td>
                <td>:</td>
                <td>{{ $hdr->note ? substr($hdr->note, 0, 30) . (strlen($hdr->note) > 30 ? '...' : '') : '-' }}</td>
            </tr>
        </table>
        <hr>

        {{-- Detail Barang --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <th class="txt-left" style="width:5%">#</th>
                <th class="txt-left" style="width:45%" colspan='2'>Item</th>
                <th class="txt-center" style="width:10%">Qty</th>
                <th class="txt-right" style="width:20%">H.Beli</th>
            </tr>
            <tr><td colspan="5"><hr></td></tr>

            @foreach($dtl as $index => $item)
            <tr>
                <td class="txt-left">{{ $index + 1 }}</td>
                <td class="txt-left" colspan='2'>
                    {{ substr($item->kode_barang, 0, 8) }}<br>
                    <small>{{ substr($item->nama_barang, 0, 20) }}{{ strlen($item->nama_barang) > 20 ? '...' : '' }}</small>
                </td>
                <td class="txt-center">{{ number_format($item->jumlah, 0) }}</td>
                <td class="txt-right">{{ number_format($item->harga_beli, 0) }}</td>

            </tr>
            @endforeach

            <tr><td colspan="5"><hr></td></tr>

            {{-- Summary --}}
            @php
                $subtotal = $dtl->sum(function($item) {
                    return $item->harga_beli * $item->jumlah;
                });
                $totalPpn = $dtl->sum('ppn');
                $grandTotal = $dtl->sum('subtotal');
            @endphp

            <tr>
                <td colspan="4" class="txt-right">Subtotal Beli</td>
                <td class="txt-right">{{ number_format($subtotal, 0) }}</td>
            </tr>
            @if($totalPpn > 0)
            <tr>
                <td colspan="4" class="txt-right">PPN</td>
                <td class="txt-right">{{ number_format($totalPpn, 0) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" class="txt-right"><b>Grand Total</b></td>
                <td class="txt-right"><b>{{ number_format($grandTotal, 0) }}</b></td>
            </tr>
            
        </table>

        <br>
        {{-- Footer Note --}}
        <div class="txt-center" style="font-size: 9pt;">
            * Barang yang sudah diterima menjadi tanggung jawab gudang *
        </div>

        {{-- Tanda tangan --}}
        <br><br><br>
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td class="txt-center" style="width:50%">Petugas</td>
                <td class="txt-center" style="width:50%">Supplier</td>
            </tr>
            <tr><td colspan="2" style="height:40px"></td></tr>
            <tr>
                <td class="txt-center">(_________________)</td>
                <td class="txt-center">(_________________)</td>
            </tr>
        </table>

        {{-- Timestamp --}}
        <div class="txt-center" style="margin-top: 10px; font-size: 9pt;">
            Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>

    </div>
    </section>

    <script>
        var lama = 1000;
        
        function printOut(){
            window.print();
            setTimeout(function() {
                window.close();
            }, lama);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P untuk print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // ESC untuk close
            if (e.key === 'Escape') {
                window.close();
            }
        });

        // Auto print jika diakses dari halaman penerimaan
        if (window.location.search.includes('autoprint=true')) {
            window.print();
        }
    </script>
</body>
</html>
