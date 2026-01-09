<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota Mutasi {{ $hdr->nomor_mutasi }}</title>
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

        /* Lebar kertas 12cm (100%) */
        body.struk .sheet { width: 100%; padding: 5mm; }

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
            .preview-controls {
                position: fixed;
                top: 10px;
                right: 10px;
                z-index: 1000;
            }
        }

        @media print {
            body.struk { width: 100%; }
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

        .info-label {
            font-weight: bold;
        }
        
        .status-selesai {
            color: green;
            font-weight: bold;
        }
        
        .status-diajukan {
            color: orange;
            font-weight: bold;
        }
        
        .status-dibatalkan {
            color: red;
            font-weight: bold;
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
                    <b>NOTA MUTASI STOK</b>
                </td>
            </tr>
            <tr>
                <td class="txt-center" colspan="3">
                    <div class="barcode">*{{ $hdr->nomor_mutasi }}*</div>
                </td>
            </tr>
        </table>
        <hr>

        {{-- Info Mutasi --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:35%">Nomor Mutasi</td>
                <td style="width:5%">:</td>
                <td>{{ $hdr->nomor_mutasi }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($hdr->tanggal)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>:</td>
                <td>{{ $hdr->petugas }}</td>
            </tr>
            <tr>
                <td>Dari Unit</td>
                <td>:</td>
                <td>{{ $hdr->nama_unit_asal }}</td>
            </tr>
            <tr>
                <td>Ke Unit</td>
                <td>:</td>
                <td>{{ $hdr->nama_unit_tujuan }}</td>
            </tr>
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
                <th class="txt-left" style="width:5%">Barcode</th>
                <th class="txt-left" style="width:55%" colspan='2'>Item</th>
                <th class="txt-center" style="width:15%">Qty</th>
            </tr>
            <tr><td colspan="5"><hr></td></tr>

            @foreach($dtl as $index => $item)
            <tr>
                <td class="txt-left">{{ $index + 1 }}</td>
                <td class="txt-left">{{ $item->kode_barang }}</td>
                <td class="txt-left" colspan="2">
                    <span>
                        {{ substr($item->nama_barang . ' (' . $item->type . ')', 0, 32) }}
                        {{ strlen($item->nama_barang . ' (' . $item->type . ')') > 32 ? '...' : '' }}
                    </span>
                </td>
                <td class="txt-center">{{ number_format($item->qty, 0) }}</td>
               
            </tr>
            @endforeach

            <tr><td colspan="5"><hr></td></tr>

            {{-- Summary --}}
            @php
                $totalQty = $dtl->sum('qty');
                $totalItem = $dtl->count();
            @endphp

            <tr>
                <td colspan="2" class="txt-right">Total Item:</td>
                <td colspan="2" class="txt-center">{{ $totalItem }}</td>
            </tr>
            <tr>
                <td colspan="2" class="txt-right"><b>Total Qty Mutasi:</b></td>
                <td colspan="2" class="txt-center"><b>{{ $totalQty }}</b></td>
            </tr>
        </table>

        <br>
        {{-- Footer Note --}}
        <div class="txt-center" style="font-size: 9pt;">
            * Barang yang sudah dimutasikan menjadi tanggung jawab unit penerima *
        </div>

        {{-- Tanda tangan --}}
        <br><br><br>
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td class="txt-center" style="width:50%">Pengirim</td>
                <td class="txt-center" style="width:50%">Penerima</td>
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

        // Auto print jika diakses dari halaman mutasi
        if (window.location.search.includes('autoprint=true')) {
            window.print();
        }
    </script>
</body>
</html>