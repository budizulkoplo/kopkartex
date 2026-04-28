@php
    $isCicilan = ($hdr->metode_bayar ?? null) === 'cicilan';
    $nomorAnggota = $hdr->nomor_anggota ?? '-';
    $namaCustomer = $hdr->customer ?? '-';
    $tanggalCetak = !empty($hdr->tanggal) ? date('d-m-Y H:i', strtotime($hdr->tanggal)) : date('d-m-Y H:i');
    $namaUnit = strtoupper(auth()->user()->unit->nama_unit ?? 'UNIT TOKO');

    $totalBp = 0;
    $totalNonBp = 0;
    $totalDiskonItem = 0;

    foreach ($dtl as $item) {
        $totalDiskonItem += (float) ($item->diskon ?? 0);
    }

    if ($isCicilan) {
        foreach ($dtl as $item) {
            $totalItem = max((((float) $item->harga) * ((float) $item->qty)) - ((float) ($item->diskon ?? 0)), 0);
            $kategoriCicilan = (int) ($item->kategori_cicilan ?? 1);

            if ($kategoriCicilan === 0) {
                $totalBp += $totalItem;
            } else {
                $totalNonBp += $totalItem;
            }
        }
    }
@endphp
<html>
<head>
    <title>Cetak Nota {{ $hdr->nomor_invoice }}</title>
    <style>
        @page { margin: 0 }

        body {
            margin: 0;
            font-family: "Courier New", monospace;
            font-size: 11px;
            width: 9.5cm;
            box-sizing: border-box;
        }

        .sheet {
            width: 9.5cm;
            padding: 5px;
            box-sizing: border-box;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }

        .line {
            border-bottom: 1px dashed #000;
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            padding: 1px 0;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .item-table td {
            font-size: 11px;
        }

        .col-nama { width: 45%; }
        .col-qty { width: 10%; text-align: right; }
        .col-harga { width: 20%; text-align: right; }
        .col-total { width: 25%; text-align: right; }

        .muted {
            font-size: 10px;
        }

        .signature-space {
            height: 38px;
        }

        @media print {
            body {
                width: 9.5cm;
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .sheet {
                width: 9.5cm;
                margin: 0;
                padding: 5px;
            }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(()=>window.close(),1000);">
    <div class="sheet">
        <div class="center">
            <b style="font-size:13px">CV MANDIRI SEJAHTERA</b>
        </div>

        <table>
            <tr>
                <td class="left"><b>{{ $namaUnit }}</b></td>
                <td class="right"><b>{{ $hdr->nomor_invoice }}</b></td>
            </tr>
        </table>

        <br>

        <div class="center">
            <b>{{ $isCicilan ? 'NOTA PENJUALAN KREDIT' : 'NOTA PENJUALAN' }}</b>
        </div>

        <div class="line"></div>

        <table>
            <tr>
                <td>Nama : {{ $namaCustomer }}</td>
                <td class="right">Kasir : {{ $hdr->kasir }}</td>
            </tr>
            <tr>
                <td>No. Agt : {{ $nomorAnggota }}</td>
                <td></td>
            </tr>
        </table>

        <div class="line"></div>

        <table class="item-table">
            <tr>
                <td class="col-nama"><b>NAMA</b></td>
                <td class="col-qty"><b>QTY</b></td>
                <td class="col-harga"><b>HARGA</b></td>
                <td class="col-total"><b>JML</b></td>
            </tr>
        </table>

        <div class="line"></div>

        @foreach($dtl as $item)
            @php
                $itemGross = ((float) $item->harga) * ((float) $item->qty);
                $itemDiskon = (float) ($item->diskon ?? 0);
                $itemTotal = max($itemGross - $itemDiskon, 0);
            @endphp
            <table class="item-table">
                <tr>
                    <td class="col-nama">{{ strtoupper($item->nama_barang ?? '-') }}</td>
                    <td class="col-qty">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                    <td class="col-harga">{{ number_format((float) $item->harga, 0, ',', '.') }}</td>
                    <td class="col-total">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
                @if($itemDiskon > 0)
                    <tr>
                        <td colspan="3" class="muted">Diskon item</td>
                        <td class="col-total muted">-{{ number_format($itemDiskon, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </table>
        @endforeach

        <div class="line"></div>

        <table>
            <tr>
                <td class="right">Sub Total :</td>
                <td class="right">{{ number_format((float) $hdr->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($totalDiskonItem > 0)
                <tr>
                    <td class="right">Total Diskon Item :</td>
                    <td class="right">Rp. {{ number_format($totalDiskonItem, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="right">Diskon Nota :</td>
                <td class="right">Rp. {{ number_format((float) ($hdr->diskon ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="right"><b>Grand Total :</b></td>
                <td class="right"><b>{{ number_format((float) $hdr->grandtotal, 0, ',', '.') }}</b></td>
            </tr>

            @if(!$isCicilan)
                <tr>
                    <td class="right">Dibayar :</td>
                    <td class="right">{{ number_format((float) $hdr->dibayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="right">Kembali :</td>
                    <td class="right">{{ number_format((float) $hdr->kembali, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>

        @if($isCicilan)
            <div class="line"></div>

            <table>
                <tr>
                    <td class="right">TOTAL BP :</td>
                    <td class="right">{{ number_format($totalBp, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="right">TOTAL NON BP :</td>
                    <td class="right">{{ number_format($totalNonBp, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="right">Tenor :</td>
                    <td class="right">{{ $hdr->tenor }} Kali</td>
                </tr>
            </table>

            @if(isset($cicilan) && $cicilan->count() > 0)
                <div class="line"></div>

                <div><b>RINCIAN CICILAN :</b></div>

                @php
                    $bpList = [];
                    $nonBpList = [];

                    foreach ($cicilan as $row) {
                        $label = ($row->cicilan ?? '-') . ': Rp ' . number_format((float) $row->total_cicilan, 0, ',', '.');

                        if ((int) ($row->kategori ?? 1) === 0) {
                            $bpList[] = $label;
                        } else {
                            $nonBpList[] = $label;
                        }
                    }
                @endphp

                @if(count($bpList) > 0)
                    <div class="muted" style="margin-top:3px">
                        <b>BP</b> {{ implode(' | ', $bpList) }}
                    </div>
                @endif

                @if(count($nonBpList) > 0)
                    <div class="muted" style="margin-top:3px">
                        <b>NON BP</b> {{ implode(' | ', $nonBpList) }}
                    </div>
                @endif
            @endif
        @endif

        <div class="line"></div>

        <div class="center">{{ $tanggalCetak }}</div>

        <br><br><br>

        <table>
            <tr>
                <td class="center">Sales</td>
                <td class="center">Pembeli</td>
            </tr>
            <tr>
                <td class="signature-space"></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td class="center">(__________)</td>
                <td class="center">(__________)</td>
            </tr>
        </table>
    </div>
</body>
</html>
