<html>
<head>
    <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
    <style>
        @page { margin: 0 }
        body {
            margin: 0;
            font-family: monospace;
            font-size: 11px;
            width: 9.5cm;
        }

        .sheet { width: 9.5cm; padding: 5px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }

        .line {
            border-bottom: 1px dashed #000;
            margin: 3px 0;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        @media print { body { width: 9.5cm; } }
    </style>
</head>

<body onload="window.print(); setTimeout(()=>window.close(),1000);">
<div class="sheet">

<?php
$isCicilan = $hdr->metode_bayar == 'cicilan';

$totalBP = 0;      // jasa + kategori 0
$totalNonBP = 0;   // kategori 1

// Kelompokkan total
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
    <b>CV MANDIRI SEJAHTERA</b><br>
    UNIT BENGKEL<br><br>
    <b><?= $isCicilan ? 'NOTA PENJUALAN KREDIT' : 'NOTA PENJUALAN' ?></b>
</div>

<div class="line"></div>

<table>
<tr><td>Nama</td><td>:</td><td><?= $hdr->customer ?></td></tr>
<tr><td>No Nota</td><td>:</td><td><?= $hdr->nomor_invoice ?></td></tr>
<tr><td>Tanggal</td><td>:</td><td><?= date('d-m-Y H:i', strtotime($hdr->tanggal)) ?></td></tr>
<tr><td>Kasir</td><td>:</td><td><?= $hdr->kasir ?></td></tr>
</table>

<div class="line"></div>

<table>
<tr>
    <td width="45%"><b>NAMA</b></td>
    <td width="10%" class="right"><b>QTY</b></td>
    <td width="20%" class="right"><b>HARGA</b></td>
    <td width="25%" class="right"><b>JML</b></td>
</tr>
</table>

<div class="line"></div>

<?php foreach($dtl as $v): ?>

<?php
$nama = '-';
if($v->jenis == 'barang' && $v->barang){
    $nama = $v->barang->nama_barang;
}
if($v->jenis == 'jasa' && $v->jasa){
    $nama = $v->jasa->nama_jasa;
}
?>

<table>
<tr>
    <td width="45%"><?= strtoupper($nama) ?></td>
    <td width="10%" class="right"><?= $v->qty ?></td>
    <td width="20%" class="right"><?= number_format($v->harga,0,',','.') ?></td>
    <td width="25%" class="right"><?= number_format($v->harga * $v->qty,0,',','.') ?></td>
</tr>
</table>

<?php endforeach; ?>

<div class="line"></div>

<table>
<tr>
    <td class="right">Sub Total :</td>
    <td class="right"><?= number_format($hdr->subtotal,0,',','.') ?></td>
</tr>
<tr>
    <td class="right">Diskon :</td>
    <td class="right"><?= $hdr->diskon ?> %</td>
</tr>
<tr>
    <td class="right"><b>Grand Total :</b></td>
    <td class="right"><b><?= number_format($hdr->grandtotal,0,',','.') ?></b></td>
</tr>

<?php if(!$isCicilan): ?>
<tr>
    <td class="right">Dibayar :</td>
    <td class="right"><?= number_format($hdr->dibayar,0,',','.') ?></td>
</tr>
<tr>
    <td class="right">Kembali :</td>
    <td class="right"><?= number_format($hdr->kembali,0,',','.') ?></td>
</tr>
<?php endif; ?>
</table>

<?php if($isCicilan): ?>
<div class="line"></div>

<table>
<tr>
    <td class="right">TOTAL BP :</td>
    <td class="right"><?= number_format($totalBP,0,',','.') ?></td>
</tr>
<tr>
    <td class="right">TOTAL NON BP :</td>
    <td class="right"><?= number_format($totalNonBP,0,',','.') ?></td>
</tr>
<tr>
    <td class="right">Tenor :</td>
    <td class="right"><?= $hdr->tenor ?> Kali</td>
</tr>
</table>

<?php if(isset($cicilan) && $cicilan->count() > 0): ?>

<div class="line"></div>
<b>RINCIAN CICILAN NON BP</b><br>

<?php
$nonbp = $cicilan->where('kategori',1);
?>

<?php foreach($nonbp as $c): ?>
Cicilan <?= $c->cicilan ?> :
Rp. <?= number_format($c->total_cicilan,0,',','.') ?><br>
<?php endforeach; ?>

<?php endif; ?>

<?php endif; ?>

<div class="line"></div>

<div class="center">
    Terima kasih atas kunjungan anda
</div>

<br><br><br>

<table width="100%">
<tr>
    <td class="center">Sales</td>
    <td class="center">Pembeli</td>
</tr>
<tr>
    <td class="center">(__________)</td>
    <td class="center">(__________)</td>
</tr>
</table>

</div>
</body>
</html>