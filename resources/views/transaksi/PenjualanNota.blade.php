<html>
    <head>
        <title>Cetak Nota <?= $hdr->nomor_invoice ?></title>
        <style>
            @page { margin: 0 }
            body { margin: 0; font-size:10px;font-family: monospace;}
            td { font-size:10px; }
            .sheet {
            margin: 0;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
            }

            /** Paper sizes **/
            body.struk        .sheet { width: 58mm; }
            body.struk .sheet        { padding: 2mm; }

            .txt-left { text-align: left;}
            .txt-center { text-align: center;}
            .txt-right { text-align: right;}

            /** For screen preview **/
            @media screen {
            body { background: #e0e0e0;font-family: monospace; }
            .sheet {
                background: white;
                box-shadow: 0 .5mm 2mm rgba(0,0,0,.3);
                margin: 5mm;
            }
            }

            /** Fix for Chrome issue #273306 **/
            @media print {
                body { font-family: monospace; }
                body.struk                 { width: 58mm; text-align: left;}
                body.struk .sheet          { padding: 2mm; }
                .txt-left { text-align: left;}
                .txt-center { text-align: center;}
                .txt-right { text-align: right;}
            }
        </style>
    </head>
    <body class="struk" onload="printOut()">
        <section class="sheet">
        <?php
            echo '<table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>'.$hdr->nomor_invoice.'</td>
                    </tr>
                    <tr>
                        <td>'.$hdr->name.'</td>
                    </tr>
                </table>';
            echo(str_repeat("=", 40)."<br/>");
            $invoice = $hdr->nomor_invoice. str_repeat("&nbsp;", (40 - (strlen($hdr->nomor_invoice))));
            $kasir = $hdr->name. str_repeat("&nbsp;", (40 - (strlen($hdr->name))));
            $tgl = date('d-m-Y H:i:s', strtotime($hdr->created_at)). str_repeat("&nbsp;", (40 - (strlen(date('d-m-Y H:i:s', strtotime($hdr->created_at))))));
            $customer = $hdr->customer;
            $customer = $customer. str_repeat("&nbsp;", (48 - (strlen($customer))));

            echo '<table cellpadding="0" cellspacing="0" style="width:100%">
                    <tr>
                        <td align="left" class="txt-left">Nota&nbsp;</td>
                        <td align="left" class="txt-left">:</td>
                        <td align="left" class="txt-left">&nbsp;'. $hdr->nomor_invoice. '.</td>
                    </tr>
                    <tr>
                        <td align="left" class="txt-left">Kasir</td>
                        <td align="left" class="txt-left">:</td>
                        <td align="left" class="txt-left">&nbsp;'. $hdr->kasir.'</td>
                    </tr>
                    <tr>
                        <td align="left" class="txt-left">Tgl.&nbsp;</td>
                        <td align="left" class="txt-left">:</td>
                        <td align="left" class="txt-left">&nbsp;'. $hdr->tanggal.'</td>
                    </tr>
                    <tr>
                        <td align="left" colspan="3" class="txt-left">'.$hdr->customer.'</td>
                    </tr>
                </table>';
            echo '<br/>';
            $tItem = 'Item'. str_repeat("&nbsp;", (13 - strlen('Item')));
            $tQty  = 'Qty'. str_repeat("&nbsp;", (6 - strlen('Qty')));
            $tHarga= str_repeat("&nbsp;", (9 - strlen('Harga'))).'Harga';
            $tTotal= str_repeat("&nbsp;", (10 - strlen('Total'))).'Total';
            $caption = $tItem. $tQty. $tHarga. $tTotal;

            echo    '<table cellpadding="0" cellspacing="0" style="width:100%">
                        <tr>
                            <td align="left" class="txt-left">'. $caption . '</td>
                        </tr>
                        <tr>
                            <td align="left" class="txt-left">'. str_repeat("=", 38) . '</td>
                        </tr>';
            if(!empty( $dtl ))
            {
                foreach($dtl as $k=>$v)
                {
                    $item = $v->nama_barang. str_repeat("&nbsp;", (38 - (strlen($v->nama_barang))));
                    echo '<tr>';
                        echo'<td align="left" class="txt-left">'.$item.'</td>';
                    echo '</tr>';

                    echo '<tr>';
                    $qty        = $v->qty;
                    $qty        = $qty. str_repeat("&nbsp;", ( 13 - strlen($qty)) );
    
                    $price      = format_rupiah($v->harga);
                    $price      = str_repeat("&nbsp;", ( 9 - strlen($price)) ). $price;

                    $total      = format_rupiah($v->harga*$v->qty);
                    $lentotal   = strlen($total);
                    $total      = str_repeat("&nbsp;", ( 10 - $lentotal) ). $total;
                        echo'<td class="txt-left" align="left">'.$qty. $price. $total .'</td>';
                    
                    echo '</tr>';
                }

                echo '<tr><td>'. str_repeat('-', 38).'</td></tr>';

                //Sub Total
                $titleST = 'Sub&nbspTotal';
                $titleST = $titleST. str_repeat("&nbsp;", ( 19 - strlen($titleST)) );
                $ST      = format_rupiah($hdr->subtotal);
                $ST      = str_repeat("&nbsp;", ( 23 - strlen($ST)) ). $ST;
                echo '<tr><td>'. $titleST. $ST.'</td></tr>';
                //Diskon
                $titleDs = 'Diskon';
                $titleDs = $titleDs. str_repeat("&nbsp;", ( 15 - strlen($titleDs)) );
                $Ds      = $hdr->diskon.'%';
                $Ds      = str_repeat("&nbsp;", ( 23 - strlen($Ds)) ). $Ds;
                echo '<tr><td>'. $titleDs. $Ds.'</td></tr>';

                //Grand Total
                $titleGT = 'Grand&nbspTotal';
                $titleGT = $titleGT. str_repeat("&nbsp;", ( 19 - strlen($titleGT)) );
                $GT      = format_rupiah($hdr->grandtotal);
                $GT      = str_repeat("&nbsp;", ( 23 - strlen($GT)) ). $GT;
                echo '<tr><td>'. $titleGT. $GT.'</td></tr>';
                
                //Bayar
                $titlePy = 'BAYAR';
                $titlePy = $titlePy. str_repeat("&nbsp;", ( 15 - strlen($titlePy)) );
                $Py      = format_rupiah($hdr->dibayar);
                $Py      = str_repeat("&nbsp;", ( 23 - strlen($Py)) ). $Py;
                echo '<tr><td>'. $titlePy. $Py.'</td></tr>';

                //Kembali
                $titleK = 'KEMBALI';
                $titleK = $titleK. str_repeat("&nbsp;", ( 15 - strlen($titleK)) );
                $Kb     = format_rupiah($hdr->kembali);
                $Kb      = str_repeat("&nbsp;", ( 23 - strlen($Kb)) ). $Kb;
                echo '<tr><td>'. $titleK. $Kb.'</td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';

            }
            echo '</table>';

            $footer = 'Terima kasih atas kunjungan anda';
            $starSpace = ( 32 - strlen($footer) ) / 2;
            $starFooter = str_repeat('*', $starSpace+1);
            echo($starFooter. '&nbsp;'.$footer . '&nbsp;'. $starFooter."<br/><br/><br/><br/>");
            echo '<p>&nbsp;</p>';  
            
        ?>
        </section>
        
    </body>
    <script>
            var lama = 1000;
            t = null;
            function printOut(){
                window.print();
                t = setTimeout("self.close()",lama);
            }
</script>
</html>