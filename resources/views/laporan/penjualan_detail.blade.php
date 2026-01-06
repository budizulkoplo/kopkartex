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

                // Buat konten print yang bersih
                let printHTML = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Penjualan Detail</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: 'Arial', sans-serif;
                                font-size: 12px;
                                line-height: 1.4;
                                color: #000;
                                padding: 20px;
                                background: white;
                            }
                            
                            @page {
                                size: A4 landscape;
                                margin: 15mm;
                            }
                            
                            @media print {
                                body {
                                    padding: 0;
                                    margin: 0;
                                }
                                
                                .print-only {
                                    display: block !important;
                                }
                                
                                .no-print {
                                    display: none !important;
                                }
                            }
                            
                            .header {
                                text-align: center;
                                margin-bottom: 20px;
                                padding-bottom: 10px;
                                border-bottom: 2px solid #000;
                            }
                            
                            .header h1 {
                                font-size: 18px;
                                margin-bottom: 5px;
                                color: #000;
                            }
                            
                            .header p {
                                margin: 2px 0;
                                font-size: 11px;
                            }
                            
                            .table-container {
                                width: 100%;
                                overflow: hidden;
                            }
                            
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                table-layout: fixed;
                            }
                            
                            th {
                                background-color: #f2f2f2 !important;
                                color: #000 !important;
                                border: 1px solid #000 !important;
                                padding: 6px 4px;
                                font-weight: bold;
                                font-size: 11px;
                                text-align: left;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            td {
                                border: 1px solid #000 !important;
                                padding: 4px 3px;
                                font-size: 10px;
                                text-align: left;
                            }
                            
                            .text-right {
                                text-align: right;
                            }
                            
                            .text-center {
                                text-align: center;
                            }
                            
                            .bold {
                                font-weight: bold;
                            }
                            
                            .invoice-total {
                                background-color: #e9ecef !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .grand-total {
                                background-color: #d1e7ff !important;
                                -webkit-print-color-adjust: exact;
                            }
                            
                            .subtotal-row td {
                                border-top: 2px solid #000 !important;
                            }
                            
                            .grand-total-row td {
                                border-top: 3px double #000 !important;
                                font-weight: bold;
                            }
                            
                            /* Column widths */
                            th:nth-child(1), td:nth-child(1) { width: 7%; }  /* No */
                            th:nth-child(2), td:nth-child(2) { width: 8%; }  /* Tanggal */
                            th:nth-child(3), td:nth-child(3) { width: 10%; } /* Invoice */
                            th:nth-child(4), td:nth-child(4) { width: 12%; } /* Customer */
                            th:nth-child(5), td:nth-child(5) { width: 8%; }  /* Unit */
                            th:nth-child(6), td:nth-child(6) { width: 8%; }  /* Metode */
                            th:nth-child(7), td:nth-child(7) { width: 10%; } /* Kode Barang */
                            th:nth-child(8), td:nth-child(8) { width: 15%; } /* Nama Barang */
                            th:nth-child(9), td:nth-child(9) { width: 6%; }  /* Qty */
                            th:nth-child(10), td:nth-child(10) { width: 10%; } /* Harga */
                            th:nth-child(11), td:nth-child(11) { width: 10%; } /* Total */
                            
                            .footer {
                                margin-top: 20px;
                                font-size: 10px;
                                text-align: center;
                                color: #666;
                            }
                            
                            .page-break {
                                page-break-before: always;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>LAPORAN PENJUALAN DETAIL</h1>
                            <p>Periode: ${$('#start_date').val()} s/d ${$('#end_date').val()}</p>
                            <p>Unit: ${$('#unit').find('option:selected').text()} | Metode: ${$('#metode').find('option:selected').text()}</p>
                            <p>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        </div>
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Tanggal</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Unit</th>
                                        <th>Metode</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                // Kelompokkan data berdasarkan invoice untuk perhitungan subtotal
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
                for (let invoice in invoiceGroups) {
                    let group = invoiceGroups[invoice];
                    let groupRows = group.rows;
                    
                    // Render setiap baris dalam invoice
                    groupRows.forEach(row => {
                        printHTML += `
                            <tr>
                                <td class="text-center">${rowNumber++}</td>
                                <td>${row.tanggal}</td>
                                <td>${row.nomor_invoice}</td>
                                <td>${row.customer}</td>
                                <td>${row.nama_unit}</td>
                                <td>${row.metode_bayar}</td>
                                <td>${row.kode_barang}</td>
                                <td>${row.nama_barang}</td>
                                <td class="text-right">${parseFloat(row.qty).toLocaleString('id-ID')}</td>
                                <td class="text-right">${parseFloat(row.harga).toLocaleString('id-ID')}</td>
                                <td class="text-right">${parseFloat(row.total).toLocaleString('id-ID')}</td>
                            </tr>`;
                    });
                    
                    // Tambahkan subtotal untuk invoice ini
                    printHTML += `
                        <tr class="subtotal-row invoice-total">
                            <td colspan="8" class="text-right bold">Subtotal Invoice ${invoice}</td>
                            <td colspan="2" class="text-right bold">Subtotal</td>
                            <td class="text-right bold">${group.total.toLocaleString('id-ID')}</td>
                        </tr>`;
                    
                    invoiceIndex++;
                    
                    // Tambahkan page break setelah setiap 40 baris (kecuali invoice terakhir)
                    if (invoiceIndex < Object.keys(invoiceGroups).length && rowNumber % 40 === 0) {
                        printHTML += `
                            <tr class="page-break">
                                <td colspan="11"></td>
                            </tr>`;
                    }
                }

                // Tambahkan grand total
                printHTML += `
                            </tbody>
                            <tfoot>
                                <tr class="grand-total-row grand-total">
                                    <td colspan="8" class="text-right bold">GRAND TOTAL</td>
                                    <td class="text-right bold">${data.length} transaksi</td>
                                    <td class="text-right bold">${invoiceCount} invoice</td>
                                    <td class="text-right bold">${grandTotal.toLocaleString('id-ID')}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="footer">
                        <p>Dicetak dari Sistem pada ${new Date().toLocaleTimeString('id-ID')}</p>
                    </div>
                </body>
                </html>`;

                // Buka window baru untuk print
                let printWindow = window.open('', '_blank', 'width=1200,height=800');
                printWindow.document.open();
                printWindow.document.write(printHTML);
                printWindow.document.close();
                
                // Tunggu konten selesai load lalu print
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                };
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
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-warning btn-sm',
                        action: function (e, dt, node, config) {
                            printReport();
                        }
                    }
                ]
            });
        </script>
    </x-slot>
</x-app-layout>