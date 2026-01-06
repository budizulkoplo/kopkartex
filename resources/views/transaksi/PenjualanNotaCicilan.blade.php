<html>
<head>
    <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
    <style>
        @page { margin: 0 }
        body { 
            margin: 0; 
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            line-height: 1.1;
        }
        
        /* Ukuran kertas 12cm (120mm) seperti sebelumnya */
        .sheet {
            width: 120mm;
            min-height: 100vh;
            padding: 3mm 5mm;
            box-sizing: border-box;
        }
        
        .print-area {
            width: 100%;
            max-width: 110mm;
            margin: 0 auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        td, th {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .txt-left   { text-align: left; }
        .txt-center { text-align: center; }
        .txt-right  { text-align: right; }
        
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 3px 0;
        }
        
        .bold { font-weight: bold; }
        .small { font-size: 9pt; }
        .xsmall { font-size: 8pt; }
        
        /* Untuk cicilan horizontal */
        .cicilan-line {
            display: block;
            line-height: 1.2;
        }
        
        .cicilan-item {
            display: inline-block;
            margin-right: 8px;
        }
        
        @media print {
            body { 
                width: 120mm;
                margin: 0;
                padding: 0;
            }
            
            .no-print { display: none; }
            
            /* Mengurangi margin bawaan browser saat print */
            @page {
                margin: 3mm;
                size: 120mm auto;
            }
        }
        
        @media screen {
            body {
                background: #f0f0f0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            
            .sheet {
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                margin: 10px;
            }
            
            .print-preview {
                color: #666;
                font-size: 9pt;
                text-align: center;
                margin-top: 10px;
                padding: 5px;
                background: #f8f8f8;
                border-top: 1px dashed #ccc;
            }
        }
    </style>
</head>
<body onload="printOut()">
<div class="sheet">
    <div class="print-area">
        
        <!-- Header Perusahaan -->
        <table>
            <tr>
                <td class="txt-center bold" colspan="3">
                    NOTA PENJUALAN KREDIT<br>
                    <?= strtoupper(auth()->user()->unit->nama_unit ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="txt-center xsmall">
                    <?= auth()->user()->unit->alamat ?? '-' ?>
                </td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Informasi Transaksi -->
        <table>
            <tr>
                <td style="width: 30%">Nota</td>
                <td style="width: 2%">:</td>
                <td class="bold"><?= $hdr->nomor_invoice ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td><?= $hdr->tanggal ?></td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td><?= $hdr->kasir ?></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>:</td>
                <td>
                    <?= $hdr->nomor_anggota ? $hdr->nomor_anggota." - ".$hdr->customer : $hdr->customer ?>
                </td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Header Tabel Item -->
        <table>
            <tr class="bold">
                <td style="width: 50%" class="txt-left">ITEM</td>
                <td style="width: 10%" class="txt-center">QTY</td>
                <td style="width: 20%" class="txt-right">HARGA</td>
                <td style="width: 20%" class="txt-right">TOTAL</td>
            </tr>
            <tr>
                <td colspan="4">
                    <hr>
                </td>
            </tr>
        </table>
        
        <!-- Detail Item -->
        <?php if(!empty($dtl)): ?>
            <?php foreach($dtl as $v): ?>
                <table>
                    <tr>
                        <td style="width: 50%" class="txt-left"><?= $v->nama_barang ?></td>
                        <td style="width: 10%" class="txt-center"><?= $v->qty ?></td>
                        <td style="width: 20%" class="txt-right"><?= number_format($v->harga*1, 0, ',', '.') ?></td>
                        <td style="width: 20%" class="txt-right"><?= number_format($v->harga * $v->qty, 0, ',', '.') ?></td>
                    </tr>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <hr>
        
        <!-- Total -->
        <table>
            <tr>
                <td style="width: 70%" class="txt-right">Sub Total</td>
                <td style="width: 30%" class="txt-right"><?= number_format($hdr->subtotal, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="txt-right">Diskon (<?= $hdr->diskon ?>%)</td>
                <td class="txt-right"><?= number_format($hdr->subtotal * ($hdr->diskon/100), 0, ',', '.') ?></td>
            </tr>
            <tr class="bold">
                <td class="txt-right">GRAND TOTAL</td>
                <td class="txt-right"><?= number_format($hdr->grandtotal, 0, ',', '.') ?></td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Rincian Cicilan (HORIZONTAL seperti asli) -->
        <table>
            <tr>
                <td colspan="2" class="txt-left bold">RINCIAN CICILAN:</td>
            </tr>
            <tr>
                <td colspan="2" class="txt-left">
                    <?php
                    $cicilanGrouped = $cicilan->groupBy('kategori');
                    $cicilanText = '';
                    
                    // Bahan Pokok
                    if(isset($cicilanGrouped[0])) {
                        foreach($cicilanGrouped[0] as $c) {
                            $cicilanText .= 'Bahan Pokok: Rp.' . number_format($c->total_cicilan, 0, ',', '.');
                        }
                    }
                    
                    // Non Bahan Pokok
                    if(isset($cicilanGrouped[1])) {
                        $nonPokokItems = [];
                        foreach($cicilanGrouped[1] as $c) {
                            $nonPokokItems[] = "Cicilan {$c->cicilan}: Rp." . number_format($c->total_cicilan, 0, ',', '.');
                        }
                        
                        if(!empty($cicilanText)) {
                            $cicilanText .= ' | ';
                        }
                        
                        $cicilanText .= 'Non Bahan Pokok: ' . implode(' | ', $nonPokokItems);
                    }
                    ?>
                    
                    <span class="cicilan-line"><?= $cicilanText ?></span>
                </td>
            </tr>
        </table>
        
        <hr>
        
        <!-- Footer -->
        <div class="txt-center xsmall" style="margin-top: 5px;">
            * Barang yang sudah dibeli tidak bisa dikembalikan *
        </div>
        
        <!-- Tanda Tangan -->
        <table style="margin-top: 10px;">
            <tr>
                <td class="txt-center" style="width: 50%">
                    Kasir<br><br><br>
                    ( <?= $hdr->kasir ?> )
                </td>
                <td class="txt-center" style="width: 50%">
                    Pembeli<br><br><br>
                    (__________________)
                </td>
            </tr>
        </table>
    
        
    </div>
</div>

<script>
    var lama = 2000; // Waktu lebih lama untuk dot matrix
    
    function printOut() {
        // Tunggu sebentar sebelum print
        setTimeout(function() {
            window.print();
            
            // Tutup window setelah selesai print
            setTimeout(function() {
                window.close();
            }, lama);
        }, 200);
    }
    
    // Fallback jika onload tidak trigger
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(printOut, 500);
    });
</script>
</body>
</html>