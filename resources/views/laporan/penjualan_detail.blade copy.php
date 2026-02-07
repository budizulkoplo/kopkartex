<x-app-layout>
    <x-slot name="pagetitle">Laporan Penjualan Detail</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Laporan Penjualan Detail</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-inline-flex gap-2">
                        <input type="date" id="start_date" class="form-control form-control-sm"
                               value="{{ $start_date }}" onchange="reloadTable()" />
                        <input type="date" id="end_date" class="form-control form-control-sm"
                               value="{{ $end_date }}" onchange="reloadTable()" />

                        <select id="unit" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all">Semua Unit</option>
                            @foreach($units as $id => $nama)
                                <option value="{{ $id }}" @if($unit_id == $id) selected @endif>{{ $nama }}</option>
                            @endforeach
                        </select>

                        <select id="metode" class="form-select form-select-sm" onchange="reloadTable()">
                            <option value="all" @if($metode_bayar=='all') selected @endif>Semua Metode</option>
                            <option value="tunai" @if($metode_bayar=='tunai') selected @endif>Tunai</option>
                            <option value="cicilan" @if($metode_bayar=='cicilan') selected @endif>Cicilan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title mb-0">Detail Transaksi Penjualan</h5>
                    </div>
                    <table id="tbpenjualan" class="table table-sm table table-bordered" style="width:100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Unit</th>
                                <th>Metode</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Print Content --}}
    <div id="printArea" style="display: none;"></div>

    {{-- JS Custom --}}
    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>
        <script>
            let table;

            function reloadTable() {
                table.ajax.reload();
            }

            function printReport() {
                // Ambil data dari DataTable
                let data = table.rows().data().toArray();
                
                if (data.length === 0) {
                    alert('Tidak ada data untuk dicetak!');
                    return;
                }

                // Buat konten print yang sederhana untuk dot matrix
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Penjualan Detail</title>
                        <style>
                            /* Reset untuk printer dot matrix */
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Courier New', monospace;
                                font-size: 11px;
                                line-height: 1;
                                color: #000;
                                padding: 0;
                                margin: 0;
                                background: white;
                                width: 100%;
                            }
                            
                            /* Paper size untuk kertas continuous form */
                            @page {
                                size: 8.5in 11in;
                                margin: 0.2in;
                            }
                            
                            @media print {
                                body {
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                    color-adjust: exact !important;
                                }
                                
                                /* Force monochrome untuk dot matrix */
                                * {
                                    color: black !important;
                                    background-color: white !important;
                                }
                                
                                .no-print {
                                    display: none !important;
                                }
                            }
                            
                            /* Header */
                            .header {
                                text-align: center;
                                margin-bottom: 15px;
                                padding-bottom: 5px;
                                border-bottom: 1px solid #000;
                                width: 100%;
                            }
                            
                            .header h1 {
                                font-size: 14px;
                                font-weight: bold;
                                margin-bottom: 3px;
                                letter-spacing: 1px;
                            }
                            
                            .header p {
                                margin: 2px 0;
                                font-size: 10px;
                            }
                            
                            /* Tabel untuk dot matrix */
                            .table-container {
                                width: 100%;
                                page-break-inside: avoid;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                table-layout: fixed;
                                word-wrap: break-word;
                                word-break: break-all;
                            }
                            
                            /* Column widths untuk printer 132 column */
                            th, td {
                                border: none;
                                border-right: 1px solid #000;
                                border-bottom: 1px solid #000;
                                padding: 3px 2px;
                                font-size: 10px;
                                line-height: 1.1;
                                vertical-align: top;
                                overflow: hidden;
                                text-overflow: ellipsis;
                            }
                            
                            th:last-child, td:last-child {
                                border-right: none;
                            }
                            
                            tr:last-child td {
                                border-bottom: none;
                            }
                            
                            /* Specific column widths */
                            th:nth-child(1), td:nth-child(1) { width: 8%; }  /* Tanggal */
                            th:nth-child(2), td:nth-child(2) { width: 10%; } /* Invoice */
                            th:nth-child(3), td:nth-child(3) { width: 12%; } /* Customer */
                            th:nth-child(4), td:nth-child(4) { width: 8%; }  /* Unit */
                            th:nth-child(5), td:nth-child(5) { width: 7%; }  /* Metode */
                            th:nth-child(6), td:nth-child(6) { width: 10%; } /* Kode Barang */
                            th:nth-child(7), td:nth-child(7) { width: 15%; } /* Nama Barang */
                            th:nth-child(8), td:nth-child(8) { width: 6%; }  /* Qty */
                            th:nth-child(9), td:nth-child(9) { width: 12%; } /* Harga */
                            th:nth-child(10), td:nth-child(10) { width: 12%; } /* Total */
                            
                            /* Header table */
                            thead th {
                                background-color: #f0f0f0 !important;
                                font-weight: bold;
                                border-bottom: 2px solid #000;
                                text-align: left;
                                white-space: nowrap;
                            }
                            
                            /* Alignment */
                            .text-right {
                                text-align: right !important;
                            }
                            
                            .text-center {
                                text-align: center !important;
                            }
                            
                            .text-left {
                                text-align: left !important;
                            }
                            
                            /* Bold text */
                            .bold {
                                font-weight: bold;
                            }
                            
                            /* Subtotal dan Grand Total */
                            .subtotal-row {
                                background-color: #f8f8f8 !important;
                                border-top: 2px solid #000 !important;
                            }
                            
                            .grand-total-row {
                                background-color: #e8e8e8 !important;
                                border-top: 3px double #000 !important;
                                font-weight: bold;
                            }
                            
                            /* Row styling untuk printer */
                            tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }
                            
                            /* Footer */
                            .footer {
                                margin-top: 20px;
                                font-size: 9px;
                                text-align: center;
                                border-top: 1px solid #000;
                                padding-top: 5px;
                            }
                            
                            /* Page break control */
                            .page-break {
                                page-break-before: always;
                                height: 0;
                                margin: 0;
                                padding: 0;
                            }
                            
                            /* Invoice grouping */
                            .invoice-group {
                                border-left: 2px solid #000;
                                border-right: 2px solid #000;
                            }
                            
                            /* Zebra striping untuk readability */
                            tbody tr:nth-child(odd) {
                                background-color: #fafafa;
                            }
                            
                            /* Print optimization */
                            .print-optimize {
                                -webkit-font-smoothing: none !important;
                                font-smooth: never !important;
                            }
                        </style>
                    </head>
                    <body class="print-optimize">
                        <div class="header">
                            <h1>LAPORAN PENJUALAN DETAIL</h1>
                            <p>${$('#unit').find('option:selected').text()} | ${$('#metode').find('option:selected').text()}</p>
                            <p>Periode: ${$('#start_date').val()} s/d ${$('#end_date').val()}</p>
                        </div>
                        
                        <div class="table-container">
                            <table cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th class="text-left">Tanggal</th>
                                        <th class="text-left">Invoice</th>
                                        <th class="text-left">Customer</th>
                                        <th class="text-left">Unit</th>
                                        <th class="text-left">Metode</th>
                                        <th class="text-left">Kode Barang</th>
                                        <th class="text-left">Nama Barang</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                // Kelompokkan data berdasarkan invoice
                let invoiceGroups = {};
                let grandTotal = 0;
                let currentInvoice = '';
                let rowNumber = 1;
                let invoiceCount = 0;
                
                // Proses data
                data.forEach((row, index) => {
                    // Kelompokkan berdasarkan invoice
                    if (currentInvoice !== row.nomor_invoice) {
                        currentInvoice = row.nomor_invoice;
                        invoiceGroups[currentInvoice] = {
                            total: 0,
                            rows: []
                        };
                        invoiceCount++;
                    }
                    
                    let total = parseFloat(row.total);
                    invoiceGroups[currentInvoice].total += total;
                    invoiceGroups[currentInvoice].rows.push(row);
                    grandTotal += total;
                });

                // Render data dengan subtotal per invoice
                let invoiceIndex = 0;
                let totalRows = 0;
                let itemsPerPage = 40; // Maksimal baris per halaman untuk dot matrix
                
                for (let invoice in invoiceGroups) {
                    let group = invoiceGroups[invoice];
                    let groupRows = group.rows;
                    
                    // Tambahkan separator untuk invoice baru
                    printHTML += `
                        <tr class="invoice-group">
                            <td colspan="10" class="bold" style="background-color: #e0e0e0 !important;">
                                Invoice: ${invoice}
                            </td>
                        </tr>`;
                    
                    // Render setiap baris dalam invoice
                    groupRows.forEach(row => {
                        // Cek jika perlu page break
                        if (totalRows > 0 && totalRows % itemsPerPage === 0) {
                            printHTML += `
                                </tbody>
                                </table>
                                <div class="page-break"></div>
                                <table cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th class="text-left">Tanggal</th>
                                        <th class="text-left">Invoice</th>
                                        <th class="text-left">Customer</th>
                                        <th class="text-left">Unit</th>
                                        <th class="text-left">Metode</th>
                                        <th class="text-left">Kode Barang</th>
                                        <th class="text-left">Nama Barang</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                        }
                        
                        printHTML += `
                            <tr>
                                <td class="text-left">${row.tanggal}</td>
                                <td class="text-left">${row.nomor_invoice}</td>
                                <td class="text-left">${row.customer.substring(0, 15)}${row.customer.length > 15 ? '...' : ''}</td>
                                <td class="text-left">${row.nama_unit.substring(0, 8)}${row.nama_unit.length > 8 ? '...' : ''}</td>
                                <td class="text-left">${row.metode_bayar}</td>
                                <td class="text-left">${row.kode_barang}</td>
                                <td class="text-left">${row.nama_barang.substring(0, 20)}${row.nama_barang.length > 20 ? '...' : ''}</td>
                                <td class="text-right">${parseFloat(row.qty).toLocaleString('id-ID')}</td>
                                <td class="text-right">${parseFloat(row.harga).toLocaleString('id-ID')}</td>
                                <td class="text-right">${parseFloat(row.total).toLocaleString('id-ID')}</td>
                            </tr>`;
                        
                        totalRows++;
                        rowNumber++;
                    });
                    
                    // Tambahkan subtotal untuk invoice ini
                    printHTML += `
                        <tr class="subtotal-row">
                            <td colspan="7" class="text-right bold">Subtotal ${invoice.substring(0, 15)}${invoice.length > 15 ? '...' : ''}</td>
                            <td colspan="2" class="text-right bold">Subtotal</td>
                            <td class="text-right bold">${group.total.toLocaleString('id-ID')}</td>
                        </tr>`;
                    
                    invoiceIndex++;
                }

                // Tambahkan grand total
                printHTML += `
                                </tbody>
                                <tfoot>
                                    <tr class="grand-total-row">
                                        <td colspan="7" class="text-right bold">GRAND TOTAL</td>
                                        <td class="text-right bold">${data.length} transaksi</td>
                                        <td class="text-right bold">${invoiceCount} invoice</td>
                                        <td class="text-right bold">${grandTotal.toLocaleString('id-ID')}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="footer">
                            <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}</p>
                            <p>Halaman 1</p>
                        </div>
                        
                        <script>
                            // Auto print dan close untuk dot matrix
                            window.onload = function() {
                                setTimeout(function() {
                                    window.print();
                                    setTimeout(function() {
                                        window.close();
                                    }, 500);
                                }, 250);
                            };
                        <\/script>
                    </body>
                    </html>`;

                // Buka window baru untuk print dengan ukuran yang sesuai
                let printWindow = window.open('', '_blank', 
                    'width=800,height=600,scrollbars=no,toolbar=no,location=no,status=no,menubar=no');
                
                // Fokus dan tulis konten
                printWindow.document.open();
                printWindow.document.write(printHTML);
                printWindow.document.close();
            }

            // Fungsi alternatif untuk print langsung tanpa preview
            function printReportDirect() {
                let data = table.rows().data().toArray();
                
                if (data.length === 0) {
                    alert('Tidak ada data untuk dicetak!');
                    return;
                }

                // Format data untuk printer dot matrix (text based)
                let printContent = '';
                printContent += '=============================================\n';
                printContent += '        LAPORAN PENJUALAN DETAIL\n';
                printContent += '=============================================\n';
                printContent += 'Periode: ' + $('#start_date').val() + ' s/d ' + $('#end_date').val() + '\n';
                printContent += 'Unit: ' + $('#unit').find('option:selected').text() + '\n';
                printContent += 'Metode: ' + $('#metode').find('option:selected').text() + '\n';
                printContent += 'Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '\n';
                printContent += '=============================================\n\n';
                
                // Header tabel
                printContent += 'Tgl       Invoice    Customer         Unit     Metode Kode     Nama Barang           Qty    Harga        Total\n';
                printContent += '--------- ---------- ---------------- -------- ------ -------- -------------------- ------ ------------ ------------\n';
                
                // Data
                let grandTotal = 0;
                let currentInvoice = '';
                let invoiceTotal = 0;
                
                data.forEach((row, index) => {
                    // Format per baris
                    let tanggal = row.tanggal.padEnd(9, ' ');
                    let invoice = row.nomor_invoice.substring(0, 10).padEnd(10, ' ');
                    let customer = row.customer.substring(0, 16).padEnd(16, ' ');
                    let unit = row.nama_unit.substring(0, 8).padEnd(8, ' ');
                    let metode = row.metode_bayar.substring(0, 6).padEnd(6, ' ');
                    let kode = row.kode_barang.substring(0, 8).padEnd(8, ' ');
                    let nama = row.nama_barang.substring(0, 20).padEnd(20, ' ');
                    let qty = parseFloat(row.qty).toFixed(0).padStart(6, ' ');
                    let harga = parseFloat(row.harga).toLocaleString('id-ID').padStart(12, ' ');
                    let total = parseFloat(row.total).toLocaleString('id-ID').padStart(12, ' ');
                    
                    printContent += tanggal + ' ' + invoice + ' ' + customer + ' ' + unit + ' ' + metode + ' ' + kode + ' ' + nama + ' ' + qty + ' ' + harga + ' ' + total + '\n';
                    
                    grandTotal += parseFloat(row.total);
                    
                    // Check for invoice change
                    if (currentInvoice !== row.nomor_invoice && currentInvoice !== '') {
                        printContent += '                                         Subtotal ' + currentInvoice.substring(0, 15) + ': ' + invoiceTotal.toLocaleString('id-ID').padStart(30, ' ') + '\n';
                        invoiceTotal = 0;
                    }
                    currentInvoice = row.nomor_invoice;
                    invoiceTotal += parseFloat(row.total);
                });
                
                // Add last invoice subtotal
                if (currentInvoice !== '') {
                    printContent += '                                         Subtotal ' + currentInvoice.substring(0, 15) + ': ' + invoiceTotal.toLocaleString('id-ID').padStart(30, ' ') + '\n';
                }
                
                // Grand total
                printContent += '\n' + '='.repeat(80) + '\n';
                printContent += 'GRAND TOTAL: ' + grandTotal.toLocaleString('id-ID').padStart(67, ' ') + '\n';
                printContent += '='.repeat(80) + '\n';
                printContent += '\n\n\n'; // Feed untuk tear off
                
                // Create print iframe
                let iframe = document.createElement('iframe');
                iframe.style.position = 'absolute';
                iframe.style.left = '-1000px';
                iframe.style.top = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                
                document.body.appendChild(iframe);
                
                let doc = iframe.contentWindow.document;
                doc.open();
                doc.write('<pre style="font-family: monospace; font-size: 12px; white-space: pre;">' + printContent + '</pre>');
                doc.close();
                
                // Print
                setTimeout(() => {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    
                    // Cleanup
                    setTimeout(() => {
                        document.body.removeChild(iframe);
                    }, 1000);
                }, 500);
            }

            table = $('#tbpenjualan').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, 'All']],
                ajax: {
                    url: "{{ route('laporan.penjualan_detail.data') }}",
                    type: "GET",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date   = $('#end_date').val();
                        d.unit       = $('#unit').val();
                        d.metode     = $('#metode').val();
                    }
                },
                columns: [
                    { data: "tanggal" },
                    { data: "nomor_invoice" },
                    { data: "customer" },
                    { data: "nama_unit" },
                    { data: "metode_bayar" },
                    { data: "kode_barang" },
                    { data: "nama_barang" },
                    { data: "qty", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString('id-ID'); } },
                    { data: "harga", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString('id-ID'); } },
                    { data: "total", className: "text-end", render: function(data) { return parseFloat(data).toLocaleString('id-ID'); } }
                ],
                drawCallback: function (settings) {
                    let api = this.api();
                    let rows = api.rows({ page: 'current' }).nodes();
                    let data = api.rows({ page: 'current' }).data();
                    
                    // Hapus baris total invoice sebelumnya
                    $(rows).filter('.invoice-total-row').remove();
                    
                    // Reset semua rowspan sebelumnya
                    $(rows).find('td').removeAttr('rowspan');
                    
                    let invoiceGroups = {};
                    let grandTotal = 0;

                    // Kelompokkan data berdasarkan invoice
                    data.each(function (row, i) {
                        let invoice = row.nomor_invoice;
                        let total = parseFloat(row.total);
                        grandTotal += total;

                        if (!invoiceGroups[invoice]) {
                            invoiceGroups[invoice] = {
                                count: 0,
                                total: 0,
                                firstRowIndex: i,
                                data: row
                            };
                        }
                        
                        invoiceGroups[invoice].count++;
                        invoiceGroups[invoice].total += total;
                    });

                    // Terapkan rowspan untuk setiap kelompok invoice
                    for (let invoice in invoiceGroups) {
                        let group = invoiceGroups[invoice];
                        
                        if (group.count > 1) {
                            // Terapkan rowspan pada baris pertama kelompok
                            $(rows).eq(group.firstRowIndex).find('td:eq(0)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(1)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(2)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(3)').attr('rowspan', group.count).addClass('align-middle');
                            $(rows).eq(group.firstRowIndex).find('td:eq(4)').attr('rowspan', group.count).addClass('align-middle');
                            
                            // Sembunyikan kolom pada baris berikutnya dalam kelompok yang sama
                            for (let i = group.firstRowIndex + 1; i < group.firstRowIndex + group.count; i++) {
                                if ($(rows).eq(i).find('td').length > 5) {
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                    $(rows).eq(i).find('td:eq(0)').remove();
                                }
                            }
                        }
                    }

                    // Tambahkan baris total untuk setiap invoice di akhir kelompok
                    let lastInvoice = null;
                    let invoiceRows = [];
                    
                    data.each(function (row, i) {
                        let invoice = row.nomor_invoice;
                        
                        if (lastInvoice !== invoice) {
                            invoiceRows.push(i);
                            lastInvoice = invoice;
                        }
                    });
                    
                    // Tambahkan baris total invoice (dari belakang ke depan agar tidak mengacaukan indeks)
                    for (let i = invoiceRows.length - 1; i >= 0; i--) {
                        let rowIndex = invoiceRows[i];
                        let invoice = data[rowIndex].nomor_invoice;
                        let group = invoiceGroups[invoice];
                        
                        $(rows).eq(rowIndex + group.count - 1).after(
                            `<tr class="fw-bold bg-light invoice-total-row">
                                <td colspan="7" class="text-end">Total Invoice: ${invoice}</td>
                                <td colspan="2" class="text-end">Subtotal</td>
                                <td class="text-end">${group.total.toLocaleString('id-ID')}</td>
                            </tr>`
                        );
                    }

                    // Render grand total di footer
                    $(api.table().footer()).html(
                        `<tr class="fw-bold bg-primary text-white">
                            <td colspan="7" class="text-end">Grand Total</td>
                            <td colspan="2" class="text-end">${data.length} transaksi</td>
                            <td class="text-end">${grandTotal.toLocaleString('id-ID')}</td>
                        </tr>`
                    );
                },
                dom:
                    "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                    "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: { 
                            columns: ':visible',
                            format: {
                                body: function (data, row, column, node) {
                                    // Untuk kolom numerik, hapus format pemisah ribuan saat export
                                    if (column === 7 || column === 8 || column === 9) {
                                        return data.replace(/\./g, '');
                                    }
                                    return data;
                                }
                            }
                        },
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row c', sheet).attr('s', '50'); // Set tinggi row
                        }
                    },
                    {
                        text: '<i class="bi bi-printer"></i> Print Dot Matrix',
                        className: 'btn btn-warning btn-sm',
                        action: function (e, dt, node, config) {
                            // Tampilkan pilihan print
                            if (confirm('Gunakan printer dot matrix? (Ya untuk dot matrix, Tidak untuk printer biasa)')) {
                                printReportDirect(); // Untuk dot matrix
                            } else {
                                printReport(); // Untuk printer biasa
                            }
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>