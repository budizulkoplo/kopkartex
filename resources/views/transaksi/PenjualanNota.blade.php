<html>
<head>
    <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
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
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
    </style>
</head>
<body class="struk" onload="printOut()">
<section class="sheet">
<div class="print-area">

    <!-- Header -->
    <table style="width:100%; border-collapse:collapse;">
        <tr><td class="txt-center" colspan="3"><b>NOTA PENJUALAN</b></td></tr>
    </table>
    <hr>
    <table style="width:100%; border-collapse:collapse;">
        <tr><td style="width:25%">Nota</td><td>:</td><td><?= $hdr->nomor_invoice ?></td></tr>
        <tr><td>Kasir</td><td>:</td><td><?= $hdr->kasir ?></td></tr>
        <tr><td>Tgl</td><td>:</td><td><?= $hdr->tanggal ?></td></tr>
        <tr>
            <td>Customer</td><td>:</td>
            <td>
                <?= $hdr->nomor_anggota ? $hdr->nomor_anggota." - ".$hdr->customer : $hdr->customer ?>
            </td>
        </tr>
    </table>
    <hr>

    <!-- Detail Item -->
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <th class="txt-left">Item</th>
            <th class="txt-center" style="width:10%">Qty</th>
            <th class="txt-right" style="width:20%">Harga</th>
            <th class="txt-right" style="width:20%">Total</th>
        </tr>
        <tr><td colspan="4"><hr></td></tr>

        <?php if(!empty($dtl)): ?>
            <?php foreach($dtl as $v): ?>
                <tr>
                    <td class="txt-left"><?= $v->nama_barang ?></td>
                    <td class="txt-center"><?= $v->qty ?></td>
                    <td class="txt-right"><?= format_rupiah($v->harga) ?></td>
                    <td class="txt-right"><?= format_rupiah($v->harga * $v->qty) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <tr><td colspan="4"><hr></td></tr>

        <tr>
            <td colspan="3" class="txt-right">Sub Total</td>
            <td class="txt-right"><?= format_rupiah($hdr->subtotal) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="txt-right">Diskon</td>
            <td class="txt-right"><?= $hdr->diskon ?>%</td>
        </tr>
        <tr>
            <td colspan="3" class="txt-right"><b>Grand Total</b></td>
            <td class="txt-right"><b><?= format_rupiah($hdr->grandtotal) ?></b></td>
        </tr>
        <tr>
            <td colspan="3" class="txt-right">Bayar</td>
            <td class="txt-right"><?= format_rupiah($hdr->dibayar) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="txt-right">Kembali</td>
            <td class="txt-right"><?= format_rupiah($hdr->kembali) ?></td>
        </tr>
    </table>

    <br>
    <!-- Footer -->
    <div class="txt-center">
        * Barang yang sudah dibeli tidak bisa dikembalikan *
    </div>

    <!-- Tanda tangan -->
    <br><br><br>
    <table style="width:100%; border-collapse:collapse; margin-top:20px;">
        <tr>
            <td class="txt-center" style="width:50%">Kasir</td>
            <td class="txt-center" style="width:50%">Pembeli</td>
        </tr>
        <tr><td colspan="2" style="height:50px"></td></tr>
        <tr>
            <td class="txt-center">(.........................)</td>
            <td class="txt-center">(.........................)</td>
        </tr>
    </table>

</div>
</section>

<script>
    var lama = 1000;
    function printOut(){
        window.print();
        setTimeout("self.close()", lama);
    }
</script>
</body>
</html>
