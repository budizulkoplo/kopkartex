<html>
<head>
    <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
    <style>
        @page {
            width: 72mm; /* Dikurangi dari 80mm */
            margin: 1mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px; /* Diperkecil dari 12px */
            width: 72mm;
            margin: 0 auto;
            line-height: 1.1;
        }
        .sheet {
            width: 100%;
            padding: 1mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .line {
            border-top: 1px dashed #000;
            margin: 2px 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px; /* Diperkecil */
        }
        td {
            padding: 0;
            white-space: nowrap;
        }
        .item-row td:first-child {
            max-width: 35mm; /* Dikurangi */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header-logo {
            font-size: 12px; /* Diperkecil */
            font-weight: bold;
            letter-spacing: 0;
        }
        .flex-row {
            display: flex;
            width: 100%;
        }
        @media print {
            body { width: 72mm; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(()=>window.close(), 1500);">
<div class="sheet">

<?php
$isCicilan = $hdr->metode_bayar == 'cicilan';

$totalBP = 0;
$totalNonBP = 0;

if($isCicilan){
    foreach($dtl as $v){
        $totalItem = $v->harga * $v->qty;
        if($v->jenis == 'jasa'){
            $totalBP += $totalItem;
        } else {
            $kategoriCicilan = $v->barang?->kategori?->cicilan ?? 1;
            if($kategoriCicilan == 0)
                $totalBP += $totalItem;
            else
                $totalNonBP += $totalItem;
        }
    }
}
?>

<!-- HEADER -->
<div class="center">
    <div class="header-logo">CV MANDIRI SEJAHTERA</div>
</div>

<table style="margin:2px 0; font-size:9px;">
<tr>
    <td class="left"><b>UNIT BENGKEL</b></td>
    <td class="right"><b><?= $hdr->nomor_invoice ?></b></td>
</tr>
</table>

<div class="center" style="font-weight:bold; margin:2px 0; font-size:10px;">
    <?= $isCicilan ? 'NOTA PENJUALAN KREDIT' : 'NOTA PENJUALAN' ?>
</div>

<div class="line"></div>

<!-- Customer Info -->
<div style="margin:2px 0; font-size:9px;">
    <span>Nama   : <?= $hdr->customer ?></span><br>
    <span>No.Agt : <?= $hdr->nomor_anggota ?? '-' ?></span>
    <span style="float:right;">Kasir : <?= $hdr->kasir ?></span>
</div>

<div class="line"></div>

<!-- Header Item -->
<div style="display:flex; font-weight:bold; margin:2px 0; font-size:9px;">
    <span style="width:32mm;">NAMA</span>
    <span style="width:8mm; text-align:right;">QTY</span>
    <span style="width:15mm; text-align:right;">HARGA</span>
    <span style="width:15mm; text-align:right;">JML</span>
</div>

<div class="line"></div>

<!-- Items -->
<?php foreach($dtl as $v): ?>
<?php
$nama = '-';
if($v->jenis == 'barang' && $v->barang){
    $nama = $v->barang->nama_barang;
}
if($v->jenis == 'jasa' && $v->jasa){
    $nama = $v->jasa->nama_jasa;
}
// Potong nama jika terlalu panjang
$nama = strlen($nama) > 20 ? substr($nama, 0, 18).'..' : $nama;
$jumlah = $v->harga * $v->qty;
?>
<div style="display:flex; margin:0; font-size:9px;">
    <span style="width:32mm;"><?= strtoupper($nama) ?></span>
    <span style="width:8mm; text-align:right;"><?= $v->qty ?></span>
    <span style="width:15mm; text-align:right;"><?= number_format($v->harga,0,',','.') ?></span>
    <span style="width:15mm; text-align:right;"><?= number_format($jumlah,0,',','.') ?></span>
</div>
<?php endforeach; ?>

<div class="line"></div>

<!-- Total Section -->
<div style="margin:2px 0; font-size:9px;">
    <div style="display:flex;">
        <span style="width:48mm; text-align:right;">Sub Total :</span>
        <span style="width:22mm; text-align:right;"><?= number_format($hdr->subtotal,0,',','.') ?></span>
    </div>
    <div style="display:flex;">
        <span style="width:48mm; text-align:right;">Diskon :</span>
        <span style="width:22mm; text-align:right;"><?= number_format($hdr->diskon,0,',','.') ?></span>
    </div>
    <div style="display:flex; font-weight:bold;">
        <span style="width:48mm; text-align:right;">Grand Total :</span>
        <span style="width:22mm; text-align:right;"><?= number_format($hdr->grandtotal,0,',','.') ?></span>
    </div>
    
    <?php if(!$isCicilan): ?>
    <div style="display:flex;">
        <span style="width:48mm; text-align:right;">Dibayar :</span>
        <span style="width:22mm; text-align:right;"><?= number_format($hdr->dibayar,0,',','.') ?></span>
    </div>
    <div style="display:flex;">
        <span style="width:48mm; text-align:right;">Kembali :</span>
        <span style="width:22mm; text-align:right;"><?= number_format($hdr->kembali,0,',','.') ?></span>
    </div>
    <?php endif; ?>
</div>

<?php if($isCicilan): ?>
<div class="line"></div>

<!-- Informasi Cicilan -->
<div style="margin:2px 0; font-size:9px;">
    <div style="display:flex;">
        <span style="width:40mm;">TOTAL BP :</span>
        <span style="width:30mm; text-align:right;"><?= number_format($totalBP,0,',','.') ?></span>
    </div>
    <div style="display:flex;">
        <span style="width:40mm;">TOTAL NON BP :</span>
        <span style="width:30mm; text-align:right;"><?= number_format($totalNonBP,0,',','.') ?></span>
    </div>
    <div style="display:flex;">
        <span style="width:40mm;">Tenor :</span>
        <span style="width:30mm; text-align:right;"><?= $hdr->tenor ?> Kali</span>
    </div>
</div>

<?php if(isset($cicilan) && $cicilan->count() > 0): ?>
<div class="line"></div>
<div style="font-weight:bold; font-size:9px;">RINCIAN CICILAN NON BP :</div>

<?php
$nonbp = $cicilan->where('kategori',1);
$cicilan_text = [];
foreach($nonbp as $c) {
    $cicilan_text[] = "{$c->cicilan}: Rp". number_format($c->total_cicilan,0,',','.');
}
$text_line = implode(' | ', $cicilan_text);
// Wrap text untuk multiple lines
$lines = str_split($text_line, 38);
foreach($lines as $line) {
    echo "<div style='margin:1px 0; font-size:8px;'>$line</div>";
}
?>
<?php endif; ?>
<?php endif; ?>

<div class="line"></div>

<div class="center" style="font-size:9px;">
    <?= date('d-m-Y H:i', strtotime($hdr->tanggal)) ?>
</div>

<br>
<!-- Signature -->
<div style="display:flex; margin-top:10px; font-size:9px;">
    <span style="width:36mm; text-align:center;">Sales</span>
    <span style="width:36mm; text-align:center;">Pembeli</span>
</div>
<div style="display:flex; margin-top:15px; font-size:9px;">
    <span style="width:36mm; text-align:center;">(__________)</span>
    <span style="width:36mm; text-align:center;">(__________)</span>
</div>

</div>
</body>
</html>