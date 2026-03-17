<html>
<head>
<title>Cetak Nota <?= $hdr->nomor_invoice ?></title>

<style>
@page { margin:0 }

body{
    margin:0;
    font-family:"Courier New", monospace;
    font-size:12px; /* diseragamkan */
    padding-left:3mm; /* tambah jarak kiri */
    box-sizing:border-box;
}

.sheet{
    width:77mm; /* dikurangi sedikit karena body pakai padding kiri */
    margin:auto;
}

.center{ text-align:center; }
.right{ text-align:right; }
.left{ text-align:left; }

.line{
    border-top:1px dashed #000;
    margin:4px 0;
}

table{
    width:100%;
    border-collapse:collapse;
}

td{
    padding:1px 0;
    font-size:12px; /* samakan semua isi tabel */
}

.item-table td{
    font-size:12px;
}

.col-nama{ width:38%; }
.col-qty{ width:10%; text-align:right; }
.col-harga{ width:22%; text-align:right; }
.col-total{ width:30%; text-align:right; }

@media print{
    body{
        width:80mm;
        padding-left:3mm; /* tetap ada saat print */
        box-sizing:border-box;
    }

    .sheet{
        width:77mm;
        margin:0;
    }
}
</style>
</head>


<body onload="window.print(); setTimeout(()=>window.close(),1000);">

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
<b style="font-size:14px">CV MANDIRI SEJAHTERA</b>
</div>

<table>
<tr>
<td><b>UNIT BENGKEL</b></td>
<td class="right"><b><?= $hdr->nomor_invoice ?></b></td>
</tr>
</table>

<br>

<div class="center">
<b><?= $isCicilan ? 'NOTA PENJUALAN KREDIT' : 'NOTA PENJUALAN' ?></b>
</div>

<div class="line"></div>

<table>
<tr>
<td>Nama : <?= $hdr->customer ?></td>
<td class="right">Kasir : <?= $hdr->kasir ?></td>
</tr>

<tr>
<td>No. Agt : <?= $hdr->nomor_anggota ?? '-' ?></td>
<td></td>
</tr>
</table>

<div class="line"></div>


<!-- HEADER ITEM -->
<table class="item-table">
<tr>
<td class="col-nama"><b>NAMA</b></td>
<td class="col-qty"><b>QTY</b></td>
<td class="col-harga"><b>HARGA</b></td>
<td class="col-total"><b>JML</b></td>
</tr>
</table>

<div class="line"></div>


<!-- ITEM LIST -->

<?php foreach($dtl as $v): ?>

<?php
$nama='-';

if($v->jenis=='barang' && $v->barang)
    $nama=$v->barang->nama_barang;

if($v->jenis=='jasa' && $v->jasa)
    $nama=$v->jasa->nama_jasa;
?>

<table class="item-table">
<tr>
<td class="col-nama"><?= strtoupper($nama) ?></td>
<td class="col-qty"><?= $v->qty ?></td>
<td class="col-harga"><?= number_format($v->harga,0,',','.') ?></td>
<td class="col-total"><?= number_format($v->harga*$v->qty,0,',','.') ?></td>
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
<td class="right">Rp. <?= number_format($hdr->diskon,0,',','.') ?></td>
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



<?php if(isset($cicilan) && $cicilan->count()>0): ?>

<div class="line"></div>

<div><b>RINCIAN CICILAN NON BP :</b></div>

<?php
$nonbp = $cicilan->where('kategori',1);
$cicilan_list=[];

foreach($nonbp as $c){
$cicilan_list[]="{$c->cicilan}: Rp ".number_format($c->total_cicilan,0,',','.');
}
?>

<div style="margin-top:3px">
<?= implode(' | ',$cicilan_list) ?>
</div>

<?php endif; ?>
<?php endif; ?>

<div class="line"></div>

<div class="center">
<?= date('d-m-Y H:i',strtotime($hdr->tanggal)) ?>
</div>

<br><br>

<table>
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