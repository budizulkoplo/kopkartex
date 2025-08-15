<html>
<head>
    <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
    <style>
        @page { margin: 0 }
        body { margin: 0; font-size:10px; font-family: monospace; }
        td { font-size:10px; vertical-align: top; }
        .sheet {
            margin: 0;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
        }

        /** Paper sizes **/
        body.struk .sheet { width: 58mm; padding: 2mm; }
        .txt-left { text-align: left; }
        .txt-center { text-align: center; }
        .txt-right { text-align: right; }

        /** Wrap panjang nama barang **/
        .nama-barang {
            word-wrap: break-word;
            white-space: normal;
        }

        /** For screen preview **/
        @media screen {
            body { background: #e0e0e0; font-family: monospace; }
            .sheet {
                background: white;
                box-shadow: 0 .5mm 2mm rgba(0,0,0,.3);
                margin: 5mm;
            }
        }

        /** Print mode **/
        @media print {
            body.struk { width: 58mm; }
            .sheet { padding: 2mm; }
        }
    </style>
</head>
<body class="struk" onload="printOut()">
<section class="sheet">
<?php
// Header toko
echo '<table cellpadding="0" cellspacing="0" style="width:100%">
    <tr><td>NOTA BENGKEL: '.$hdr->nomor_invoice.'</td></tr>
    <tr><td>'.$hdr->name.'</td></tr>
</table>';
echo str_repeat("=", 40)."<br/>";

// Info nota
echo '<table cellpadding="0" cellspacing="0" style="width:100%">
    <tr>
        <td class="txt-left">Nota</td><td>:</td><td class="txt-left">'.$hdr->nomor_invoice.'</td>
    </tr>
    <tr>
        <td class="txt-left">Kasir</td><td>:</td><td class="txt-left">'.$hdr->kasir.'</td>
    </tr>
    <tr>
        <td class="txt-left">Tgl.</td><td>:</td><td class="txt-left">'.$hdr->tanggal.'</td>
    </tr>
    <tr>
        <td colspan="3" class="txt-left">'.$hdr->customer.'</td>
    </tr>
</table><br/>';

// Header kolom item
echo '<table cellpadding="0" cellspacing="0" style="width:100%">
    <tr>
        <td style="width:50%;" class="txt-left"><b>Item</b></td>
        <td style="width:10%;" class="txt-right"><b>Qty</b></td>
        <td style="width:20%;" class="txt-right"><b>Harga</b></td>
        <td style="width:20%;" class="txt-right"><b>Total</b></td>
    </tr>
    <tr><td colspan="4" style="border-bottom:1px solid #000;"></td></tr>';

if (!empty($dtl)) {
    foreach ($dtl as $v) {
        $nama = $v->nama_barang;
        $qty  = $v->qty;
        $harga = format_rupiah($v->harga);
        $total = format_rupiah($v->harga * $v->qty);

        echo "<tr>
            <td class='txt-left nama-barang'>$nama</td>
            <td class='txt-right'>$qty</td>
            <td class='txt-right'>$harga</td>
            <td class='txt-right'>$total</td>
        </tr>";
    }
    echo '<tr><td colspan="4" style="border-top:1px solid #000;"></td></tr>';

    // Sub Total
    echo "<tr>
        <td colspan='2' class='txt-right'>Sub Total</td>
        <td colspan='2' class='txt-right'>".format_rupiah($hdr->subtotal)."</td>
    </tr>";

    // Diskon
    echo "<tr>
        <td colspan='2' class='txt-right'>Diskon</td>
        <td colspan='2' class='txt-right'>".$hdr->diskon."%</td>
    </tr>";

    // Grand Total
    echo "<tr>
        <td colspan='2' class='txt-right'><b>Grand Total</b></td>
        <td colspan='2' class='txt-right'><b>".format_rupiah($hdr->grandtotal)."</b></td>
    </tr>";

    // Bayar
    echo "<tr>
        <td colspan='2' class='txt-right'>Bayar</td>
        <td colspan='2' class='txt-right'>".format_rupiah($hdr->dibayar)."</td>
    </tr>";

    // Kembali
    echo "<tr>
        <td colspan='2' class='txt-right'>Kembali</td>
        <td colspan='2' class='txt-right'>".format_rupiah($hdr->kembali)."</td>
    </tr>";
}
echo '</table><br/>';

// Footer
$footer = 'Terima kasih atas kunjungan anda';
$starSpace = ( 32 - strlen($footer) ) / 2;
$starFooter = str_repeat('*', $starSpace+1);
echo $starFooter.' '.$footer.' '.$starFooter."<br/><br/><br/>";
?>
</section>
</body>
<script>
    var lama = 1000;
    var t = null;
    function printOut(){
        window.print();
        t = setTimeout("self.close()", lama);
    }
</script>
</html>
